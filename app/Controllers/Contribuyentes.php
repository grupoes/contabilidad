<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Models\UbigeoModel;
use App\Models\ContribuyenteModel;
use App\Models\SistemaContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\HistorialTarifaModel;
use App\Models\CertificadoDigitalModel;
use App\Models\PagosModel;

class Contribuyentes extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $sistema = new SistemaModel();
        $sistemas = $sistema->where('status', 1)->findAll();

        $certi = new CertificadoDigitalModel();

        $consulta_certificado_por_vencer = $certi->query('SELECT c.ruc, c.razon_social, cd.tipo_certificado, cd.fecha_inicio, cd.fecha_vencimiento
        FROM certificado_digital cd
        inner join contribuyentes c on c.id = cd.contribuyente_id
        WHERE cd.fecha_vencimiento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) and cd.estado = 1;')->getResult();

        return view('contribuyente/lista', compact('sistemas', 'consulta_certificado_por_vencer'));
    }

    public function allCobros()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        return view('contribuyente/cobros');
    }

    public function listaUbigeo() {
        $model = new UbigeoModel();
        $data = $model->allUbigeo();
        return $this->response->setJSON($data);
    }

    public function listaContribuyentes($filtro)
    {
        $model = new ContribuyenteModel();

        $sql = "";

        if($filtro !== 'TODOS') {
            $sql = "WHERE c.tipoServicio = '$filtro'";
        }
        
        $data = $model->query("SELECT 
            c.*, 
            -- Verificar si tiene sistema
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM sistemas_contribuyente sc 
                    WHERE sc.contribuyente_id = c.id
                ) THEN 'SI'
                ELSE 'NO'
            END AS tiene_sistema,
            -- Verificar si tiene certificado digital
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM certificado_digital cd 
                    WHERE cd.contribuyente_id = c.id and cd.estado = 1
                ) THEN 'SI'
                ELSE 'NO'
            END AS tiene_certificado,
            -- Verificar si el certificado está vencido
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM certificado_digital cd 
                    WHERE cd.contribuyente_id = c.id and cd.estado = 1
                    AND cd.fecha_vencimiento >= CURDATE()
                ) THEN 'NO' -- Tiene un certificado válido
                ELSE 'SI' -- No tiene certificado válido o está vencido
            END AS certificado_vencido
        FROM contribuyentes c $sql order by c.id desc")->getResult();

        return $this->response->setJSON($data);
    }

    public function guardar()
    {
        $sistema = new SistemaContribuyenteModel();
        $model = new ContribuyenteModel();

        $model->db->transStart();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $sistemas = "";

            if(isset($data['nameSystem'])) {
                $sistemas = $data['nameSystem'];
            }

            $idTabla = $data['idTable'];

            $verificar = $model->where('ruc', $data['numeroDocumento'])->first();

            $datos = [
                'ruc' => $data['numeroDocumento'],
                'razon_social' => $data['razonSocial'],
                'nombre_comercial' => $data['nombreComercial'],
                'direccion_fiscal' => $data['direccionFiscal'],
                'ubigeo_id' => $data['ubigeo'],
                'urbanizacion' => $data['urbanizacion'],
                'tipoSuscripcion' => $data['tipoSuscripcion'],
                'tipoServicio' => $data['tipoServicio'],
                'tipoPago' => $data['tipoPago'],
                'costoMensual' => $data['costoMensual'],
                'costoAnual' => $data['costoAnual'],
                'diaCobro' => $data['diaCobro'],
                'fechaContrato' => $data['fechaContrato'],
                'telefono' => "",
                'correo' => "",
                'usuario_secundario' => "",
                'clave_usuario_secundario' => "",
                'acceso' => $data['numeroDocumento'],
                'estado' => 1
            ];

            $tarifa = new HistorialTarifaModel();

            if($idTabla === "0") {
                if($verificar) {
                    return $this->response->setJSON(['status' => 'error', 'message' => "El RUC ya se encuentra registrado."]);
                }

                $model->insert($datos);

                $contribuyente_id = $model->insertID();

                if(isset($data['nameSystem'])) {
                    for ($i=0; $i < count($sistemas); $i++) { 
                        $sistema->insert([
                            'contribuyente_id' => $contribuyente_id,
                            'system_id' => $sistemas[$i]
                        ]);
                    }
                }

                $tarifa->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'fecha_inicio' => $data['fechaContrato'],
                    'monto_mensual' => $data['costoMensual'],
                    'monto_anual' => $data['costoAnual'],
                    'estado' => 1
                ]);

                $model->db->transComplete();

                if ($model->db->transStatus() === false) {
                    throw new \Exception("Error al realizar la operación.");
                }

                return $this->response->setJSON(['status' => 'success', 'message' => "Contribuyente registrado correctamente."]);

            } else {
                $model->update($idTabla,$datos);

                $sistema->where('contribuyente_id', $idTabla)->delete();

                if(isset($data['nameSystem'])) {
                    for ($i=0; $i < count($sistemas); $i++) { 
                        $sistema->insert([
                            'contribuyente_id' => $idTabla,
                            'system_id' => $sistemas[$i]
                        ]);
                    }
                }

                $tarifaData = $tarifa->where('contribuyente_id', $idTabla)->orderBy('id', 'desc')->first();

                $idTarifa = $tarifaData['id'];

                $dataTarifa = array(
                    "monto_mensual" => $data['costoMensual'],
                    "monto_anual" => $data['costoAnual']
                );

                $tarifa->update($idTarifa, $dataTarifa);

                return $this->response->setJSON(['status' => 'success', 'message' => "Contribuyente editado correctamente."]);
            }

            

        } catch (\Exception $e) {
            $model->db->transRollback();
            
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getContribuyente($id)
    {
        $model = new ContribuyenteModel();
        $data = $model->find($id);

        $sistema = new SistemaContribuyenteModel();
        $sistemas = $sistema->where('contribuyente_id', $id)->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $data, 'sistemas' => $sistemas]);
    }

    public function getTarifaContribuyente($id)
    {
        $tarifa = new HistorialTarifaModel();
        $contri = new ContribuyenteModel();

        $data_tarifa = $tarifa->where('contribuyente_id', $id)->where('estado', 1)->orderBy('id', 'desc')->findAll();
        $data_contribuyente = $contri->find($id);

        return $this->response->setJSON(['status' => 'success', 'data_tarifa' => $data_tarifa, 'data_contribuyente' => $data_contribuyente]);
        
    }

    public function getCertificadoDigital($id)
    {
        $certificado = new CertificadoDigitalModel();
        $contri = new ContribuyenteModel();

        $data_certificado = $certificado->where('contribuyente_id', $id)->where('estado !=', 0)->orderBy('id', 'desc')->findAll();
        $data_contribuyente = $contri->find($id);

        return $this->response->setJSON(['status' => 'success', 'data_certificado' => $data_certificado, 'data_contribuyente' => $data_contribuyente]);
    }

    public function guardarTarifa()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $tarifa = new HistorialTarifaModel();

            $last_tarifa = $tarifa->where('contribuyente_id', $data['idTableTarifa'])->where('estado', 1)->orderBy('fecha_inicio', 'DESC')->first();

            if($data['fechaInicioTarifa'] <= $last_tarifa['fecha_inicio']) {
                return $this->response->setJSON(['status' => 'error', 'message' => "No puedes colocar una fecha menor o igual a la ultima fecha de la tarifa"]);
            }

            if($last_tarifa) {
                $tarifa->update($last_tarifa['id'], ['fecha_fin' => $data['fechaInicioTarifa']]);
            }

            $tarifa->insert([
                'contribuyente_id' => $data['idTableTarifa'],
                'fecha_inicio' => $data['fechaInicioTarifa'],
                'monto_mensual' => $data['montoMensualTarifa'],
                'monto_anual' => $data['montoAnualTarifa'],
                'estado' => 1
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => "Tarifa registrada correctamente."]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteTarifa($id)
    {
        $model = new HistorialTarifaModel();

        $data = array('estado' => 0);

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => "Tarifa eliminada correctamente."]);
    }

    public function listaHonorariosCobros($select)
    {
        $model = new ContribuyenteModel();

        $sql = "";

        if($select !== 'TODOS') {
            $sql = "WHERE c.tipoServicio = '$select'";
        }

        $datos = $model->query("SELECT c.id,
            c.razon_social,
            c.ruc,
            c.tipoPago,
            c.diaCobro,
            c.tipoServicio,
            c.tipoSuscripcion,
            -- Cálculo de meses de deuda
            TIMESTAMPDIFF(MONTH,
                CASE 
                    WHEN MAX(p.fecha_pago) IS NULL THEN
                        -- Si no hay pagos, considerar desde el registro del contribuyente
                        DATE_ADD(DATE_FORMAT(c.fechaContrato, '%Y-%m-01'), INTERVAL c.diaCobro - 1 DAY)
                    WHEN c.tipoPago = 'ADELANTADO' THEN
                        -- Para pagos adelantados, considerar el inicio del mes del último pago
                        DATE_FORMAT(LAST_DAY(MAX(p.fecha_pago)), '%Y-%m-01')
                    WHEN c.tipoPago = 'ATRASADO' THEN
                        -- Para pagos atrasados, considerar el día de cobro del último mes pagado
                        DATE_ADD(DATE_FORMAT(LAST_DAY(MAX(p.fecha_pago)), '%Y-%m-01'), INTERVAL c.diaCobro - 1 DAY)
                END,
                CURRENT_DATE
            ) AS meses_deuda
        FROM contribuyentes c
        LEFT JOIN pagos p ON c.id = p.contribuyente_id
        $sql
        GROUP BY c.id, c.razon_social, c.tipoPago, c.diaCobro, c.fechaContrato;")->getResult();

        return $this->response->setJSON($datos);
    }

    public function guardarCertificadoDigital()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $certificado = new CertificadoDigitalModel();

            $clave = "";
            $ruta = "";
            $nameFile = "";

            if($data['tipo_certificado'] === 'PROPIO') {
                if ($this->request->getFile('file_certificado')->isValid() && !$this->request->getFile('file_certificado')->hasMoved()) {

                    $archivo = $this->request->getFile('file_certificado');
                
                    $nombreOriginal = $archivo->getClientName();
                    $extension = $archivo->getClientExtension();
    
                    if (!in_array($extension, ['pfx', 'cer', 'p12'])) {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos con extensión .pfx o .cer.']);
                    }
    
                    $archivo->move(WRITEPATH . 'uploads/certificadoDigital/', $nombreOriginal);
    
                    // Ruta donde se guardó el archivo
                    $rutaArchivo = WRITEPATH . 'uploads/certificadoDigital/' . $nombreOriginal;
    
                    $traer_ultimo = $certificado->where('contribuyente_id', $data['idTableCertificado'])->orderBy('id', 'DESC')->first();
    
                    if($traer_ultimo) {
                        $actualizar = array("estado" => 2);
    
                        $certificado->update($traer_ultimo['id'], $actualizar);
                    }

                    $codigoAleatorio = bin2hex(random_bytes(4));

                    $clave = $data['claveCertificado'];
                    $ruta = $rutaArchivo;
                    $nameFile = $codigoAleatorio . "_" .$nombreOriginal;
    
                } else {
                    // Manejar el caso donde no se envió un archivo válido
                    $archivo = null;
                    // Opcional: Mensaje de error
                    $error = $this->request->getFile('archivo')->getErrorString();
    
                    return $this->response->setJSON(['status' => 'error', 'message' => $error]);
                }
            } else {
                $traer_ultimo = $certificado->where('contribuyente_id', $data['idTableCertificado'])->orderBy('id', 'DESC')->first();
    
                if($traer_ultimo) {
                    $actualizar = array("estado" => 2);
    
                    $certificado->update($traer_ultimo['id'], $actualizar);
                }
            }

            $certificado->insert([
                'contribuyente_id' => $data['idTableCertificado'],
                'tipo_certificado' => $data['tipo_certificado'],
                'fecha_inicio' => $data['fechaInicioCertificado'],
                'fecha_vencimiento' => $data['fechaVencimientoCertificado'],
                'clave' => $clave,
                'ruta' => $ruta,
                'nameFile' => $nameFile,
                'estado' => 1
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => "Certificado registrado correctamente."]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function descargarCertificado($nameFile)
    {
        // Ruta completa del archivo
        $rutaArchivo = WRITEPATH . 'uploads/certificadoDigital/' . $nameFile;
    
        // Verificar si el archivo existe
        if (file_exists($rutaArchivo)) {
            // Descargar el archivo
            return $this->response->download($rutaArchivo, null);
        } else {
            // Retornar error si no se encuentra el archivo
            return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo no existe.']);
        }
    }

    public function deleteCertificadoDigital($id)
    {
        $model = new CertificadoDigitalModel();

        $data = array("estado" => 0);

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'El elimino correctamente el certificado']);
    }

}
