<?php
/**
 * Convierte la hoja "DATA ORIGINAL" del "modelo de inventario" (formato anterior) a la plantilla nueva de
 * importación de libros, ya rellenada con lo que se puede deducir. Lo que no se puede deducir queda vacío y se
 * anota en la columna "Datos por completar (automático)".
 *
 * Uso:  php scripts/convertir_modelo_inventario.php "ruta\modelo de inventario.xlsx" [salida.xlsx] [notarias.csv]
 *
 * notarias.csv (opcional): dos columnas "Notaría;RUC" (nombre como aparece en el modelo y su RUC). Con él se llena la
 * columna "RUC de la notaría". Sin él se genera notarias_para_completar.csv con los nombres para que se llenen.
 *
 * Después se sube el archivo resultante en Inventario de libros > Importar libros.
 * Todo el llenado queda en una sola hoja ("Libros"): una fila por libro y, si tiene varias tandas de impresión,
 * una fila más por cada tanda (repitiendo RUC, tipo y N° de libro), igual que el modelo anterior.
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (empty($argv[1]) || !is_file($argv[1])) {
    fwrite(STDERR, "Uso: php scripts/convertir_modelo_inventario.php \"ruta\\modelo.xlsx\" [salida.xlsx]\n");
    exit(1);
}

$entrada = $argv[1];
$salida = $argv[2] ?? __DIR__ . '/../inventario_convertido.xlsx';

// Equivalencias nombre de notaría -> RUC (opcional)
$equivalencias = [];
if (!empty($argv[3]) && is_file($argv[3])) {
    $h = fopen($argv[3], 'r');
    $cabecera = fgets($h);
    $sepCsv = substr_count($cabecera, ';') >= substr_count($cabecera, ',') ? ';' : ',';
    while (($fila = fgetcsv($h, 0, $sepCsv)) !== false) {
        $ruc = preg_replace('/\D/', '', (string) ($fila[1] ?? ''));
        if (trim((string) ($fila[0] ?? '')) !== '' && strlen($ruc) === 11) {
            $equivalencias[mb_strtoupper(preg_replace('/\s+/', ' ', trim($fila[0])))] = $ruc;
        }
    }
    fclose($h);
}

// ---------------------------------------------------------------- Conexión (credenciales del .env)
$env = [];
foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES) as $linea) {
    if (preg_match('/^\s*database\.default\.(\w+)\s*=\s*(.*)$/', $linea, $m)) {
        $env[$m[1]] = trim($m[2]);
    }
}
$pdo = new PDO('mysql:host=' . ($env['hostname'] ?? 'localhost') . ';dbname=' . $env['database'] . ';charset=utf8mb4', $env['username'], $env['password'] ?? '');

$tiposBD = $pdo->query('SELECT tli_nombre FROM tipo_libro WHERE tli_estado = 1 ORDER BY tli_nombre')->fetchAll(PDO::FETCH_COLUMN);
$sedesBD = $pdo->query('SELECT nombre_sede FROM sede WHERE estado = 1 ORDER BY nombre_sede')->fetchAll(PDO::FETCH_COLUMN);
$notariasBD = $pdo->query("SELECT CONCAT(not_ruc, ' - ', not_razon_social) FROM notaria WHERE not_estado = 1 ORDER BY not_razon_social")->fetchAll(PDO::FETCH_COLUMN);
$stansBD = $pdo->query("SELECT CONCAT(COALESCE(s.nombre_sede, t.stan_sede), ' - ', t.stan_numero) FROM stan t LEFT JOIN sede s ON s.id = t.sede_id WHERE t.stan_estado = 1 ORDER BY 1")->fetchAll(PDO::FETCH_COLUMN);

// ---------------------------------------------------------------- Utilidades
function sinTildes(string $s): string
{
    return strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
}

function limpio($v): string
{
    $v = trim(preg_replace('/\s+/', ' ', (string) $v));

    return in_array($v, ['-', '--', 'X'], true) ? '' : $v;
}

function mayus(string $s): string
{
    return sinTildes(mb_strtoupper($s));
}

function tipoLibro(string $raw): string
{
    $t = mayus($raw);

    return match (true) {
        $t === '' => '',
        str_contains($t, 'DIARIO') && (str_contains($t, 'SIMPLIF') || str_contains($t, 'SIMPLEM')) => 'Libro Diario de Formato Simplificado',
        str_contains($t, 'DIARIO') => 'Libro Diario',
        str_contains($t, 'MAYOR') => 'Libro Mayor',
        str_contains($t, 'VENTAS') && str_contains($t, 'INGRESOS') => 'Registro de Ventas e Ingresos',
        str_contains($t, 'VENTAS') => 'Registro de Ventas',
        str_contains($t, 'COMPRAS') => 'Registro de Compras',
        str_contains($t, 'ACTAS') => 'Libro de Actas',
        str_contains($t, 'ACTIVO') && (str_contains($t, 'FIJ') || str_contains($t, 'FJ')) => 'Registro de Activos Fijos',
        str_contains($t, 'INVENTARIO') && str_contains($t, 'BALANCE') => 'Libro de Inventarios y Balances',
        str_contains($t, 'INVENTARIO') && str_contains($t, 'VALORIZADO') => 'Registro de Inventario Permanente Valorizado',
        str_contains($t, 'INVENTARIO') && (str_contains($t, 'FISIC') || str_contains($t, 'UNIDADES')) => 'Registro de Inventario Permanente en Unidades Físicas',
        str_contains($t, 'CAJA') => 'Libro Caja y Bancos',
        str_contains($t, 'KARDEX') => 'Kardex',
        str_contains($t, 'MATRICULA') => 'Libro de Matrícula de Acciones',
        str_contains($t, 'PLANILLA') => 'Libro de Planillas',
        str_contains($t, 'COSTOS') => 'Registro de Costos',
        default => '',
    };
}

function sedeNormalizada(string $raw, array $sedes): string
{
    $t = mayus($raw);
    if ($t === '') {
        return '';
    }

    $mejor = '';
    $mejorDist = 3;
    foreach ($sedes as $s) {
        $d = levenshtein($t, mayus($s));
        if ($d < $mejorDist) {
            [$mejor, $mejorDist] = [$s, $d]; // tolera errores de escritura como "TARPOTO"
        }
    }

    return $mejor;
}

const MESES = ['ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8, 'SETIEMBRE' => 9, 'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12];

/** "MM/AAAA" desde un texto de mes (número o nombre) y un año. */
function mmaaaa($mes, $anio): string
{
    return str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '/' . $anio;
}

