<?php

namespace App\Controllers;

use App\Libraries\ConsultaRuc;
use App\Models\ContribuyenteModel;
use App\Models\LibroModel;
use App\Models\NotariaModel;
use App\Models\StanModel;
use App\Models\TipoLibroModel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Plantilla de Excel e importación de libros y de su historial de impresiones.
 * Extiende Libro para reutilizar exactamente las mismas validaciones del registro manual.
 *
 * Los libros a los que les faltan datos se importan como "Por completar" (libro.lib_faltantes lista lo que falta).
 */
class LibroImportar extends Libro
{
    private const MAX_FILAS = 500;
    private const MAX_BYTES = 5242880; // 5 MB

    // Hoja "Libros": * = obligatorias para importar. El resto puede quedar vacío (el libro queda "Por completar").
    private const CAB_LIBROS = [
        'RUC del cliente', 'Razón social (referencia)', 'Régimen (referencia)', 'Tipo de libro', 'Tipo de hoja', 'N° de libro',
        'Sede', 'RUC de la notaría', 'Notaría', 'Fecha de legalización', 'N° de kardex', 'N° de stan', 'Folio inicial', 'Folio final',
        'Alerta: mínimo en blanco', 'Impresas: folio desde', 'Impresas: folio hasta', 'Impresas: periodo desde (MM/AAAA)',
        'Impresas: periodo hasta (MM/AAAA)', 'Observación de la impresión', 'Datos por completar (automático)',
    ];

    /** Libros ya vistos en el archivo (clave cliente|tipo|N°): las filas siguientes del mismo libro son otras tandas de impresión. */
    private $grupos = [];

    private $contribuyentes = [];

    // ------------------------------------------------------------------ Plantilla

