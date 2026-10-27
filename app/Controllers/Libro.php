<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\LibroImpresionModel;
use App\Models\LibroModel;
use App\Models\LibroSimulacionModel;
use App\Models\NotariaModel;
use App\Models\StanModel;
use App\Models\TipoLibroModel;

class Libro extends BaseController
{
    /** true cuando se está importando desde Excel (permite cargar el historial en libros por completar). */
    protected $importando = false;

    /** Usuario y fecha de la acción: $p = lib, lim o lsi; $accion = add, edit o delete. */
    protected function sello($p, $accion)
    {
        return ["{$p}_user_{$accion}" => session()->id, "{$p}_fecha_{$accion}" => date('Y-m-d H:i:s')];
    }

    protected function moduloId()
    {
        $modulo = db_connect()->table('modulos')->select('id')->where('url', 'libros-electronicos')->get()->getRowArray();

        return $modulo ? $modulo['id'] : 0;
    }

    protected function permiso($accion)
    {
        return $this->getPermisosAcciones($this->moduloId(), session()->perfil_id, $accion);
    }

    protected function datosVista()
    {
        $tipos = (new TipoLibroModel())->where('tli_estado', 1)->orderBy('tli_nombre', 'ASC')->findAll();
        $contribuyentes = (new ContribuyenteModel())->select('id, razon_social, ruc')->where('estado >', 0)->where('tipoServicio', 'CONTABLE')->orderBy('razon_social', 'ASC')->findAll();

        return [
            'tipos' => $tipos,
            'contribuyentes' => $contribuyentes,
            'regimenes' => (new LibroModel())->regimenes(),
            'tributos' => (new LibroModel())->tributosRenta(),
            'stans' => (new StanModel())->select('stan_id, sede_id, stan_numero')->where('stan_estado', 1)->orderBy('stan_numero', 'ASC')->findAll(),
            'notarias' => (new NotariaModel())->where('not_estado', 1)->orderBy('not_razon_social', 'ASC')->findAll(),
            'sedes' => (new LibroModel())->sedes(),
            'menu' => $this->permisos_menu(),
            'permiso_crear' => $this->permiso('crear'),
            'permiso_editar' => $this->permiso('editar'),
            'permiso_eliminar' => $this->permiso('eliminar'),
            'permiso_ver' => $this->permiso('ver detalle'),
            'permiso_impresion' => $this->permiso('registrar impresion'),
            'permiso_simular' => $this->permiso('simular'),
            'permiso_reponer' => $this->permiso('reponer hojas'),
        ];
    }