/** Interpreta textos como "1/2024-12/2024", "APERTURA 2024-CIERRE 2025" o "ENERO- DICIEMBRE 2021". Devuelve [desde, hasta] o null. */
function periodos(string $raw): ?array
{
    $t = mayus($raw);
    $mes = '(' . implode('|', array_keys(MESES)) . ')';
    $sep = '\s*(?:-|A|AL)\s*';

    if (preg_match('#^(\d{1,2})/(\d{4})' . $sep . '(\d{1,2})/(\d{4})$#', $t, $m)) {
        return [mmaaaa($m[1], $m[2]), mmaaaa($m[3], $m[4])];
    }
    if (preg_match('#^(\d{1,2})/(\d{4})' . $sep . 'CIERRE\s*(\d{4})$#', $t, $m)) {
        return [mmaaaa($m[1], $m[2]), mmaaaa(13, $m[3])];
    }
    if (preg_match('#^APERTURA\s*(\d{4})?' . $sep . 'CIERRE\s*(\d{4})$#', $t, $m)) {
        return [mmaaaa(0, $m[1] ?: $m[2]), mmaaaa(13, $m[2])];
    }
    if (preg_match('#^APERTURA\s*(\d{4})?' . $sep . $mes . '\s*(\d{4})$#', $t, $m)) {
        return [mmaaaa(0, $m[1] ?: $m[3]), mmaaaa(MESES[$m[2]], $m[3])];
    }
    if (preg_match('#^APERTURA\s*(\d{4})?' . $sep . '(\d{1,2})/(\d{4})$#', $t, $m)) {
        return [mmaaaa(0, $m[1] ?: $m[3]), mmaaaa($m[2], $m[3])];
    }
    if (preg_match('#^' . $mes . '\s*(\d{4})' . $sep . 'CIERRE\s*(\d{4})$#', $t, $m)) {
        return [mmaaaa(MESES[$m[1]], $m[2]), mmaaaa(13, $m[3])];
    }
    if (preg_match('#^' . $mes . '\s*(\d{4})?' . $sep . $mes . '\s*(\d{4})$#', $t, $m)) {
        return [mmaaaa(MESES[$m[1]], $m[2] ?: $m[4]), mmaaaa(MESES[$m[3]], $m[4])];
    }

    return null;
}