    public function plantilla()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('crear')) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        $libroModel = new LibroModel();
        $tipos = array_column((new TipoLibroModel())->where('tli_estado', 1)->orderBy('tli_nombre', 'ASC')->findAll(), 'tli_nombre');
        $sedes = array_column($libroModel->sedes(), 'nombre_sede');
        $notarias = array_map(
            fn($n) => $n['not_ruc'] . ' - ' . $n['not_razon_social'],
            (new NotariaModel())->where('not_estado', 1)->orderBy('not_razon_social', 'ASC')->findAll()
        );
        $stans = array_map(
            fn($s) => $s['sede_nombre'] . ' - ' . $s['stan_numero'],
            (new StanModel())->select('stan.stan_numero, COALESCE(sede.nombre_sede, stan.stan_sede) AS sede_nombre')
                ->join('sede', 'sede.id = stan.sede_id', 'left')->where('stan.stan_estado', 1)
                ->orderBy('sede_nombre', 'ASC')->orderBy('stan.stan_numero', 'ASC')->findAll()
        );

        $excel = new Spreadsheet();

        $hoja = $excel->getActiveSheet();
        $hoja->setTitle('Instrucciones');
        $lineas = [
            ['PLANTILLA DE IMPORTACIÓN DE LIBROS - se llena todo en la hoja "Libros"', true],
            ['', false],
            ['1. Una fila por libro. Si el libro tiene varias tandas de impresión, ponga una fila más por cada tanda REPITIENDO el RUC, el Tipo de libro y el N° de libro (los demás datos del libro pueden quedar vacíos en esas filas).', false],
            ['2. El cliente se identifica por su RUC (debe existir como cliente contable). Obligatorios: RUC y Tipo de libro (columnas con título azul oscuro).', false],
            ['3. Lo que falte (sede, notaría, fecha, kardex, stan, folios, N° de libro, tipo de hoja) NO impide importar: el libro queda "Por completar" y el sistema anota qué le falta. Se completa después desde Editar.', false],
            ['4. Notaría: escriba su RUC (11 dígitos) y su nombre. El sistema busca el RUC entre las notarías registradas; si no existe, lo consulta en la API de RUC y la registra automáticamente.', false],
            ['5. Tipo de libro, Tipo de hoja y Sede se eligen de la lista desplegable. El Régimen no se escribe: se toma del que el cliente tiene configurado (la columna es solo de referencia).', false],
            ['6. Folio inicial y Folio final van en columnas separadas; el total de hojas se calcula solo. Alerta (mínimo en blanco): si se deja vacía, se usa 100.', false],
            ['7. El N° de stan debe ser de la sede indicada; si ese stan no existe en la sede, se crea automáticamente.', false],
            ['8. Fechas en formato dd/mm/aaaa (no pueden ser futuras). Periodos en formato MM/AAAA (00 = apertura, 13 y 14 = cierre).', false],
            ['9. Impresiones: columnas "Impresas: ..." (folio desde y hasta, periodos y observación). Los folios de cada tanda deben ir en orden, una después de la otra.', false],
            ['10. Libro ELECTRÓNICO (Tipo de hoja = Electrónico): solo se necesitan RUC, Tipo de libro y Tipo de hoja. No usa hojas ni impresiones. Solo uno por cliente y tipo de libro.', false],
            ['11. El N° de libro no puede repetirse para el mismo cliente y tipo de libro (salvo las filas de impresión del mismo libro).', false],
            ['12. Máximo 500 filas. No cambie los títulos de las columnas ni el nombre de la hoja. La columna "Datos por completar (automático)" es solo informativa.', false],
            ['13. Antes de importar, el sistema valida el archivo y muestra cada fila como correcta, por completar o con error (y permite descargar solo las filas con error para corregirlas).', false],
        ];
        foreach ($lineas as $i => [$texto, $negrita]) {
            $hoja->setCellValue('A' . ($i + 1), $texto);
            $hoja->getStyle('A' . ($i + 1))->getFont()->setBold($negrita);
        }
        $hoja->getColumnDimension('A')->setWidth(170);

        $listas = $excel->createSheet();
        $listas->setTitle('Listas');
        $columnasListas = [
            'A' => ['Tipos de libro', $tipos],
            'B' => ['Tipos de hoja', ['Manual', 'Computarizado', 'Electrónico']],
            'C' => ['Sedes', $sedes],
            'D' => ['Notarías registradas (RUC - nombre)', $notarias],
            'E' => ['Stan (solo referencia: sede - número)', $stans],
        ];
        foreach ($columnasListas as $col => [$titulo, $valores]) {
            $listas->setCellValue($col . '1', $titulo);
            $listas->getStyle($col . '1')->getFont()->setBold(true);
            foreach (array_values($valores) as $i => $v) {
                $listas->setCellValueExplicit($col . ($i + 2), (string) $v, DataType::TYPE_STRING);
            }
            $listas->getColumnDimension($col)->setAutoSize(true);
        }

        $libros = $excel->createSheet(1);
        $libros->setTitle('Libros');
        $this->cabecera($libros, self::CAB_LIBROS, ['A', 'D']);
        $ultima = self::MAX_FILAS + 1;

        foreach (['A', 'F', 'H', 'K', 'L'] as $col) { // texto, para que Excel no convierta RUC ni números de libro
            $libros->getStyle("{$col}2:{$col}{$ultima}")->getNumberFormat()->setFormatCode('@');
        }
        foreach (['R', 'S'] as $col) {
            $libros->getStyle("{$col}2:{$col}{$ultima}")->getNumberFormat()->setFormatCode('@');
        }
        $libros->getStyle("J2:J{$ultima}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        $this->desplegable($libros, 'D', $ultima, 'A', count($tipos));
        $this->desplegable($libros, 'E', $ultima, 'B', 3);
        $this->desplegable($libros, 'G', $ultima, 'C', count($sedes));

        $excel->setActiveSheetIndexByName('Libros');

        return $this->descargar($excel, 'plantilla_libros.xlsx');
    }

    private function descargar(Spreadsheet $excel, string $nombre)
    {
        $escritor = new Xlsx($excel);
        ob_start();
        $escritor->save('php://output');
        $contenido = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombre . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($contenido);
    }

    private function cabecera($hoja, array $titulos, array $obligatorias)
    {
        foreach ($titulos as $i => $titulo) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $hoja->setCellValue($col . '1', $titulo);
            $hoja->getColumnDimension($col)->setWidth(max(16, min(34, mb_strlen($titulo) + 4)));

            $estilo = $hoja->getStyle($col . '1');
            $estilo->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $estilo->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(in_array($col, $obligatorias, true) ? '1F4E78' : '5B7C99');
            $estilo->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        }

        $hoja->getRowDimension(1)->setRowHeight(34);
        $hoja->freezePane('A2');
    }

    private function desplegable($hoja, $columna, $hastaFila, $columnaListas, $cantidad)
    {
        $formula = 'Listas!$' . $columnaListas . '$2:$' . $columnaListas . '$' . max(2, $cantidad + 1);

        for ($fila = 2; $fila <= $hastaFila; $fila++) {
            $v = $hoja->getCell($columna . $fila)->getDataValidation();
            $v->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Valor no válido')
                ->setError('Elija un valor de la lista.')
                ->setFormula1($formula);
        }
    }

    // ------------------------------------------------------------------ Importación

    public function previa()
    {
        return $this->ejecutar(false);
    }

    public function confirmar()
    {
        return $this->ejecutar(true);
    }

    private function directorio()
    {
        $dir = WRITEPATH . 'uploads/libros_importar/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Limpia archivos temporales con más de 2 horas
        foreach (glob($dir . '*.xlsx') ?: [] as $viejo) {
            if (filemtime($viejo) < time() - 7200) {
                @unlink($viejo);
            }
        }

        return $dir;
    }

    private function ejecutar(bool $confirmar)
    {
        try {
            if (!session()->logged_in) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sesión vencida.']);
            }

            if (!$this->permiso('crear')) {
                return $this->sinPermiso();
            }

            $dir = $this->directorio();

            if ($confirmar) {
                $token = (string) $this->request->getPost('token');
                if (!preg_match('/^[a-f0-9]{32}$/', $token) || !is_file($dir . $token . '.xlsx')) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo ya no está disponible. Vuelva a validarlo.']);
                }
                $ruta = $dir . $token . '.xlsx';
            } else {
                $archivo = $this->request->getFile('archivo');
                if (!$archivo || !$archivo->isValid()) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Seleccione un archivo de Excel (.xlsx).']);
                }
                if (strtolower($archivo->getClientExtension()) !== 'xlsx') {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo debe ser de Excel (.xlsx).']);
                }
                if ($archivo->getSize() > self::MAX_BYTES) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo de 5 MB.']);
                }

                $token = bin2hex(random_bytes(16));
                $archivo->move($dir, $token . '.xlsx');
                $ruta = $dir . $token . '.xlsx';
            }

            try {
                $resultado = $this->procesar($ruta, $confirmar);
            } catch (\Throwable $e) {
                @unlink($ruta);
                return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
            }

            if ($confirmar) {
                @unlink($ruta);
            }

            // Los valores originales solo se usan para el archivo de errores
            $resultado['filas'] = array_map(fn($f) => array_diff_key($f, ['v' => 1, 'destino' => 1]), $resultado['filas']);

            return $this->response->setJSON(['status' => 'success', 'token' => $token] + $resultado);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Excel con solo las filas con error (y su motivo), con el mismo formato de la plantilla para corregirlas y volver a subirlas. */
    public function errores()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('crear')) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        $token = (string) $this->request->getGet('token');
        $ruta = $this->directorio() . $token . '.xlsx';
        if (!preg_match('/^[a-f0-9]{32}$/', $token) || !is_file($ruta)) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        $resultado = $this->procesar($ruta, false);

        $excel = new Spreadsheet();
        $libros = $excel->getActiveSheet();
        $libros->setTitle('Libros');
        $this->cabecera($libros, array_merge(self::CAB_LIBROS, ['Motivo del error']), ['A', 'D']);

        $fila = 2;
        foreach ($resultado['filas'] as $f) {
            if ($f['estado'] !== 'error' || empty($f['v'])) {
                continue;
            }

            $valores = array_values($f['v']);
            $valores[] = $f['mensaje'];

            foreach ($valores as $i => $v) {
                $libros->setCellValueExplicit([$i + 1, $fila], (string) $v, DataType::TYPE_STRING);
            }
            $fila++;
        }
        $excel->setActiveSheetIndex(0);

        return $this->descargar($excel, 'libros_con_errores.xlsx');
    }

    /**
     * Valida e importa el archivo. Siempre ejecuta la importación real dentro de una transacción:
     * en la vista previa ($confirmar = false) se deshace al final, así las reglas se prueban contra
     * lo que ya se importó en el mismo archivo (duplicados, libro anterior, notarías creadas, etc.).
     */
    private function procesar(string $ruta, bool $confirmar)
    {
        $this->importando = true;

        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(false);
        $excel = $lector->load($ruta);

        $hojaLibros = $excel->getSheetByName('Libros');

        if (!$hojaLibros) {
            throw new \RuntimeException('El archivo no tiene la hoja "Libros" de la plantilla. Descargue la plantilla nuevamente.');
        }

        $filasLibros = $this->leerHoja($hojaLibros, self::CAB_LIBROS, 'Libros');

        if (!$filasLibros) {
            throw new \RuntimeException('El archivo no tiene filas para importar.');
        }

        $this->grupos = [];
        $cat = $this->catalogos($excel->getSheetByName('Notarias'));
        $filas = [];

        $db = db_connect();
        $db->transStart(!$confirmar);

        try {
            foreach ($filasLibros as $fila) {
                array_push($filas, ...$this->importarLibro($fila, $cat));
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        $orden = fn($f) => [$f['fila'], $f['tipo'] === 'libro' ? 0 : 1];
        usort($filas, fn($a, $b) => $orden($a) <=> $orden($b));

        $cuenta = fn($tipo, $estado) => count(array_filter($filas, fn($f) => $f['tipo'] === $tipo && $f['estado'] === $estado));

        return [
            'confirmado' => $confirmar,
            'resumen' => [
                'libros_ok' => $cuenta('libro', 'ok'),
                'libros_incompletos' => $cuenta('libro', 'incompleto'),
                'libros_error' => $cuenta('libro', 'error'),
                'impresiones_ok' => $cuenta('impresion', 'ok'),
                'impresiones_error' => $cuenta('impresion', 'error'),
            ],
            'filas' => $filas,
        ];
    }

    // ------------------------------------------------------------------ Lectura del Excel

    private function leerHoja($hoja, array $cabeceras, string $nombre)
    {
        foreach ($cabeceras as $i => $esperada) {
            $actual = $this->texto($hoja->getCell([$i + 1, 1])->getValue());
            if ($this->clave($actual) !== $this->clave($esperada)) {
                throw new \RuntimeException('La hoja "' . $nombre . '" no tiene los títulos de la plantilla (columna ' . ($i + 1) . ': se esperaba "' . $esperada . '"). Descargue la plantilla nuevamente.');
            }
        }

        $filas = [];
        $max = $hoja->getHighestDataRow();

        for ($r = 2; $r <= $max; $r++) {
            $valores = [];

            foreach ($cabeceras as $i => $_) {
                $celda = $hoja->getCell([$i + 1, $r]);
                $valor = $celda->getValue();

                // Las fechas de Excel llegan como número: se convierten a texto Y-m-d
                if (is_numeric($valor) && Date::isDateTime($celda)) {
                    $valor = Date::excelToDateTimeObject($valor)->format('Y-m-d');
                }

                $valores[] = $this->texto($valor);
            }

            // En "Libros" las columnas de referencia (razón social, régimen, datos por completar) no cuentan para saber si la fila está vacía
            $vacia = true;
            foreach ($valores as $i => $v) {
                if ($nombre === 'Libros' && in_array($i, [1, 2, 20], true)) {
                    continue;
                }
                if ($v !== '') {
                    $vacia = false;
                    break;
                }
            }

            if (!$vacia) {
                $filas[] = ['n' => $r, 'v' => $valores];
            }
        }

        if (count($filas) > self::MAX_FILAS) {
            throw new \RuntimeException('La hoja "' . $nombre . '" tiene más de ' . self::MAX_FILAS . ' filas. Divida el archivo.');
        }

        return $filas;
    }

    private function texto($v)
    {
        if ($v === null) {
            return '';
        }

        if (is_float($v) || is_int($v)) {
            return floor($v) == $v ? number_format($v, 0, '', '') : (string) $v;
        }

        return trim((string) $v);
    }

    /** Clave de comparación: minúsculas, sin tildes y sin espacios repetidos. */
    private function clave($s)
    {
        $s = mb_strtolower(trim((string) $s));
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return preg_replace('/\s+/', ' ', $s);
    }

    private function entero($s)
    {
        return preg_match('/^\d+$/', (string) $s) ? (int) $s : null;
    }

    /** "03/2026", "3/2026" o "2026-03" -> "2026-03" (mes 00 a 14). Devuelve null si está vacío y false si es inválido. */
    private function periodo($s)
    {
        $s = trim((string) $s);
        if ($s === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $s, $m)) {
            [$mes, $anio] = [(int) $m[1], $m[2]];
        } elseif (preg_match('/^(\d{4})-(\d{1,2})$/', $s, $m)) {
            [$mes, $anio] = [(int) $m[2], $m[1]];
        } else {
            return false;
        }

        return $mes >= 0 && $mes <= 14 ? $anio . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) : false;
    }

    private function fecha($s)
    {
        $s = trim((string) $s);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $formato) {
            $f = \DateTime::createFromFormat($formato, $s);
            if ($f && $f->format($formato) === $s) {
                return $f->format('Y-m-d');
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ Catálogos y búsquedas

    private function catalogos($hojaNotarias = null)
    {
        $cat = ['tipos' => [], 'sedes' => [], 'notariasRuc' => [], 'notariasNombre' => [], 'stans' => [], 'equivalencias' => []];

        foreach ((new TipoLibroModel())->where('tli_estado', 1)->findAll() as $t) {
            $cat['tipos'][$this->clave($t['tli_nombre'])] = $t;
        }

        foreach ((new LibroModel())->sedes() as $s) {
            $cat['sedes'][$this->clave($s['nombre_sede'])] = $s;
        }

        foreach ((new NotariaModel())->where('not_estado', 1)->findAll() as $n) {
            $cat['notariasRuc'][$n['not_ruc']] = $n;
            $cat['notariasNombre'][$this->clave($n['not_razon_social'])] = $n;
        }

        foreach ((new StanModel())->where('stan_estado', 1)->findAll() as $s) {
            $cat['stans'][(int) $s['sede_id']][$this->clave($s['stan_numero'])] = $s;
        }

        // Hoja opcional "Notarias": equivalencia nombre -> RUC
        if ($hojaNotarias) {
            for ($r = 2; $r <= min($hojaNotarias->getHighestDataRow(), 2 + self::MAX_FILAS); $r++) {
                $nombre = $this->texto($hojaNotarias->getCell([1, $r])->getValue());
                $ruc = preg_replace('/\D/', '', $this->texto($hojaNotarias->getCell([2, $r])->getValue()));
                if ($nombre !== '' && strlen($ruc) === 11) {
                    $cat['equivalencias'][$this->clave($nombre)] = $ruc;
                }
            }
        }

        return $cat;
    }

    private function contribuyente(string $ruc)
    {
        if (!array_key_exists($ruc, $this->contribuyentes)) {
            $this->contribuyentes[$ruc] = (new ContribuyenteModel())->select('id, razon_social, ruc')
                ->where('ruc', $ruc)->where('tipoServicio', 'CONTABLE')->where('estado >', 0)->first();
        }

        return $this->contribuyentes[$ruc];
    }

    private function descripcion($tipo, $numero)
    {
        return $tipo . ($numero !== '' ? ' N° ' . $numero : '');
    }

    /** Razón social de un RUC desde la API (con caché de 30 min para no repetir consultas entre la vista previa y la importación). */
    private function consultarRuc(string $ruc)
    {
        $clave = 'ruc_notaria_' . $ruc;

        if ($guardado = cache($clave)) {
            return $guardado;
        }

        $r = (new ConsultaRuc())->consultar('ruc', $ruc, 15, 8);

        if (($r['respuesta'] ?? '') === 'ok' && !empty($r['data']->razon_social)) {
            $resultado = ['ok' => true, 'razon' => trim((string) $r['data']->razon_social)];
            cache()->save($clave, $resultado, 1800);

            return $resultado;
        }

        return ['ok' => false, 'mensaje' => !empty($r['errores_curl']) ? 'la API no respondió' : 'RUC no encontrado en la API'];
    }

    /**
     * Notaría de una fila: por RUC (base de datos -> API) o, si solo hay nombre, por equivalencia o nombre registrado.
     * Devuelve [notaría|null, error|null, faltante|null, nota|null].
     */
    private function resolverNotaria(string $rucTxt, string $nombre, array &$cat)
    {
        $ruc = preg_replace('/\D/', '', $rucTxt);

        if ($rucTxt !== '' && strlen($ruc) !== 11) {
            return [null, 'El RUC de la notaría debe tener 11 dígitos.', null, null];
        }

        if ($ruc === '' && $nombre !== '') {
            $ruc = $cat['equivalencias'][$this->clave($nombre)] ?? '';

            if ($ruc === '') {
                $n = $cat['notariasNombre'][$this->clave($nombre)] ?? null;

                return $n ? [$n, null, null, null] : [null, null, 'Notaría (falta su RUC)', null];
            }
        }

        if ($ruc === '') {
            return [null, null, 'Notaría', null];
        }

        if ($n = $cat['notariasRuc'][$ruc] ?? null) {
            return [$n, null, null, null];
        }

        $existente = (new NotariaModel())->where('not_ruc', $ruc)->where('not_estado !=', 0)->first();
        if ($existente) {
            return [null, 'La notaría con RUC ' . $ruc . ' está inactiva. Actívela en Configuración > Notarias.', null, null];
        }

        $api = $this->consultarRuc($ruc);
        if (!$api['ok']) {
            return [null, 'La notaría con RUC ' . $ruc . ' no está registrada y no se pudo obtener de la API (' . $api['mensaje'] . '). Regístrela en Configuración > Notarias.', null, null];
        }

        $id = (new NotariaModel())->insert(['not_ruc' => $ruc, 'not_razon_social' => $api['razon'], 'not_estado' => 1]);
        $n = ['not_id' => $id, 'not_ruc' => $ruc, 'not_razon_social' => $api['razon'], 'not_estado' => 1];
        $cat['notariasRuc'][$ruc] = $n;
        $cat['notariasNombre'][$this->clave($api['razon'])] = $n;

        return [$n, null, null, 'Notaría ' . $api['razon'] . ' registrada desde la API'];
    }

    // ------------------------------------------------------------------ Filas

    /** Importa una fila de "Libros". Devuelve una o dos filas de resultado (el libro y, si trae, su primera impresión). */
    private function importarLibro(array $fila, array &$cat)
    {
        $v = $fila['v'];
        [$ruc, , $regimenExcel, $tipo, $hoja, $numero, $sede, $rucNot, $notaria, $fecha, $kardex, $stan, $folioIni, $folioFin, $minimo] = $v;

        $base = ['hoja' => 'Libros', 'tipo' => 'libro', 'destino' => 'Libros', 'v' => $v, 'fila' => $fila['n'], 'ruc' => $ruc, 'cliente' => '', 'descripcion' => $this->descripcion($tipo, $numero), 'estado' => 'error', 'mensaje' => '', 'faltan' => ''];
        $clave = null;
        $error = function ($m) use (&$base, &$clave, $fila) {
            if ($clave !== null && !isset($this->grupos[$clave])) {
                $this->grupos[$clave] = ['estado' => 'error', 'fila' => $fila['n']];
            }

            return [['mensaje' => $m] + $base];
        };

        if (!preg_match('/^\d{11}$/', $ruc)) {
            return $error('El RUC debe tener 11 dígitos.');
        }

        $cli = $this->contribuyente($ruc);
        if (!$cli) {
            return $error('No existe un cliente contable con ese RUC.');
        }
        $base['cliente'] = $cli['razon_social'];

        $t = $cat['tipos'][$this->clave($tipo)] ?? null;
        if (!$t) {
            return $error($tipo === '' ? 'Falta el tipo de libro.' : 'El tipo de libro "' . $tipo . '" no existe en el sistema.');
        }

        // ---------- Un libro ya conocido: las filas repetidas son otras tandas de impresión del mismo libro
        $tieneImpresion = ($v[15] !== '' || $v[16] !== '' || $v[17] !== '' || $v[18] !== '');

        if ($numero !== '') {
            $clave = $cli['id'] . '|' . $t['tli_id'] . '|' . (ctype_digit($numero) ? (string) (int) $numero : mb_strtolower($numero));
            $grupo = $this->grupos[$clave] ?? null;

            if ($grupo) {
                if ($grupo['estado'] === 'error') {
                    return $error('La fila principal de este libro (fila ' . $grupo['fila'] . ') tiene error: corríjala primero.');
                }
                if (!$tieneImpresion) {
                    return $error('Fila repetida: el libro ya está en la fila ' . $grupo['fila'] . ' y esta fila no trae ninguna impresión.');
                }

                return [$this->filaImpresion($fila, $grupo['lib_id'], $cli, $tipo, $numero)];
            }

            // Sin datos del libro y con impresión: es otra tanda de un libro que ya existe en el sistema
            $soloImpresion = !array_filter([$hoja, $sede, $rucNot, $notaria, $fecha, $kardex, $stan, $folioIni, $folioFin, $minimo], fn($x) => $x !== '');
            if ($soloImpresion && $tieneImpresion && ($existente = (new LibroModel())->libroConNumero($cli['id'], $t['tli_id'], $numero))) {
                return [$this->filaImpresion($fila, $existente['lib_id'], $cli, $tipo, $numero)];
            }
        }

        $tipoHoja = null;
        if ($hoja !== '') {
            $tipoHoja = ['manual' => 'manual', 'computarizado' => 'computarizado', 'electronico' => 'electronico'][$this->clave($hoja)] ?? false;
            if ($tipoHoja === false) {
                return $error('El tipo de hoja debe ser Manual, Computarizado o Electrónico.');
            }
        }

        $regimen = (new LibroModel())->regimenDe($cli['id']);
        $notas = [];
        $faltan = [];

        // El régimen del Excel es solo de referencia: se avisa si no coincide con el configurado
        if ($regimenExcel !== '' && $regimen && $this->clave($regimenExcel) !== $this->clave($this->regimenContribuyente($cli['id']))) {
            $notas[] = 'Aviso: el régimen del Excel (' . $regimenExcel . ') no coincide con el configurado (' . $this->regimenContribuyente($cli['id']) . '); se usa el configurado';
        }

        $d = ['contribuyente' => $cli['id'], 'regimen' => $regimen ? (string) $regimen : '', 'tipoLibro' => $t['tli_id'], 'tipoHoja' => (string) $tipoHoja];

        // ---------- Libro electrónico: solo necesita los datos básicos
        if ($tipoHoja === 'electronico') {
            [$mensaje, $datos] = $this->armarDatosLibro($d, 0);
            if ($mensaje) {
                return $error($mensaje);
            }

            (new LibroModel())->insert($datos + $this->sello('lib', 'add'));

            return [['estado' => 'ok', 'mensaje' => 'Libro electrónico.'] + $base];
        }

        // ---------- Libro con hojas: lo que falta no impide importar (queda "Por completar"); lo que viene mal sí es error
        if (!$regimen) {
            $faltan[] = 'Régimen del contribuyente (configúrelo en Contribuyentes)';
        }
        if (!$tipoHoja) {
            $faltan[] = 'Tipo de hoja';
        }
        if ($numero === '') {
            $faltan[] = 'N° de libro';
        }

        $s = null;
        if ($sede === '') {
            $faltan[] = 'Sede';
        } else {
            $s = $cat['sedes'][$this->clave($sede)] ?? null;
            if (!$s) {
                return $error('La sede "' . $sede . '" no existe o está inactiva.');
            }
        }

        [$n, $errorNotaria, $faltaNotaria, $notaNotaria] = $this->resolverNotaria($rucNot, $notaria, $cat);
        if ($errorNotaria) {
            return $error($errorNotaria);
        }
        if ($faltaNotaria) {
            $faltan[] = $faltaNotaria;
        }
        if ($notaNotaria) {
            $notas[] = $notaNotaria;
        }

        $f = null;
        if ($fecha === '') {
            $faltan[] = 'Fecha de legalización';
        } else {
            $f = $this->fecha($fecha);
            if (!$f) {
                return $error('La fecha de legalización debe tener el formato dd/mm/aaaa.');
            }

            $hoy = (new \DateTime('now', new \DateTimeZone(config('App')->appTimezone)))->format('Y-m-d');
            if ($f > $hoy) {
                return $error('La fecha de legalización no puede ser posterior a hoy (' . date('d/m/Y', strtotime($hoy)) . ').');
            }
        }

        if ($kardex === '') {
            $faltan[] = 'N° de kardex';
        }

        $st = null;
        if ($stan === '') {
            $faltan[] = 'N° de stan';
        } elseif (!$s) {
            $faltan[] = 'N° de stan (falta la sede)';
        } else {
            $st = $cat['stans'][(int) $s['id']][$this->clave($stan)] ?? null;
            if (!$st) {
                // El stan no existe en esa sede: se crea
                $id = (new StanModel())->insert(['stan_sede' => $s['nombre_sede'], 'sede_id' => $s['id'], 'stan_numero' => $stan, 'stan_estado' => 1]);
                $st = ['stan_id' => $id, 'sede_id' => $s['id'], 'stan_numero' => $stan];
                $cat['stans'][(int) $s['id']][$this->clave($stan)] = $st;
                $notas[] = 'Stan ' . $stan . ' registrado en ' . $s['nombre_sede'];
            }
        }

        $fi = $this->entero($folioIni);
        $ff = $this->entero($folioFin);
        if (($folioIni !== '' && $fi === null) || ($folioFin !== '' && $ff === null)) {
            return $error('Los folios deben ser números enteros.');
        }

        if ($fi === null && $ff === null) {
            $faltan[] = 'Folios';
        } elseif ($fi === null) {
            $faltan[] = 'Folio inicial';
        } elseif ($ff === null) {
            $faltan[] = 'Folio final';
        } elseif ($fi < 1 || $ff < $fi) {
            return $error('Folios inválidos: el inicial debe ser mayor a 0 y el final mayor o igual al inicial.');
        }

        $mi = $minimo === '' ? 100 : $this->entero($minimo);
        if ($mi === null || $mi < 1) {
            return $error('La alerta (mínimo en blanco) debe ser un número entero mayor a 0.');
        }

        // ---------- Completo: mismas reglas del registro manual
        if (!$faltan) {
            $d += [
                'numero' => $numero, 'sede' => $s['id'], 'notaria' => $n['not_id'], 'fechaLegalizacion' => $f, 'kardex' => $kardex,
                'stan' => $st['stan_id'], 'folioInicial' => (string) $fi, 'folioFinal' => (string) $ff, 'minimo' => (string) $mi,
            ];

            [$mensaje, $datos] = $this->armarDatosLibro($d, 0);
            if ($mensaje) {
                return $error($mensaje);
            }

            $libId = (new LibroModel())->insert($datos + $this->sello('lib', 'add'));
            $estado = 'ok';
            $mensajeOk = 'Libro registrado.';
        } else {
            // ---------- Incompleto: se registra "Por completar" con lo que traiga
            if ($numero !== '' && ($existe = (new LibroModel())->libroConNumero($cli['id'], $t['tli_id'], $numero))) {
                return $error('Ya existe el libro ' . $existe['nombre'] . ' para este cliente. Use otro número de libro.');
            }

            $libId = (new LibroModel())->insert([
                'con_id' => $cli['id'],
                'tli_id' => $t['tli_id'],
                'lib_tipo_hoja' => $tipoHoja,
                'tri_id' => $regimen ?: null,
                'lib_numero' => $numero !== '' ? $numero : null,
                'lib_sede' => $s ? $s['nombre_sede'] : '',
                'sede_id' => $s ? (int) $s['id'] : null,
                'not_id' => $n ? (int) $n['not_id'] : null,
                'lib_fecha_legalizacion' => $f,
                'lib_kardex' => $kardex !== '' ? $kardex : null,
                'stan_id' => $st ? (int) $st['stan_id'] : null,
                'lib_folio_inicial' => $fi ?? 1,
                'lib_folio_final' => $ff ?? 1,
                'lib_total_hojas' => ($fi !== null && $ff !== null) ? $ff - $fi + 1 : 0,
                'lib_minimo' => $mi,
                'lib_estado' => 1,
                'lib_faltantes' => mb_substr(implode(', ', $faltan), 0, 255),
            ] + $this->sello('lib', 'add'));
            $estado = 'incompleto';
            $mensajeOk = 'Libro registrado por completar.';
        }

        if ($clave !== null) {
            $this->grupos[$clave] = ['estado' => $estado, 'lib_id' => $libId, 'fila' => $fila['n']];
        }

        $resultados = [['estado' => $estado, 'mensaje' => trim($mensajeOk . ' ' . implode('. ', $notas)), 'faltan' => implode(', ', $faltan)] + $base];

        // ---------- Primera tanda de impresiones, escrita en la misma fila del libro
        if ($tieneImpresion) {
            $resultados[] = $this->filaImpresion($fila, $libId, $cli, $tipo, $numero);
        }
        return $resultados;
    }

    /**
     * Resultado de la impresión escrita en una fila (columnas "Impresas: ..."). Si falla, la fila de error se arma
     * como "solo impresión" (RUC + tipo + N° + datos de la impresión) para poder corregirla y volver a subirla.
     */
    private function filaImpresion(array $fila, $libId, array $cli, $tipo, $numero)
    {
        $v = $fila['v'];
        [$desde, $hasta, $p1, $p2, $obs] = [$v[15], $v[16], $v[17], $v[18], $v[19]];
        $soloImpresion = [$v[0], '', '', $tipo, '', $numero, '', '', '', '', '', '', '', '', '', $desde, $hasta, $p1, $p2, $obs, ''];

        return ['hoja' => 'Libros', 'tipo' => 'impresion', 'destino' => 'Libros', 'v' => $soloImpresion]
            + $this->aplicarImpresion($libId, $desde, $hasta, $p1, $p2, $obs)
            + ['fila' => $fila['n'], 'ruc' => $v[0], 'cliente' => $cli['razon_social'], 'descripcion' => 'Impresión de ' . $this->descripcion($tipo, $numero) . ' (fila ' . $fila['n'] . ')', 'faltan' => ''];
    }
    /** Valida y registra una impresión del historial. Devuelve ['estado' => 'ok'|'error', 'mensaje' => ...]. */
    private function aplicarImpresion($libId, $desdeTxt, $hastaTxt, $p1Txt, $p2Txt, $observacion)
    {
        $error = fn($m) => ['estado' => 'error', 'mensaje' => $m];

        $model = new LibroModel();
        $libro = $model->find($libId);

        if ($libro['lib_tipo_hoja'] === 'electronico') {
            return $error('Un libro electrónico no usa hojas: no registra impresiones.');
        }

        $desde = $this->entero($desdeTxt);
        $hasta = $this->entero($hastaTxt);
        if ($desde === null || $hasta === null || $desde < 1 || $hasta < $desde) {
            return $error('Rango de folios inválido (folio desde y hasta deben ser enteros, con hasta mayor o igual a desde).');
        }

        // Si el libro ya tiene sus folios, la impresión debe estar dentro de ellos
        if ((int) $libro['lib_total_hojas'] > 0 && ($desde < (int) $libro['lib_folio_inicial'] || $hasta > (int) $libro['lib_folio_final'])) {
            return $error('Los folios están fuera del libro (' . $libro['lib_folio_inicial'] . ' al ' . $libro['lib_folio_final'] . ').');
        }

        $ultimoFolio = 0;
        foreach ($model->impresiones($libId) as $imp) {
            $ultimoFolio = max($ultimoFolio, (int) $imp['lim_folio_hasta']);
        }
        if ($desde <= $ultimoFolio) {
            return $error('Los folios se cruzan con una impresión anterior: deben continuar después del folio ' . $ultimoFolio . '.');
        }

        $p1 = $this->periodo($p1Txt);
        $p2 = $this->periodo($p2Txt);
        if ($p1 === false || $p2 === false) {
            return $error('Los periodos deben tener el formato MM/AAAA (mes de 00 a 14).');
        }

        if ($mensaje = $this->mensajeImpresion($libId, $p1, $p2)) {
            return $error($mensaje);
        }

        $this->registrarImpresion($libId, $desde, $hasta, $p1, $p2, $observacion);

        return ['estado' => 'ok', 'mensaje' => 'Impresión registrada (folios ' . $desde . ' al ' . $hasta . ').'];
    }
}
