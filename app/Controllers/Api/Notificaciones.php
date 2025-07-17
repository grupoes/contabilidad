<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use Resend\Resend;

use App\Models\FechaDeclaracionModel;
use App\Models\ContribuyenteModel;
use App\Models\ContactosContribuyenteModel;
use App\Models\EnviosModel;
use App\Models\ContratosModel;
use App\Models\HonorariosModel;
use App\Models\FacturasHonorariosModel;
use App\Models\MesModel;
use App\Models\AnioModel;
use App\Models\PdtRentaModel;

use DateTime;

class Notificaciones extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */

    protected $format    = 'json';

    public function index()
    {
        $fecha = new FechaDeclaracionModel();
        $contrib = new ContribuyenteModel();
        $contacto = new ContactosContribuyenteModel();

        $date = date('Y-m-d');

        $consulta = $fecha->query("SELECT id_numero, MIN(fecha_declaracion.fecha_notificar) AS notificacion, MIN(fecha_declaracion.fecha_exacta) as fecha_exacta, MIN(anio.anio_descripcion) as anio, MIN(mes.mes_descripcion) as mes
        FROM fecha_declaracion
        INNER JOIN tributo ON tributo.id_tributo = fecha_declaracion.id_tributo
        INNER JOIN anio ON anio.id_anio = fecha_declaracion.id_anio 
        INNER JOIN mes ON mes.id_mes = fecha_declaracion.id_mes
        WHERE '$date' BETWEEN fecha_declaracion.fecha_notificar AND fecha_declaracion.fecha_exacta AND tributo.id_pdt = 1
        GROUP BY id_numero")->getResult();

        $empresas = [];

        setlocale(LC_TIME, 'es_ES.UTF-8');

        foreach ($consulta as $key => $value) {
            $fecha_obj = new \DateTime($value->fecha_exacta);

            $formatter = new \IntlDateFormatter(
                'es_ES',                      // Locale
                \IntlDateFormatter::FULL,     // Fecha larga (puedes usar MEDIUM, SHORT, etc.)
                \IntlDateFormatter::NONE,     // No mostrar la hora
                'America/Lima',              // Zona horaria
                \IntlDateFormatter::GREGORIAN
            );

            $formatter->setPattern("EEEE d 'de' MMMM 'de' y");

            $letraFecha = $formatter->format($fecha_obj);

            $digito = $value->id_numero - 1;
            $emp = $contrib->query("SELECT c.id, c.ruc, c.razon_social, c.nombre_comercial, c.direccion_fiscal, nw.link FROM contribuyentes as c inner join numeros_whatsapp as nw ON nw.id = c.numeroWhatsappId WHERE c.tipoServicio = 'CONTABLE' AND c.estado = 1 and RIGHT(c.ruc, 1) = '$digito' ")->getResult();

            foreach ($emp as $key1 => $value1) {
                $contactos = $contacto->where('contribuyente_id', $value1->id)->where('estado', 1)->findAll();

                $emp[$key1]->contactos = $contactos;
                $emp[$key1]->fechaExacta = $letraFecha;
                $emp[$key1]->periodo = strtoupper($value->mes . " " . $value->anio);
            }

            $empresas = array_merge($empresas, $emp);
        }

        return $this->respond($empresas);
    }

    public function balance()
    {
        $fecha = new FechaDeclaracionModel();
        $contrib = new ContribuyenteModel();
        $contacto = new ContactosContribuyenteModel();

        $date = date('Y-m-d');

        $consulta = $fecha->query("(SELECT id_numero, MIN(fecha_declaracion.fecha_notificar) AS notificacion
        FROM fecha_declaracion 
        INNER JOIN tributo ON tributo.id_tributo = fecha_declaracion.id_tributo 
        WHERE fecha_declaracion.fecha_notificar = '$date' 
        AND tributo.id_pdt = 3 
        GROUP BY id_numero)
        UNION
        (SELECT id_numero, MIN(fecha_declaracion.fecha_exacta) AS notificacion
        FROM fecha_declaracion 
        INNER JOIN tributo ON tributo.id_tributo = fecha_declaracion.id_tributo 
        WHERE fecha_declaracion.fecha_exacta = '$date' 
        AND tributo.id_pdt = 3 
        GROUP BY id_numero);
        ")->getResult();

        $empresas = [];

        foreach ($consulta as $key => $value) {
            $digito = $value->id_numero - 1;
            $emp = $contrib->query("SELECT * FROM contribuyentes WHERE estado = 1 and RIGHT(ruc, 1) = '$digito'")->getResult();

            foreach ($emp as $key1 => $value1) {
                $contactos = $contacto->where('contribuyente_id', $value1->id)->where('estado', 1)->findAll();

                $emp[$key1]->contactos = $contactos;
            }

            $empresas = array_merge($empresas, $emp);
        }

        return $this->respond($empresas);
    }

    public function mensajesPendientes()
    {
        $envio = new EnviosModel();

        $consulta = $envio->where('estado', 'pendiente')->findAll(20);

        return $this->respond($consulta);
    }

    public function updateMessage()
    {
        $envio = new EnviosModel();

        $data = $this->request->getJSON();

        $id = $data->id ?? null;
        $fecha_envio = $data->fecha_envio ?? null;
        $estado = $data->estado ?? null;

        $envio->update($id, ['fecha_envio' => $fecha_envio, 'estado' => $estado]);

        return $this->respond(['message' => "Mensaje actualizado correctamente"]);
    }

    public function sendEmail()
    {

        /*$resend = Resend::client('re_5wjnGFy9_2269kCpAmET27oqTKKa1eSQv');

        $result = $resend->emails->send([
            'from' => 'Acme <contabilidad@grupoesconsultores.com>',
            'to' => ['desarrollo.tecnologico.tarapoto@gmail.com'],
            'subject' => 'hello world',
            'html' => '<strong>it works!</strong>',
        ]);

        return $this->respond($result);*/
    }

    public function sendFacturas()
    {
        try {
            $datos = $this->request->getJSON();

            $ruc = $datos->ruc ?? null;
            $razonSocial = $datos->razonSocial ?? null;
            $productoName = $datos->productoName ?? null;
            $precio = $datos->precio ?? null;

            $fecha_comprobante = date('d/m/Y');

            $data["contribuyente"] = array(
                "token_contribuyente"           => getenv("API_KEY_GENERAR_FACTURA"), //Token del contribuyente
                "id_usuario_vendedor"           => getenv("ID_USUARIO_VENDEDOR"), //Debes ingresar el ID de uno de tus vendedores (opcional)
                "tipo_proceso"                  => getenv("TIPO_ENVIO_SUNAT"), //Funcional en una siguiente versión. El ambiente al que se enviará, puede ser: {prueba, produccion}
                "tipo_envio"                    => "inmediato" //funcional en una siguiente versión. Aquí puedes definir si se enviará de inmediato a sunat
            );

            $data["cliente"] = array(
                "tipo_docidentidad"             => 6, //{0: SINDOC, 1: DNI, 6: RUC}
                "numerodocumento"               => $ruc, //Es opcional solo cuando tipo_docidentidad es 0, caso contrario se debe ingresar el número de ruc
                "nombre"                        => $razonSocial, //Es opcional solo cuando tipo_docidentidad es 1, caso contrario es obligatorio ingresar aquí la razón social
                "email"                         => "", //opcional: (si tiene correo se enviará automáticamente el email)
                "direccion"                     => "", //opcional: 
                "ubigeo"                        => "",
                "sexo"                          => "", //opcional: masculino
                "fecha_nac"                     => "", //opcional: 
                "celular"                       => "" //opcional
            );

            $data["cabecera_comprobante"] = array(
                "tipo_documento"                => "01",  //{"01": FACTURA, "03": BOLETA}
                "moneda"                        => "PEN",  //{"USD", "PEN"}
                "idsucursal"                    => getenv("ID_SUCURSAL"),  //{ID DE SUCURSAL}
                "id_condicionpago"              => "",  //condicionpago_comprobante
                "fecha_comprobante"             => $fecha_comprobante,  //fecha_comprobante
                "nro_placa"                     => "",  //nro_placa_vehiculo
                "nro_orden"                     => "",  //nro_orden
                "guia_remision"                 => "",  //guia_remision_manual
                "descuento_monto"               => 0,  // (máximo 2 decimales) (monto total del descuento)
                "descuento_porcentaje"          => 0,  // (máximo 2 decimales) (porcentaje total del descuento)
                "observacion"                   => "",  //observacion_documento
            );

            $detalle[] = array(
                "idproducto"                    => 91282,  //(opcional, puede ser cero) (si el idproducto coincide con la BD se llevará control del stock)
                "codigo"                        => getenv("CODIGO_PRODUCTO"), //codigo del producto (requerido)
                "afecto_icbper"                 => "no",  //"afecto_icbper":"no",
                "id_tipoafectacionigv"          => 20,  //"id_tipoafectacionigv":"10",
                "descripcion"                   => $productoName,  //"descripcion":"Zapatos",
                "idunidadmedida"                => 'NIU',  //{NIU para unidades, ZZ para servicio}
                "precio_venta"                  => $precio,  //Precio unitario de venta (inc. IGV),
                "cantidad"                      => 1,  //"cantidad":"1"
            );

            $data["detalle"] = $detalle;

            $ruta = getenv("API_GENERAR_FACTURA");
            $data_json = json_encode($data);

            if (!$ruta) {
                return $this->respond([
                    'respuesta' => 'error',
                    'mensaje' => 'No se definió la URL de la API'
                ], 500);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ruta);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    "Authorization: Bearer " . getenv("API_KEY_GENERAR_FACTURA"),
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                )
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $respuesta  = curl_exec($ch);
            $errorCurl = curl_error($ch);

            curl_close($ch);

            if ($errorCurl) {
                return $this->respond([
                    'respuesta' => 'error',
                    'mensaje' => 'Error en conexión CURL',
                    'detalle' => $errorCurl
                ], 500);
            }

            $respuestaDecodificada = json_decode($respuesta, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->respond([
                    'respuesta' => 'error',
                    'mensaje' => 'Respuesta de API inválida (JSON mal formado)',
                    'raw' => $respuesta
                ], 500);
            }

            return $this->respond($respuestaDecodificada);
        } catch (\Exception $e) {
            return $this->respond([
                'respuesta' => 'error',
                'mensaje' => 'Error inesperado en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listEmpresas()
    {
        $contrib = new ContribuyenteModel();

        $periodo = date('Y-m');

        $empresas = $contrib->select('id, ruc, razon_social, tipoServicio, tipoPago')->where('estado', 1)->where("tipoSuscripcion", 'NO GRATUITO')->findAll();

        foreach ($empresas as $key => $value) {
            $id = $value['id'];

            $monto = $this->verificar_monto_mensual($id, $periodo);

            $empresas[$key]['monto_mensual'] = $monto;

            if ($value['tipoServicio'] == 'ALQUILER') {
                $mes = date('m');
                $anio = date('Y');
                $mesLetra = $this->getMes($mes) . ' ' . $anio;
                $descripcion = "SERVICIO DE ARRENDAMIENTO DEL SOFTWARE DEL MES DE " . $mesLetra;
            } else {
                if ($value['tipoPago'] == 'ATRASADO') {
                    $fecha = DateTime::createFromFormat('Y-m', $periodo);
                    $fecha->modify('-1 month');

                    $mes = $fecha->format('m');
                    $anio = $fecha->format('Y');
                    $mesLetra = $this->getMes($mes) . ' ' . $anio;

                    $descripcion = "SERVICIO DE CONTABILIDAD DEL MES DE " . $mesLetra;
                } else {
                    $mes = date('m');
                    $anio = date('Y');
                    $mesLetra = $this->getMes($mes) . ' ' . $anio;
                    $descripcion = "SERVICIO DE CONTABILIDAD DEL MES DE " . $mesLetra;
                }
            }

            $empresas[$key]['descripcion'] = $descripcion;
        }

        return $this->respond($empresas);
    }

    public function verificar_monto_mensual($id, $periodo)
    {
        $contratos = new ContratosModel();

        $mensual = $contratos->query("SELECT c.contribuyenteId, ht.fecha_inicio, ht.monto_mensual
        FROM historial_tarifas ht
        INNER JOIN contratos c ON ht.contratoId = c.id
        WHERE c.contribuyenteId = $id AND c.estado = 1 AND DATE_FORMAT(ht.fecha_inicio, '%Y-%m') <= '$periodo' and ht.estado = 1 ORDER BY ht.fecha_inicio DESC;")->getResult();

        return $mensual[0]->monto_mensual;
    }

    public function saveHonorario()
    {
        try {
            $honorario = new HonorariosModel();

            $datos = $this->request->getJSON();

            $ruc = $datos->ruc;

            $anio = date('Y');
            $mes = date('m');

            $descripcion = $this->getMes($mes) . ' ' . $anio;

            $insert = [
                'descripcion' => $descripcion,
                'mes' => $mes,
                'year' => $anio,
                'estado' => 1
            ];

            $honorario->insert($insert);

            $id = $honorario->getInsertID();
            $registro = $honorario->find($id);

            return $this->respond([
                'status' => 'success',
                'message' => 'Honorario creado correctamente',
                'registro' => $registro
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al crear el honorario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function saveFactura()
    {
        $facturas = new FacturasHonorariosModel();

        try {
            $datos = $this->request->getJSON();

            $honorario_id = $datos->honorario_id ?? null;
            $contribuyente_id = $datos->contribuyente_id ?? null;
            $tipo_doc = $datos->tipo_doc ?? null;
            $serie_comprobante = $datos->serie_comprobante ?? null;
            $numero_comprobante = $datos->numero_comprobante ?? null;
            $tipo_envio_sunat = $datos->tipo_envio_sunat ?? null;
            $titulo = $datos->titulo ?? null;
            $mensaje = $datos->mensaje ?? null;
            $url_absoluta_a4 = $datos->url_absoluta_a4 ?? null;
            $url_absoluta_ticket = $datos->url_absoluta_ticket ?? null;
            $anio = $datos->anio ?? null;
            $mes = $datos->mes ?? null;
            $descripcion = $datos->descripcion ?? null;
            $estado = $datos->estado ?? null;
            $monto = $datos->monto ?? null;

            $datos = [
                'honorario_id' => $honorario_id,
                'contribuyente_id' => $contribuyente_id,
                'tipo_doc' => $tipo_doc,
                'serie_comprobante' => $serie_comprobante,
                'numero_comprobante' => $numero_comprobante,
                'tipo_envio_sunat' => $tipo_envio_sunat,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url_absoluta_a4' => $url_absoluta_a4,
                'url_absoluta_ticket' => $url_absoluta_ticket,
                'anio' => $anio,
                'mes' => $mes,
                'descripcion' => $descripcion,
                'estado' => $estado,
                'monto' => $monto
            ];

            $facturas->insert($datos);

            return $this->respond([
                'status' => 'success',
                'message' => 'Factura creada correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al crear la factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMes($mes)
    {
        setlocale(LC_TIME, 'es_ES.UTF-8');

        $meses = [
            '01' => 'ENERO',
            '02' => 'FEBRERO',
            '03' => 'MARZO',
            '04' => 'ABRIL',
            '05' => 'MAYO',
            '06' => 'JUNIO',
            '07' => 'JULIO',
            '08' => 'AGOSTO',
            '09' => 'SEPTIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE',
        ];

        return $meses[$mes];
    }

    public function notificationPdtRenta()
    {
        $fechaDeclaracion = new FechaDeclaracionModel();
        $cont = new ContribuyenteModel();
        $pdt = new PdtRentaModel();

        $array = [];

        $vencimientos = $fechaDeclaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, DATE_SUB(fd.fecha_exacta, INTERVAL 2 DAY) AS nueva_fecha, m.mes_descripcion, a.anio_descripcion FROM fecha_declaracion fd INNER JOIN mes m ON m.id_mes = fd.id_mes INNER JOIN anio a ON a.id_anio = fd.id_anio where fd.id_tributo = 2 and fd.fecha_exacta BETWEEN '2025-07-01' and CURDATE() + INTERVAL 2 DAY")->getResultArray();

        foreach ($vencimientos as $key => $value) {
            $id_anio = $value['id_anio'];
            $id_mes = $value['id_mes'];
            $id_numero = $value['id_numero'];

            $digito = $id_numero - 1;

            $contribuyentes = $cont->select('id, razon_social, ruc, fechaContrato, IF(MONTH(fechaContrato) = MONTH(CURDATE()) AND YEAR(fechaContrato) = YEAR(CURDATE()), "actual", "antiguo") AS tipo_contrato')->where('estado', 1)->where('RIGHT(ruc, 1)', $digito)->where('tipoServicio', 'CONTABLE')->findAll();

            foreach ($contribuyentes as $keys => $values) {
                $ruc = $values['ruc'];

                $pdtRenta = $pdt->query("SELECT id_pdt_renta FROM pdt_renta where ruc_empresa = '$ruc' and periodo = $id_mes and anio = $id_anio and estado = 1")->getResultArray();

                if (!$pdtRenta) {
                    $array[] = [
                        'contribuyente_id' => $values['id'],
                        'ruc' => $ruc,
                        'razon_social' => $values['razon_social'],
                        'anio' => $value['anio_descripcion'],
                        'mes' => $value['mes_descripcion'],
                        'numero' => $id_numero - 1,
                        'fecha_exacta' => $value['fecha_exacta'],
                        'fechaContrato' => $values['fechaContrato'],
                        'tipo_contrato' => $values['tipo_contrato'],
                        'id_anio' => $id_anio,
                        'id_mes' => $id_mes
                    ];
                }
            }
        }

        return $this->respond($array);
    }

    public function excluirPeriodoPdtRenta()
    {
        $pdt = new PdtRentaModel();

        $datos = $this->request->getJSON();

        $id_anio = $datos->id_anio;
        $id_mes = $datos->id_mes;
        $ruc = $datos->ruc;

        $datos = [
            'ruc_empresa' => $ruc,
            'periodo' => $id_mes,
            'anio' => $id_anio,
            'estado' => 1,
            'user_id' => session()->id,
            'excluido' => 'SI'
        ];

        $pdt->insert($datos);

        return $this->respond([
            'status' => 'success',
            'message' => 'Periodo excluido correctamente'
        ]);
    }

    public function getMontosPdtRenta()
    {
        $pdtRenta = new PdtRentaModel();

        $pdts = $pdtRenta->query("SELECT pr.id_pdt_renta, pr.ruc_empresa, pr.periodo, pr.anio, pr.total_compras, pr.total_ventas, ap.id_archivos_pdt, ap.nombre_pdt FROM pdt_renta pr INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.estado = 1 AND ap.estado = 1 AND pr.anio = 11 AND pr.total_ventas = 0 order by pr.periodo asc")->getResultArray();

        $array = [];

        foreach ($pdts as $key => $value) {
            $rutaPdt = FCPATH . 'archivos/pdt/' . $value['nombre_pdt'];

            if (file_exists($rutaPdt)) {
                $datos = $this->apiLoadPdtFile($rutaPdt);

                if ($datos['status'] === 'success') {
                    $compras = $datos['igv_compras'];
                    $ventas = $datos['igv_ventas'];

                    $totalVentas = $ventas['100'] + $ventas['154'] - $ventas['102'] + $ventas['160'] - $ventas['162'] + $ventas['106'] + $ventas['127'] + $ventas['105'] + $ventas['109'] + $ventas['112'];

                    $totalCompras = $compras['107'] + $compras['156'] + $compras['110'] + $compras['113'] + $compras['114'] + $compras['116'] + $compras['119'] + $compras['120'] + $compras['122'];

                    $data_update = array(
                        "total_ventas" => $totalVentas,
                        "total_compras" => $totalCompras
                    );

                    $pdtRenta->update($value['id_pdt_renta'], $data_update);

                    $array[] = [
                        'ruc' => $value['ruc_empresa'],
                        'actualizado' => 'SI'
                    ];
                } else {
                    $array[] = [
                        'ruc' => $value['ruc_empresa'],
                        'actualizado' => 'NO'
                    ];
                }
            }
        }

        return $this->respond($array);
    }

    public function apiLoadPdtFile($rutaFile)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv("API_LOAD_PDT_FILE"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('archivo' => new \CURLFILE($rutaFile)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: multipart/form-data'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        //
    }
}