/** "243 AL 516", "DEL 2 AL 10", "104-125", "001-300" -> [desde, hasta]; null si no es un rango válido. */
function rango(string $raw): ?array
{
    if (preg_match('/(\d+)\s*(?:-|AL|A)\s*(\d+)/i', mayus($raw), $m)) {
        [$a, $b] = [(int) $m[1], (int) $m[2]];

        return $a >= 1 && $b >= $a ? [$a, $b] : null;
    }

    return null;
}

function fechaExcel($celda): ?int
{
    $v = $celda->getValue();
    if (is_numeric($v) && $v > 20000) {
        return (int) $v;
    }

    $t = trim((string) $v);
    if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $t, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
        return (int) Date::PHPToExcel(mktime(0, 0, 0, (int) $m[2], (int) $m[1], (int) $m[3]));
    }

    return null;
}

// ---------------------------------------------------------------- Lectura del modelo anterior
$lector = IOFactory::createReaderForFile($entrada);
$lector->setReadDataOnly(true);
$origen = $lector->load($entrada)->getSheetByName('DATA ORIGINAL');
if (!$origen) {
    fwrite(STDERR, "El archivo no tiene la hoja \"DATA ORIGINAL\".\n");
    exit(1);
}

const CAB_LIBROS = [
    'RUC del cliente', 'Razón social (referencia)', 'Régimen (referencia)', 'Tipo de libro', 'Tipo de hoja', 'N° de libro',
    'Sede', 'RUC de la notaría', 'Notaría', 'Fecha de legalización', 'N° de kardex', 'N° de stan', 'Folio inicial', 'Folio final',
    'Alerta: mínimo en blanco', 'Impresas: folio desde', 'Impresas: folio hasta', 'Impresas: periodo desde (MM/AAAA)',
    'Impresas: periodo hasta (MM/AAAA)', 'Observación de la impresión', 'Datos por completar (automático)',
];
const CAB_IMPRESIONES = ['RUC del cliente', 'Tipo de libro', 'N° de libro', 'Folio desde', 'Folio hasta', 'Periodo desde (MM/AAAA)', 'Periodo hasta (MM/AAAA)', 'Observación'];

$celda = fn($c, $r) => $origen->getCell([$c, $r]);
$valor = fn($c, $r) => limpio($celda($c, $r)->getValue());

$filas = [];
$continuaciones = [];   // filas repetidas del mismo libro: cada una es otra tanda de impresión
$origenes = [];          // texto original de cada fila (hoja "Datos originales")
$vistos = [];
$resumenTipos = [];
$notariasNombres = [];

