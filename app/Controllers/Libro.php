<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\LibroImpresionModel;
use App\Models\LibroModel;
use App\Models\LibroSimulacionModel;
use App\Models\TipoLibroModel;

class Libro extends BaseController
{
    private $sedes = ['Tarapoto', 'Yurimaguas'];

    private function moduloId()
    {
        $modulo = db_connect()->table('modulos')->select('id')->where('url', 'libros-electronicos')->get()->getRowArray();

        return $modulo ? $modulo['id'] : 0;
    }

    private function permiso($accion)
    {
        return $this->getPermisosAcciones($this->moduloId(), session()->perfil_id, $accion);
    }

    private function datosVista()
    {
        $tipos = (new TipoLibroModel())->where('tli_estado', 1)->findAll();
        $contribuyentes = (new ContribuyenteModel())->select('id, razon_social, ruc')->where('estado', 1)->where('tipoServicio', 'CONTABLE')->orderBy('razon_social', 'ASC')->findAll();

        return [
            'tipos' => $tipos,
            'contribuyentes' => $contribuyentes,
            'regimenes' => (new LibroModel())->regimenes(),
            'tributos' => (new LibroModel())->tributosRenta(),
            'sedes' => $this->sedes,
            'menu' => $this->permisos_menu(),
            'permiso_crear' => $this->permiso('crear'),
            'permiso_editar' => $this->permiso('editar'),
            'permiso_eliminar' => $this->permiso('eliminar'),
        ];
    }

    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        return view('libro/index', $this->datosVista());
    }

    public function cliente($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
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

    /** Régimen de Renta que el contribuyente tiene configurado (PDT RENTA), o '' si no tiene. */
    private function regimenContribuyente($conId)
    {
        $model = new LibroModel();
        $nombres = array_column($model->tributosRenta(), 'tri_descripcion', 'id_tributo');
        $mapa = $model->regimenes();

        return $nombres[$mapa[$conId] ?? 0] ?? '';
    }

    /** Clientes con al menos un libro (cantidad de libros y alertas) y la lista de libros con alerta. */
    public function renderClientes()
    {
        $model = new LibroModel();
        $nombres = array_column($model->tributosRenta(), 'tri_descripcion', 'id_tributo');
        $mapa = $model->regimenes();

        $clientes = [];
        $alertas = [];

        foreach ($model->listar() as $l) {
            $id = $l['con_id'];

            if (!isset($clientes[$id])) {
                $clientes[$id] = [
                    'con_id' => $id,
                    'razon_social' => $l['razon_social'],
                    'ruc' => $l['ruc'],
                    'regimen' => $nombres[$mapa[$id] ?? 0] ?? '',
                    'libros' => 0,
                    'alertas' => 0,
                    'estados' => [],
                ];
            }

            $clientes[$id]['libros']++;
            $clientes[$id]['estados'][$l['estado']] = true;

            if ($l['estado'] !== 'OK') {
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

        $libro = (new LibroModel())->listar($id);

        if (!$libro) {
            return redirect()->to(base_url('libros-electronicos'));
        }

        return view('libro/detalle', $this->datosVista() + ['libroId' => $id]);
    }

    public function renderLibros($conId = null)
    {
        $libros = (new LibroModel())->listar(null, $conId);

        $editar = $this->permiso('editar');
        $eliminar = $this->permiso('eliminar');

        foreach ($libros as $k => $l) {
            $acciones = '<a href="' . base_url('libros-electronicos/' . $l['lib_id']) . '" class="btn btn-primary btn-sm" title="ver"><i class="ti ti-eye f-18"></i></a> ';

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
        $model = new LibroModel();
        $libro = $model->listar($id);

        if (!$libro) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Libro no encontrado']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'libro' => $libro[0],
            'impresiones' => $model->impresiones($id),
            'simulaciones' => array_map(function ($s) use ($model) {
                $s['texto'] = $model->periodoTexto($s['lsi_periodo']);
                return $s;
            }, $model->simulaciones($id)),
        ]);
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

            $fi = (int) ($d['folioInicial'] ?: 1);
            $ff = (int) $d['folioFinal'];

            if (empty($d['contribuyente']) || empty($d['tipoLibro'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Complete contribuyente y tipo de libro.']);
            }

            if ($ff < $fi) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El folio final debe ser mayor o igual al folio inicial.']);
            }

            // El total de hojas siempre se calcula de los folios, no se recibe del formulario
            $total = $ff - $fi + 1;

            // "Otro...": se reutiliza el tipo si ya existe o se crea uno nuevo
            if ($d['tipoLibro'] === 'otro') {
                $nombre = trim($d['tipoOtro'] ?? '');
                if ($nombre === '') {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Indique el nombre del libro.']);
                }

                $tipos = new TipoLibroModel();
                $tipo = $tipos->where('tli_nombre', $nombre)->first();
                if ($tipo) {
                    $tipos->update($tipo['tli_id'], ['tli_estado' => 1]);
                    $d['tipoLibro'] = $tipo['tli_id'];
                } else {
                    $d['tipoLibro'] = $tipos->insert(['tli_nombre' => $nombre, 'tli_estado' => 1]);
                }
            }

            $sede = in_array($d['sede'], $this->sedes) ? $d['sede'] : $this->sedes[0];

            $datos = [
                'con_id' => $d['contribuyente'],
                'tli_id' => $d['tipoLibro'],
                'tri_id' => $d['regimen'] ?: null,
                'lib_numero' => trim($d['numero']),
                'lib_sede' => $sede,
                'lib_notaria' => trim($d['notaria']),
                'lib_fecha_legalizacion' => $d['fechaLegalizacion'] ?: null,
                'lib_kardex' => trim($d['kardex']),
                'lib_stan' => trim($d['stan']),
                'lib_folio_inicial' => $fi,
                'lib_folio_final' => $ff,
                'lib_total_hojas' => $total,
                'lib_minimo' => (int) ($d['minimo'] ?: 100),
                'lib_observacion' => trim($d['observacion']),
                'lib_estado' => 1,
            ];

            $libro = new LibroModel();

            if ($id == 0) {
                $libroId = $libro->insert($datos);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Se guardó correctamente.', 'id' => $libroId]);
            }

            $libro->update($id, $datos);

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

            (new LibroModel())->update($id, ['lib_estado' => 0]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function saveImpresion()
    {
        try {
            if (!$this->permiso('editar')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            $d = $this->request->getPost();
            $desde = (int) $d['folioDesde'];
            $hasta = (int) $d['folioHasta'];

            if ($desde < 1 || $hasta < $desde) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Rango de folios inválido.']);
            }

            (new LibroImpresionModel())->insert([
                'lib_id' => $d['libroId'],
                'lim_folio_desde' => $desde,
                'lim_folio_hasta' => $hasta,
                'lim_periodo_desde' => $d['periodoDesde'] ?: null,
                'lim_periodo_hasta' => $d['periodoHasta'] ?: null,
                'lim_estado' => 1,
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Impresión registrada.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteImpresion($id)
    {
        if (!$this->permiso('editar')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
        }

        (new LibroImpresionModel())->update($id, ['lim_estado' => 0]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
    }

    public function saveSimulacion()
    {
        try {
            if (!$this->permiso('editar')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
            }

            $d = $this->request->getPost();
            $hojas = (int) $d['hojas'];

            if (empty($d['periodo']) || $hojas < 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Indique periodo y hojas.']);
            }

            (new LibroSimulacionModel())->insert([
                'lib_id' => $d['libroId'],
                'lsi_periodo' => $d['periodo'],
                'lsi_hojas' => $hojas,
                'lsi_estado' => 1,
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Agregado a la simulación.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteSimulacion($id)
    {
        if (!$this->permiso('editar')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
        }

        (new LibroSimulacionModel())->update($id, ['lsi_estado' => 0]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
    }

    public function reponer()
    {
        try {
            if (!$this->permiso('editar')) {
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
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Hojas repuestas.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