    protected function sinPermiso()
    {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
    }

    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('listar')) {
            return redirect()->to(base_url('home'));
        }

        return view('libro/index', $this->datosVista());
    }

    public function cliente($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('listar')) {
            return redirect()->to(base_url('home'));
        }

        $contribuyente = (new ContribuyenteModel())->select('id, razon_social, ruc')->find($id);

        if (!$contribuyente) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        return view('libro/cliente', $this->datosVista() + [
            'contribuyente' => $contribuyente,
            'regimenCliente' => $this->regimenContribuyente($id),
        ]);
    }

    /** Régimen de Renta configurado hoy en el contribuyente, consultado al abrir el modal del libro. */
    public function regimenConfigurado($conId)
    {
        if (!$this->permiso('crear') && !$this->permiso('editar')) {
            return $this->sinPermiso();
        }

        $id = (new LibroModel())->regimenDe($conId);

        return $this->response->setJSON(['regimen' => $id, 'nombre' => $this->regimenContribuyente($conId)]);
    }

    /** Régimen de Renta que el contribuyente tiene configurado (PDT RENTA), o '' si no tiene. */
    protected function regimenContribuyente($conId)
    {
        $model = new LibroModel();
        $nombres = array_column($model->tributosRenta(), 'tri_descripcion', 'id_tributo');
        $mapa = $model->regimenes();

        return $nombres[$mapa[$conId] ?? 0] ?? '';
    }

    /**
     * Todos los clientes contables (activos e inactivos) con la cantidad de libros y alertas;
     * los que no tienen libros salen con libros = 0. También devuelve la lista de libros con alerta.
     */
    public function renderClientes()
    {
        if (!$this->permiso('listar')) {
            return $this->response->setJSON(['clientes' => [], 'alertas' => []]);
        }

        $model = new LibroModel();
        $nombres = array_column($model->tributosRenta(), 'tri_descripcion', 'id_tributo');
        $mapa = $model->regimenes();

        $clientes = [];
        $alertas = [];

        $contribuyentes = (new ContribuyenteModel())->select('id, razon_social, ruc, estado')
            ->where('estado >', 0)->where('tipoServicio', 'CONTABLE')->orderBy('razon_social', 'ASC')->findAll();

        foreach ($contribuyentes as $c) {
            $clientes[$c['id']] = [
                'con_id' => $c['id'],
                'razon_social' => $c['razon_social'],
                'ruc' => $c['ruc'],
                'estado' => (int) $c['estado'],
                'regimen' => $nombres[$mapa[$c['id']] ?? 0] ?? '',
                'libros' => 0,
                'alertas' => 0,
                'estados' => [],
            ];
        }

        foreach ($model->listar() as $l) {
            $id = $l['con_id'];

            if (!isset($clientes[$id])) {
                continue;
            }

            $clientes[$id]['libros']++;
            $clientes[$id]['estados'][$l['estado']] = true;

            if (in_array($l['estado'], ['Stock bajo', 'Agotado'], true)) {
                $clientes[$id]['alertas']++;
                $alertas[] = $l;
            }
        }

        foreach ($clientes as $k => $c) {
            $clientes[$k]['estados'] = array_keys($c['estados']);
        }

        return $this->response->setJSON(['clientes' => array_values($clientes), 'alertas' => $alertas]);
    }

    public function detalle($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('ver detalle')) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        $libro = (new LibroModel())->listar($id);

        if (!$libro) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        return view('libro/detalle', $this->datosVista() + ['libroId' => $id]);
    }

    public function renderLibros($conId = null)
    {
        if (!$this->permiso('listar')) {
            return $this->response->setJSON([]);
        }

        $libros = (new LibroModel())->listar(null, $conId);

        $ver = $this->permiso('ver detalle');
        $editar = $this->permiso('editar');
        $eliminar = $this->permiso('eliminar');

        foreach ($libros as $k => $l) {
            $acciones = $ver ? '<a href="' . base_url('libros-electronicos/' . $l['lib_id']) . '" class="btn btn-primary btn-sm" title="ver"><i class="ti ti-eye f-18"></i></a> ' : '';

            if ($editar) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm modificar" title="editar" data-id="' . $l['lib_id'] . '"><i class="ti ti-edit-circle f-18"></i></button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm eliminar" title="eliminar" data-id="' . $l['lib_id'] . '"><i class="ti ti-trash f-18"></i></button>';
            }

            $libros[$k]['acciones'] = $acciones;
        }

        return $this->response->setJSON($libros);
    }

    public function dataLibro($id)
    {
        // El detalle usa "ver detalle" y el modal de edición usa "editar"
        if (!$this->permiso('ver detalle') && !$this->permiso('editar')) {
            return $this->sinPermiso();
        }

        $model = new LibroModel();
        $libro = $model->listar($id);

        if (!$libro) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Libro no encontrado']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'libro' => $libro[0],
            'bloqueo' => $model->libroAnteriorConHojas($libro[0]),
            'impresiones' => $model->impresiones($id),
            'simulaciones' => array_map(function ($s) use ($model) {
                $s['texto'] = $model->periodoTexto($s['lsi_periodo']);
                return $s;
            }, $model->simulaciones($id)),
        ]);
    }

    /**
     * Valida los datos de un libro (los mismos para el registro manual y la importación) y arma el arreglo a guardar.
     * Devuelve [mensajeDeError, null] o [null, datos]. $d puede modificarse (p. ej. el tipo "otro" se resuelve a su id).
     */
    protected function armarDatosLibro(array &$d, int $id)
    {
        // Un libro electrónico no tiene hojas, folios, sede ni numeración: solo necesita los datos básicos
        $electronico = ($d['tipoHoja'] ?? '') === 'electronico';

        // Todos los campos visibles son obligatorios
        $obligatorios = [
            'contribuyente' => 'Contribuyente',
            'regimen' => 'Régimen',
            'tipoLibro' => 'Tipo de libro',
            'tipoHoja' => 'Tipo de hoja',
        ];

        if (!$electronico) {
            $obligatorios += [
                'numero' => 'N° de libro',
                'sede' => 'Sede',
                'notaria' => 'Notaría',
                'fechaLegalizacion' => 'Fecha de legalización',
                'kardex' => 'N° de kardex',
                'stan' => 'N° de stan',
                'folioInicial' => 'Folio inicial',
                'folioFinal' => 'Folio final',
                'minimo' => 'Alerta: mínimo en blanco',
            ];
        }

        if (($d['tipoLibro'] ?? '') === 'otro') {
            $obligatorios['tipoOtro'] = 'Nombre del libro';
        }

        $faltan = [];
        foreach ($obligatorios as $campo => $etiqueta) {
            if (trim((string) ($d[$campo] ?? '')) === '') {
                $faltan[] = $etiqueta;
            }
        }

        if ($faltan) {
            return ['Todos los campos son obligatorios. Falta completar: ' . implode(', ', $faltan) . '.', null];
        }

        if (!in_array($d['tipoHoja'], ['manual', 'computarizado', 'electronico'], true)) {
            return ['Tipo de hoja no válido.', null];
        }

        // El régimen del libro debe ser el configurado en el contribuyente
        $configurado = (new LibroModel())->regimenDe($d['contribuyente']);
        $dondeCambiar = 'Para cambiarlo, vaya a Contribuyentes > menú de acciones del contribuyente > Configurar declaraciones > PDT > RENTA y luego vuelva a guardar.';

        if (!$configurado) {
            return ['El contribuyente no tiene un régimen configurado. Configúrelo primero en Contribuyentes > menú de acciones del contribuyente > Configurar declaraciones > PDT > RENTA.', null];
        }

        if ((string) $d['regimen'] !== (string) $configurado) {
            return ['El régimen del libro debe ser el configurado en el contribuyente (' . $this->regimenContribuyente($d['contribuyente']) . '). ' . $dondeCambiar, null];
        }

        if ($electronico) {
            // "Otro...": se reutiliza el tipo si ya existe o se crea uno nuevo
            if ($mensaje = $this->resolverTipoLibro($d)) {
                return [$mensaje, null];
            }

            $modelo = new LibroModel();

            // Un solo libro electrónico por contribuyente y tipo de libro
            $existente = $modelo->libroElectronico($d['contribuyente'], $d['tipoLibro'], $id);
            if ($existente) {
                return ['Ya existe el libro electrónico ' . $existente['nombre'] . ' para este contribuyente.', null];
            }

            // Un libro con impresiones o simulaciones usa hojas: no puede pasar a electrónico
            if ($id > 0 && ($modelo->impresiones($id) || $modelo->simulaciones($id))) {
                return ['No se puede cambiar a electrónico: el libro ya tiene impresiones o simulaciones registradas.', null];
            }

            $datos = [
                'con_id' => $d['contribuyente'],
                'tli_id' => $d['tipoLibro'],
                'lib_tipo_hoja' => 'electronico',
                'tri_id' => $d['regimen'] ?: null,
                'lib_numero' => null,
                'lib_sede' => '',
                'sede_id' => null,
                'not_id' => null,
                'lib_fecha_legalizacion' => null,
                'lib_kardex' => null,
                'stan_id' => null,
                'lib_folio_inicial' => 1,
                'lib_folio_final' => 1,
                'lib_total_hojas' => 0,
                'lib_minimo' => 0,
                'lib_estado' => 1,
            ];
        } else {
            // La notaría debe existir y estar activa
            if (!(new NotariaModel())->where('not_id', (int) $d['notaria'])->where('not_estado', 1)->first()) {
                return ['Seleccione una notaría activa. Puede registrarlas en Configuración > Notarias.', null];
            }

            // La sede debe existir en la tabla sede y estar activa
            $sede = (new LibroModel())->sedeActiva($d['sede']);
            if (!$sede) {
                return ['Seleccione una sede activa.', null];
            }

            // El stan debe existir, estar activo y ser de la sede del libro
            $stan = (new StanModel())->where('stan_id', (int) $d['stan'])->where('stan_estado', 1)->first();
            if (!$stan || (int) $stan['sede_id'] !== (int) $sede['id']) {
                return ['Seleccione un stan activo de la sede ' . $sede['nombre_sede'] . '. Puede registrarlos en Configuración > Stan.', null];
            }

            $fi = (int) $d['folioInicial'];
            $ff = (int) $d['folioFinal'];

            if ($fi < 1 || (int) $d['minimo'] < 1) {
                return ['El folio inicial y el mínimo de alerta deben ser mayores a 0.', null];
            }

            if ($ff < $fi) {
                return ['El folio final debe ser mayor o igual al folio inicial.', null];
            }

            // El total de hojas siempre se calcula de los folios, no se recibe del formulario
            $total = $ff - $fi + 1;

            // "Otro...": se reutiliza el tipo si ya existe o se crea uno nuevo
            if ($mensaje = $this->resolverTipoLibro($d)) {
                return [$mensaje, null];
            }

            // La fecha de legalización no puede ser futura (hasta hoy, en la zona horaria de la aplicación)
            $fecha = \DateTime::createFromFormat('Y-m-d', (string) $d['fechaLegalizacion']);
            if (!$fecha || $fecha->format('Y-m-d') !== $d['fechaLegalizacion']) {
                return ['La fecha de legalización no es válida.', null];
            }

            $hoy = (new \DateTime('now', new \DateTimeZone(config('App')->appTimezone)))->format('Y-m-d');
            if ($d['fechaLegalizacion'] > $hoy) {
                return ['La fecha de legalización no puede ser posterior a hoy (' . date('d/m/Y', strtotime($hoy)) . ').', null];
            }

            // El N° de libro no se repite para el mismo contribuyente y tipo de libro (entre los libros activos)
            $numero = trim($d['numero']);
            $existente = (new LibroModel())->libroConNumero($d['contribuyente'], $d['tipoLibro'], $numero, $id);
            if ($existente) {
                return ['Ya existe el libro ' . $existente['nombre'] . ' para este contribuyente. Use otro número de libro.', null];
            }

            $datos = [
                'con_id' => $d['contribuyente'],
                'tli_id' => $d['tipoLibro'],
                'lib_tipo_hoja' => $d['tipoHoja'],
                'tri_id' => $d['regimen'] ?: null,
                'lib_numero' => trim($d['numero']),
                'lib_sede' => $sede['nombre_sede'],
                'sede_id' => (int) $sede['id'],
                'not_id' => (int) $d['notaria'],
                'lib_fecha_legalizacion' => $d['fechaLegalizacion'] ?: null,
                'lib_kardex' => trim($d['kardex']),
                'stan_id' => (int) $d['stan'],
                'lib_folio_inicial' => $fi,
                'lib_folio_final' => $ff,
                'lib_total_hojas' => $total,
                'lib_minimo' => (int) ($d['minimo'] ?: 100),
                'lib_estado' => 1,
            ];
        }

        return [null, $datos];
    }

    public function save()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $d = $this->request->getPost();
            $id = (int) ($d['idLibro'] ?? 0);

            if (!$this->permiso($id == 0 ? 'crear' : 'editar')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            [$error, $datos] = $this->armarDatosLibro($d, $id);
            if ($error) {
                return $this->response->setJSON(['status' => 'error', 'message' => $error]);
            }

            // Guardado completo y validado: el libro deja de estar "por completar"
            $datos['lib_faltantes'] = null;

            $libro = new LibroModel();

            if ($id == 0) {
                $libroId = $libro->insert($datos + $this->sello('lib', 'add'));

                return $this->response->setJSON(['status' => 'success', 'message' => 'Se guardó correctamente.', 'id' => $libroId]);
            }

            $libro->update($id, $datos + $this->sello('lib', 'edit'));

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se editó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteLibro($id)
    {
        try {
            if (!$this->permiso('eliminar')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            // Un libro con historial de impresiones no se puede eliminar
            $model = new LibroModel();
            if ($model->impresiones($id)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se puede eliminar el libro porque ya tiene impresiones registradas.']);
            }

            $model->update($id, ['lib_estado' => 0] + $this->sello('lib', 'delete'));

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /** Si el tipo de libro es "otro", lo busca o lo crea y deja su id en $d['tipoLibro']. Devuelve un mensaje de error o null. */
    protected function resolverTipoLibro(array &$d)
    {
        if (($d['tipoLibro'] ?? '') !== 'otro') {
            return null;
        }

        $nombre = trim($d['tipoOtro'] ?? '');
        if ($nombre === '') {
            return 'Indique el nombre del libro.';
        }

        $tipos = new TipoLibroModel();
        $tipo = $tipos->where('tli_nombre', $nombre)->first();
        if ($tipo) {
            $tipos->update($tipo['tli_id'], ['tli_estado' => 1]);
            $d['tipoLibro'] = $tipo['tli_id'];
        } else {
            $d['tipoLibro'] = $tipos->insert(['tli_nombre' => $nombre, 'tli_estado' => 1]);
        }

        return null;
    }

    /** Mensaje de error si el libro es electrónico o si el libro anterior de la serie aún tiene hojas en blanco; '' si no hay impedimento. */
    protected function mensajeLibroAnterior($libroId, $accion)
    {
        $model = new LibroModel();
        $libro = $model->find($libroId);

        // Un libro importado con datos pendientes no puede imprimir ni simular hasta completarse
        // (la importación del historial de impresiones sí puede registrarlas)
        if ($libro && !empty($libro['lib_faltantes']) && !$this->importando) {
            return 'Complete primero los datos del libro (faltan: ' . $libro['lib_faltantes'] . '): no puede ' . $accion . '.';
        }

        if ($libro && $libro['lib_tipo_hoja'] === 'electronico') {
            return 'Un libro electrónico no usa hojas: no puede ' . $accion . '.';
        }

        // Al importar el historial de libros anteriores no aplica la regla del libro anterior (es para el trabajo del día a día)
        $bloqueo = $libro && !$this->importando ? $model->libroAnteriorConHojas($libro) : null;

        if (!$bloqueo) {
            return '';
        }

        return 'No puede ' . $accion . ' en este libro mientras el libro anterior (' . $bloqueo['nombre'] . ') tenga hojas en blanco: quedan ' . $bloqueo['en_blanco'] . '.';
    }

    /** Mensaje de error ('' si no hay) de las reglas de libro anterior y de periodo para registrar una impresión. */
    protected function mensajeImpresion($libroId, $p1, $p2)
    {
        // No se puede imprimir en el libro siguiente mientras el anterior tenga hojas en blanco
        if ($mensaje = $this->mensajeLibroAnterior($libroId, 'registrar impresiones')) {
            return $mensaje;
        }

        // El periodo hasta no puede ser anterior al periodo desde
        if ($p1 && $p2 && $p2 < $p1) {
            return 'El periodo hasta no puede ser anterior al periodo desde.';
        }

        // El periodo (mes y año) no puede ser anterior al de la última impresión con periodo
        $model = new LibroModel();
        $ultima = $model->ultimoPeriodoImpreso($libroId);
        $inicio = $p1 ?: $p2;

        if ($ultima && $inicio && $inicio < $ultima) {
            return 'El periodo no puede ser anterior a ' . $model->periodoTexto($ultima) . ', donde quedó la última impresión.';
        }

        return '';
    }

    /** Guarda la impresión y quita las simulaciones de sus periodos. Devuelve cuántas simulaciones quitó. */
    protected function registrarImpresion($libroId, $desde, $hasta, $p1, $p2, $observacion)
    {
        $db = db_connect();
        $db->transStart();

        (new LibroImpresionModel())->insert([
            'lib_id' => $libroId,
            'lim_folio_desde' => $desde,
            'lim_folio_hasta' => $hasta,
            'lim_periodo_desde' => $p1,
            'lim_periodo_hasta' => $p2,
            'lim_observacion' => mb_substr(trim((string) $observacion), 0, 255) ?: null,
            'lim_estado' => 1,
        ] + $this->sello('lim', 'add'));

        // Lo impreso ya es real: se quitan las simulaciones de esos periodos
        $quitadas = $this->quitarSimulaciones($libroId, $p1, $p2);

        $db->transComplete();

        return $quitadas;
    }

    public function saveImpresion()
    {
        try {
            if (!$this->permiso('registrar impresion')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            $d = $this->request->getPost();
            $desde = (int) $d['folioDesde'];
            $hasta = (int) $d['folioHasta'];

            if ($desde < 1 || $hasta < $desde) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Rango de folios inválido.']);
            }

            $p1 = $d['periodoDesde'] ?: null;
            $p2 = $d['periodoHasta'] ?: null;

            if ($mensaje = $this->mensajeImpresion($d['libroId'], $p1, $p2)) {
                return $this->response->setJSON(['status' => 'error', 'message' => $mensaje]);
            }

            $quitadas = $this->registrarImpresion($d['libroId'], $desde, $hasta, $p1, $p2, $d['observacion'] ?? '');

            $msg = 'Impresión registrada.';
            if ($quitadas > 0) {
                $msg .= ' Se quitaron ' . $quitadas . ($quitadas == 1 ? ' simulación' : ' simulaciones') . ' de los periodos impresos.';
            }

            return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Da de baja las simulaciones del libro cuyo periodo (YYYY-MM) cae entre $p1 y $p2 inclusive.
     * Si solo hay un periodo se usa ese; si vienen invertidos se intercambian. Devuelve cuántas quitó.
     */
    protected function quitarSimulaciones($libroId, $p1, $p2)
    {
        $p1 = $p1 ?: $p2;
        $p2 = $p2 ?: $p1;

        if (!$p1) {
            return 0;
        }

        if ($p1 > $p2) {
            [$p1, $p2] = [$p2, $p1];
        }

        $model = new LibroSimulacionModel();
        $ids = array_column(
            $model->where('lib_id', $libroId)->where('lsi_estado', 1)->where('lsi_periodo >=', $p1)->where('lsi_periodo <=', $p2)->findAll(),
            'lsi_id'
        );

        if ($ids) {
            $model->update($ids, ['lsi_estado' => 0] + $this->sello('lsi', 'delete'));
        }

        return count($ids);
    }

    public function deleteImpresion($id)
    {
        if (!$this->permiso('registrar impresion')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
        }

        $impresion = (new LibroImpresionModel())->where('lim_estado', 1)->find($id);

        if (!$impresion) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Impresión no encontrada.']);
        }

        // Solo se puede eliminar la última impresión del historial (la de folios más altos)
        $impresiones = (new LibroModel())->impresiones($impresion['lib_id']);
        $ultima = end($impresiones);

        if (!$ultima || (int) $ultima['lim_id'] !== (int) $id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se puede eliminar la última impresión registrada.']);
        }

        (new LibroImpresionModel())->update($id, ['lim_estado' => 0] + $this->sello('lim', 'delete'));

        return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
    }

    public function saveSimulacion()
    {
        try {
            if (!$this->permiso('simular')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            $d = $this->request->getPost();
            $hojas = (int) $d['hojas'];

            // No se puede simular en el libro siguiente mientras el anterior tenga hojas en blanco
            if ($mensaje = $this->mensajeLibroAnterior($d['libroId'], 'simular')) {
                return $this->response->setJSON(['status' => 'error', 'message' => $mensaje]);
            }

            if (empty($d['periodo']) || $hojas < 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Indique periodo y hojas.']);
            }

            // El periodo (mes y año) no puede ser anterior al de la última impresión
            $model = new LibroModel();
            $ultima = $model->ultimoPeriodoImpreso($d['libroId']);
            if ($ultima && $d['periodo'] < $ultima) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El periodo no puede ser anterior a ' . $model->periodoTexto($ultima) . ', donde quedó la última impresión.']);
            }

            // No se pueden simular más hojas de las que quedan disponibles
            $libro = $model->listar($d['libroId']);
            if (!$libro) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Libro no encontrado.']);
            }

            $disponibles = (int) $libro[0]['restantes'];
            if ($hojas > $disponibles) {
                return $this->response->setJSON(['status' => 'error', 'message' => $disponibles > 0 ? 'Solo quedan ' . $disponibles . ' hojas disponibles para simular.' : 'No quedan hojas disponibles para simular.']);
            }

            (new LibroSimulacionModel())->insert([
                'lib_id' => $d['libroId'],
                'lsi_periodo' => $d['periodo'],
                'lsi_hojas' => $hojas,
                'lsi_estado' => 1,
            ] + $this->sello('lsi', 'add'));

            return $this->response->setJSON(['status' => 'success', 'message' => 'Agregado a la simulación.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteSimulacion($id)
    {
        if (!$this->permiso('simular')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
        }

        $simulacion = (new LibroSimulacionModel())->where('lsi_estado', 1)->find($id);

        if (!$simulacion) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Simulación no encontrada.']);
        }

        // Solo se puede eliminar la última simulación: la de periodo más reciente (primera de la lista)
        $simulaciones = (new LibroModel())->simulaciones($simulacion['lib_id']);

        if (!$simulaciones || (int) $simulaciones[0]['lsi_id'] !== (int) $id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se puede eliminar la última simulación registrada.']);
        }

        (new LibroSimulacionModel())->update($id, ['lsi_estado' => 0] + $this->sello('lsi', 'delete'));

        return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
    }

    public function reponer()
    {
        try {
            if (!$this->permiso('reponer hojas')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            $id = $this->request->getPost('libroId');
            $hojas = (int) $this->request->getPost('hojas');

            if ($hojas < 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Indique las hojas nuevas.']);
            }

            $model = new LibroModel();
            $libro = $model->find($id);

            // El total sale de los folios (final - inicial + 1): reponer extiende el folio final
            $model->update($id, [
                'lib_folio_final' => $libro['lib_folio_final'] + $hojas,
                'lib_total_hojas' => $libro['lib_total_hojas'] + $hojas,
            ] + $this->sello('lib', 'edit'));

            return $this->response->setJSON(['status' => 'success', 'message' => 'Hojas repuestas.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