for ($r = 2; $r <= $origen->getHighestDataRow(); $r++) {
    $ruc = preg_replace('/\D/', '', (string) $celda(2, $r)->getValue());
    if ($ruc === '') {
        continue;
    }

    $tipoRaw = $valor(9, $r);
    $tipo = tipoLibro($tipoRaw);
    $resumenTipos[mayus($tipoRaw) . ' => ' . ($tipo ?: '(sin equivalente)')] = ($resumenTipos[mayus($tipoRaw) . ' => ' . ($tipo ?: '(sin equivalente)')] ?? 0) + 1;

    $periodosRaw = $valor(12, $r);
    $impresasRaw = $valor(13, $r);
    $obsRaw = $valor(15, $r);
    $hojasRaw = $valor(11, $r);

    $numero = preg_match('/^\d+$/', $valor(10, $r)) ? (string) (int) $valor(10, $r) : '';
    $sede = sedeNormalizada($valor(6, $r), $sedesBD);
    $notaria = $valor(7, $r);
    if ($notaria !== '') {
        $notariasNombres[$notaria] = true;
    }

    $fecha = fechaExcel($celda(8, $r));
    $stan = preg_match('/^\d+$/', $valor(16, $r)) ? (string) (int) $valor(16, $r) : '';
    $folios = rango($hojasRaw);
    $impresas = rango($impresasRaw);
    $per = $impresas ? periodos($periodosRaw) : null;

    // Observación de la impresión: lo que no se pudo convertir (periodos en texto, observaciones del modelo anterior)
    $notas = [];
    if ($impresas) {
        if ($periodosRaw !== '' && !$per && !in_array(mayus($periodosRaw), ['NINGUNO'], true)) {
            $notas[] = 'Periodos: ' . $periodosRaw;
        }
        if ($impresasRaw !== '' && !preg_match('/^(DEL )?\d+\s*(-|AL|A)\s*\d+$/i', $impresasRaw)) {
            $notas[] = 'Hojas impresas: ' . $impresasRaw;
        }
        if ($obsRaw !== '') {
            $notas[] = $obsRaw;
        }
    }

    $hoja = preg_match('/MANUAL/i', $periodosRaw) ? 'Manual' : '';

    $faltan = [];
    if ($tipo === '') {
        $faltan[] = $tipoRaw === '' ? 'Tipo de libro (obligatorio)' : 'Tipo de libro (obligatorio: "' . $tipoRaw . '" no tiene equivalente)';
    }
    if ($hoja === '') {
        $faltan[] = 'Tipo de hoja';
    }
    if ($numero === '') {
        $faltan[] = 'N° de libro';
    }
    if ($sede === '') {
        $faltan[] = 'Sede';
    }
    if ($notaria === '') {
        $faltan[] = 'Notaría';
    } elseif (!isset($equivalencias[mb_strtoupper($notaria)])) {
        $faltan[] = 'Notaría (falta su RUC)';
    }
    if (!$fecha) {
        $faltan[] = 'Fecha de legalización';
    }
    $faltan[] = 'N° de kardex'; // el modelo anterior no lo tiene
    if ($stan === '') {
        $faltan[] = 'N° de stan';
    }
    if (!$folios) {
        $faltan[] = 'Folios';
    }

    $registro = [
        'ruc' => $ruc,
        'razon' => $valor(3, $r),
        'regimen' => $valor(5, $r),
        'tipo' => $tipo,
        'hoja' => $hoja,
        'numero' => $numero,
        'sede' => $sede,
        'rucNotaria' => $equivalencias[mb_strtoupper($notaria)] ?? '',
        'notaria' => $notaria,
        'fecha' => $fecha,
        'kardex' => '',
        'stan' => $stan,
        'fi' => $folios[0] ?? '',
        'ff' => $folios[1] ?? '',
        'minimo' => '',
        'desde' => $impresas[0] ?? '',
        'hasta' => $impresas[1] ?? '',
        'p1' => $per[0] ?? '',
        'p2' => $per[1] ?? '',
        'obs' => implode(' | ', $notas),
        'faltan' => implode(', ', $faltan),
        'origen' => $r,
        'raw' => [$tipoRaw, $valor(10, $r), $hojasRaw, $periodosRaw, $impresasRaw, $valor(14, $r), $obsRaw],
    ];

    // El modelo anterior repite el libro en varias filas cuando tiene varias tandas de impresión:
    // la primera fila crea el libro y las siguientes quedan como filas de impresión debajo de él
    $clave = ($tipo !== '' && $numero !== '') ? $ruc . '|' . $tipo . '|' . $numero : null;
    if ($clave !== null && isset($vistos[$clave])) {
        if ($impresas) {
            $continuaciones[$clave][] = ['ruc' => $ruc, 'razon' => $registro['razon'], 'regimen' => '', 'tipo' => $tipo, 'hoja' => '', 'numero' => $numero, 'sede' => '', 'rucNotaria' => '', 'notaria' => '',
                'fecha' => null, 'kardex' => '', 'stan' => '', 'fi' => '', 'ff' => '', 'minimo' => '', 'desde' => $impresas[0], 'hasta' => $impresas[1],
                'p1' => $per[0] ?? '', 'p2' => $per[1] ?? '', 'obs' => implode(' | ', $notas), 'faltan' => ''];
        }
        $origenes[] = ['origen' => $r, 'ruc' => $ruc, 'raw' => $registro['raw'], 'destino' => 'Fila de impresión del mismo libro'];
        continue;
    }

    if ($clave !== null) {
        $vistos[$clave] = true;
    }
    $registro['clave'] = $clave;
    $filas[] = $registro;
    $origenes[] = ['origen' => $r, 'ruc' => $ruc, 'raw' => $registro['raw'], 'destino' => 'Fila del libro'];
}

// Las filas de impresión van justo debajo de la fila de su libro
$conImpresiones = [];
foreach ($filas as $f) {
    $conImpresiones[] = $f;
    foreach ($continuaciones[$f['clave'] ?? ''] ?? [] as $c) {
        $conImpresiones[] = $c;
    }
}
$totalLibros = count($filas);
$filas = $conImpresiones;

// ---------------------------------------------------------------- Libro de salida (mismo diseño que la plantilla del sistema)
$excel = new Spreadsheet();

function cabecera($hoja, array $titulos, array $obligatorias)
{
    foreach ($titulos as $i => $titulo) {
        $col = Coordinate::stringFromColumnIndex($i + 1);
        $hoja->setCellValue($col . '1', $titulo);
        $hoja->getColumnDimension($col)->setWidth(max(16, min(34, mb_strlen($titulo) + 4)));
        $e = $hoja->getStyle($col . '1');
        $e->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $e->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(in_array($col, $obligatorias, true) ? '1F4E78' : '5B7C99');
        $e->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    }
    $hoja->getRowDimension(1)->setRowHeight(34);
    $hoja->freezePane('A2');
}

function desplegable($hoja, $columna, $hastaFila, $columnaListas, $cantidad)
{
    $formula = 'Listas!$' . $columnaListas . '$2:$' . $columnaListas . '$' . max(2, $cantidad + 1);
    for ($f = 2; $f <= $hastaFila; $f++) {
        $hoja->getCell($columna . $f)->getDataValidation()
            ->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank(true)->setShowDropDown(true)
            ->setShowErrorMessage(true)->setErrorTitle('Valor no válido')->setError('Elija un valor de la lista.')->setFormula1($formula);
    }
}

$ins = $excel->getActiveSheet();
$ins->setTitle('Instrucciones');
$texto = [
    'DATOS CONVERTIDOS DESDE "DATA ORIGINAL" - revise y complete en la hoja "Libros", luego impórtelo',
    '',
    '1. Todo el llenado está en la hoja "Libros": una fila por libro y, debajo, una fila más por cada tanda de impresión adicional (con el mismo RUC, tipo y N° de libro).',
    '2. La columna "Datos por completar (automático)" indica qué le falta a cada fila. Lo que falte NO impide importar: el libro queda "Por completar".',
    '3. Obligatorios para importar: RUC y Tipo de libro. Las filas sin tipo de libro saldrán con error (puede descargarlas desde la ventana de importación).',
    '4. Notaría: complete la columna "RUC de la notaría" (el sistema la busca por RUC y, si no está registrada, la registra desde la API de RUC). Sin RUC, el libro queda "Por completar".',
    '5. Tipo de hoja: solo se dedujo "Manual" cuando el modelo anterior lo decía; el resto está vacío (elíjalo en la lista).',
    '6. N° de kardex: el modelo anterior no lo tenía; queda vacío.',
    '7. Las impresiones del modelo anterior se pasaron a las columnas "Impresas: ..." cuando eran un rango de folios válido. Lo que no se pudo convertir (periodos en texto, observaciones) quedó en "Observación de la impresión".',
    '8. La hoja "Datos originales" conserva el texto original de cada fila (solo referencia, no se importa).',
    '9. Más instrucciones: descargue la plantilla desde Inventario de libros > Descargar plantilla.',
];foreach ($texto as $i => $t) {
    $ins->setCellValue('A' . ($i + 1), $t);
}
$ins->getStyle('A1')->getFont()->setBold(true);
$ins->getColumnDimension('A')->setWidth(170);

$listas = $excel->createSheet();
$listas->setTitle('Listas');
foreach (['A' => ['Tipos de libro', $tiposBD], 'B' => ['Tipos de hoja', ['Manual', 'Computarizado', 'Electrónico']], 'C' => ['Sedes', $sedesBD], 'D' => ['Notarias registradas (RUC - nombre)', $notariasBD], 'E' => ['Stan (solo referencia: sede - número)', $stansBD]] as $col => [$tit, $vals]) {
    $listas->setCellValue($col . '1', $tit);
    $listas->getStyle($col . '1')->getFont()->setBold(true);
    foreach (array_values($vals) as $i => $v) {
        $listas->setCellValueExplicit($col . ($i + 2), (string) $v, DataType::TYPE_STRING);
    }
    $listas->getColumnDimension($col)->setAutoSize(true);
}

$ultima = max(count($filas), 1) + 1;
$libros = $excel->createSheet(1);
$libros->setTitle('Libros');
cabecera($libros, CAB_LIBROS, ['A', 'D']);
foreach (['A', 'F', 'H', 'K', 'L', 'R', 'S'] as $col) {
    $libros->getStyle("{$col}2:{$col}" . ($ultima + 500))->getNumberFormat()->setFormatCode('@');
}
$libros->getStyle('J2:J' . ($ultima + 500))->getNumberFormat()->setFormatCode('dd/mm/yyyy');
desplegable($libros, 'D', $ultima + 500, 'A', count($tiposBD));
desplegable($libros, 'E', $ultima + 500, 'B', 3);
desplegable($libros, 'G', $ultima + 500, 'C', count($sedesBD));

foreach ($filas as $i => $f) {
    $r = $i + 2;
    $texto = [1 => $f['ruc'], 2 => $f['razon'], 3 => $f['regimen'], 4 => $f['tipo'], 5 => $f['hoja'], 6 => $f['numero'], 7 => $f['sede'], 8 => $f['rucNotaria'], 9 => $f['notaria'],
        11 => $f['kardex'], 12 => $f['stan'], 18 => $f['p1'], 19 => $f['p2'], 20 => $f['obs'], 21 => $f['faltan']];
    foreach ($texto as $c => $v) {
        if ($v !== '') {
            $libros->setCellValueExplicit([$c, $r], (string) $v, DataType::TYPE_STRING);
        }
    }
    if ($f['fecha']) {
        $libros->setCellValue([10, $r], $f['fecha']);
    }
    foreach ([13 => $f['fi'], 14 => $f['ff'], 16 => $f['desde'], 17 => $f['hasta']] as $c => $v) {
        if ($v !== '') {
            $libros->setCellValue([$c, $r], (int) $v);
        }
    }
}
$libros->getColumnDimension('U')->setWidth(60);

$nombres = array_keys($notariasNombres);
sort($nombres);

$orig = $excel->createSheet(3);
$orig->setTitle('Datos originales');
foreach (['Fila en DATA ORIGINAL', 'RUC', 'Dónde quedó', 'Tipo (original)', 'N° de libro (original)', 'Total de hojas legalizadas (original)', 'Periodos impresas en oficina (original)', 'Cantidad de hojas impresas (original)', 'Hojas en blanco (original)', 'Observaciones (original)'] as $i => $t) {
    $orig->setCellValue([$i + 1, 1], $t);
    $orig->getStyle(Coordinate::stringFromColumnIndex($i + 1) . '1')->getFont()->setBold(true);
    $orig->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth(28);
}
foreach ($origenes as $i => $f) {
    $r = $i + 2;
    $orig->setCellValueExplicit([1, $r], (string) $f['origen'], DataType::TYPE_STRING);
    $orig->setCellValueExplicit([2, $r], $f['ruc'], DataType::TYPE_STRING);
    $orig->setCellValueExplicit([3, $r], $f['destino'], DataType::TYPE_STRING);
    foreach ($f['raw'] as $k => $v) {
        $orig->setCellValueExplicit([4 + $k, $r], (string) $v, DataType::TYPE_STRING);
    }
}

$excel->setActiveSheetIndexByName('Libros');
(new Xlsx($excel))->save($salida);

// Notarías que aún no tienen RUC: se dejan en un CSV (Notaría;RUC) para llenarlo y volver a ejecutar el convertidor
$sinRuc = array_values(array_filter($nombres, fn($n) => !isset($equivalencias[mb_strtoupper($n)])));
$csv = __DIR__ . '/../notarias_para_completar.csv';
if ($sinRuc) {
    $h = fopen($csv, 'w');
    fwrite($h, "\xEF\xBB\xBF");
    fputcsv($h, ['Notaría', 'RUC'], ';');
    foreach ($sinRuc as $n) {
        fputcsv($h, [$n, ''], ';');
    }
    fclose($h);
}

// ---------------------------------------------------------------- Resumen
$con = fn($campo) => count(array_filter($filas, fn($f) => $f[$campo] !== '' && $f[$campo] !== null));
echo "Archivo generado: " . realpath($salida) . "\n\n";
echo "Filas del modelo anterior: " . count($origenes) . " -> libros: $totalLibros | filas de impresión adicionales (debajo de su libro): " . (count($filas) - $totalLibros) . " | clientes (RUC) distintos: " . count(array_unique(array_column($filas, 'ruc'))) . "\n";
echo "Con tipo de libro reconocido: {$con('tipo')} | sin tipo: " . (count($filas) - $con('tipo')) . "\n";
echo "Con sede: {$con('sede')} | con N° de libro: {$con('numero')} | con fecha: {$con('fecha')} | con stan: {$con('stan')} | con folios: {$con('fi')}\n";
echo "Con impresión (folios) convertida: {$con('desde')} | con periodos convertidos: {$con('p1')} | con tipo de hoja (Manual): {$con('hoja')}\n";
echo "Notarías distintas: " . count($nombres) . " | con RUC: " . (count($nombres) - count($sinRuc)) . " | sin RUC: " . count($sinRuc) . ($sinRuc ? " (lista en " . realpath($csv) . ")" : '') . "\n\n";
echo "Equivalencias de tipo de libro usadas:\n";
ksort($resumenTipos);
foreach ($resumenTipos as $k => $n) {
    echo str_pad((string) $n, 5) . $k . "\n";
}
