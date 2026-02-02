<?php

namespace App\Controllers\Api;

use App\Models\AnioModel;
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
use App\Models\FeriadoModel;
use App\Models\MesModel;
use App\Models\PdtRentaModel;
use App\Models\PdtPlameModel;
use App\Models\TipoCambioModel;
use App\Models\PagoServidorModel;
use App\Models\ServidorModel;
use App\Models\PdtAnualModel;
use App\Models\R08PlameModel;
use App\Models\TipoCambioFacturadorModel;
use App\Models\TrabajadoresContriModel;
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

    public function sendNotaVenta()
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
                "tipo_documento"                => "77",  //{"01": FACTURA, "03": BOLETA}
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

            $ruta = getenv("API_GENERAR_NOTA_VENTA");
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

        $pdts = $pdtRenta->query("SELECT pr.id_pdt_renta, pr.ruc_empresa, pr.periodo, pr.anio, pr.total_compras, pr.total_ventas, ap.id_archivos_pdt, ap.nombre_pdt FROM pdt_renta pr INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.estado = 1 AND ap.estado = 1 AND pr.anio = 11 and pr.periodo between 11 and 11 /*AND pr.total_ventas = 0*/ order by pr.periodo asc")->getResultArray();

        $array = [];

        foreach ($pdts as $key => $value) {
            $rutaPdt = FCPATH . 'archivos/pdt/' . $value['nombre_pdt'];

            if (file_exists($rutaPdt)) {
                $datos = $this->apiLoadPdtFile($rutaPdt);

                if ($datos['status'] === 'success') {
                    //$compras = $datos['igv_compras'];
                    $ventas = $datos['igv_ventas'];

                    $totalVentas = $ventas['100'] + $ventas['154'] - $ventas['102'] + $ventas['160'] - $ventas['162'] + $ventas['106'] + $ventas['127'] + $ventas['105'] + $ventas['109'] + $ventas['112'];

                    //$totalCompras = $compras['107'] + $compras['156'] + $compras['110'] + $compras['113'] + $compras['114'] + $compras['116'] + $compras['119'] + $compras['120'] + $compras['122'];

                    $descuentos = $ventas['102'] + $ventas['162'];

                    if ($ventas['100'] >= $descuentos) {
                        $venta_gravada = $ventas['100'] - $descuentos;
                        $venta_no_gravada = $ventas['154'] + $ventas['160'] + $ventas['106'] + $ventas['127'] + $ventas['105'] + $ventas['109'] + $ventas['112'];
                    } else {
                        $venta_gravada = $ventas['100'];
                        $venta_no_gravada = $totalVentas - $venta_gravada;
                    }

                    $data_update = array(
                        "compras_gravadas" => $datos['compra_gravada'],
                        "compras_no_gravadas" => $datos['compra_no_gravada'],
                        "ventas_gravadas" => $venta_gravada,
                        "ventas_no_gravadas" => $venta_no_gravada,
                        "renta_pdt" => $datos['renta_pdt']
                    );

                    $pdtRenta->update($value['id_pdt_renta'], $data_update);

                    $array[] = [
                        'ruc' => $value['ruc_empresa'],
                        'actualizado' => 'SI',
                        'periodo' => $value['periodo'],
                        'anio' => $value['anio'],
                        "ventas_gravadas" => $venta_gravada,
                        "ventas_no_gravadas" => $venta_no_gravada,
                        "descuentos" => $descuentos
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

    public function excluirPeriodoPdtPlame()
    {
        $pdt = new PdtPlameModel();

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

    public function getCambios()
    {
        $cambio = new TipoCambioModel();

        try {
            $fecha = date('Y-m-d');

            $tipo = $this->apiTipoCambio($fecha);

            $datos = [
                'compra' => $tipo->compra,
                'venta' => $tipo->venta,
                'origen' => $tipo->origen,
                'moneda' => $tipo->moneda,
                'fecha' => $tipo->fecha
            ];

            $cambio->insert($datos);

            return $this->respond([
                'status' => 'success',
                'message' => 'Agregado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCambiosFacturador()
    {
        $cambio = new TipoCambioFacturadorModel();

        try {
            $fecha = date('Y-m-d');

            $tipo = $this->apiTipoCambio($fecha);

            $datos = [
                'compra' => $tipo->compra,
                'venta' => $tipo->venta,
                'origen' => $tipo->origen,
                'moneda' => $tipo->moneda,
                'fecha' => $tipo->fecha
            ];

            $cambio->insert($datos);

            return $this->respond([
                'status' => 'success',
                'message' => 'Agregado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    function apiTipoCambio($fecha)
    {
        $token = 'apis-token-1.aTSI1U7KEuT-6bbbCguH-4Y8TI6KS73N';

        // Iniciar llamada a API
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.apis.net.pe/v1/tipo-cambio-sunat?fecha=' . $fecha,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Referer: https://apis.net.pe/tipo-de-cambio-sunat-api',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // Datos listos para usar
        $tipoCambioSunat = json_decode($response);
        return $tipoCambioSunat;
    }

    public function getConsultaTipoCambio($fecha)
    {
        $tipoCambio = new TipoCambioModel();

        $tipo_cambio = $tipoCambio->select('compra, venta, fecha')->where('fecha', $fecha)->first();

        if ($tipo_cambio) {
            return $this->respond([
                'status' => 'success',
                'data' => $tipo_cambio
            ]);
        } else {
            return $this->respond([
                'status' => 'error',
                'message' => 'No se encontro el tipo de cambio'
            ]);
        }
    }

    public function savePagoServidor()
    {
        $contribuyente = new ContribuyenteModel();
        $pagoServidor = new PagoServidorModel();
        $servidor = new ServidorModel();

        $fecha = date('Y-m-d');

        $contribuyentes = $contribuyente->query("SELECT DISTINCT c.id, c.ruc, c.razon_social, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes c INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id INNER JOIN sistemas s ON sc.system_id = s.id WHERE s.`status` = 1 and c.estado = 1 and sc.system_id != 3 and c.tipoServicio = 'CONTABLE' and c.tipoSuscripcion = 'NO GRATUITO' order by c.id desc;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $pagos = $pagoServidor->where('contribuyente_id', $value['id'])->where('estado', 'pendiente')->orderBy('id', 'desc')->findAll();

            if ($pagos) {
                $fecha_fin = $pagos[0]['fecha_fin'];
                $fecha_inicio = $pagos[0]['fecha_inicio'];
                $idpago = $pagos[0]['id'];

                $fecha_ = new \DateTime($fecha_fin);
                $fecha_->modify('-15 days');
                $fecha_noti = $fecha_->format('Y-m-d');

                if ($fecha_noti == $fecha) {

                    $monto_server = $servidor->where('contribuyente_id', $value['id'])->first();

                    $monto_server = $monto_server['monto'];

                    $new_fecha_inicio = $this->sumFechaAnio($fecha_inicio);

                    $new_fecha_fin = $this->sumFechaAnioServidor($new_fecha_inicio);

                    $data_pago = array(
                        "contribuyente_id" => $value['id'],
                        "fecha_pago" => null,
                        "fecha_proceso" => null,
                        "monto_total" => $monto_server,
                        "fecha_inicio" => $new_fecha_inicio,
                        "fecha_fin" => $new_fecha_fin,
                        "monto_pendiente" => $monto_server,
                        "monto_pagado" => 0,
                        "usuario_id_cobra" => 1,
                        "estado" => 'pendiente',
                    );

                    $pagoServidor->insert($data_pago);

                    $fi = new DateTime($fecha_inicio);
                    $dateInit = $fi->format('d-m-Y');

                    $ff = new DateTime($fecha_fin);
                    $dateEnd = $ff->format('d-m-Y');

                    $descripcion = "SERVICIO POR EL SERVIDOR DEL SISTEMA DE FACTURACION DEL PERIODO: " . $dateInit . " AL " . $dateEnd;

                    $contribuyentes[$key]['pagos'] = "ok";
                    $contribuyentes[$key]['fecha_inicio'] = $new_fecha_inicio;
                    $contribuyentes[$key]['fecha_fin'] = $new_fecha_fin;
                    $contribuyentes[$key]['monto'] = $monto_server;
                    $contribuyentes[$key]['descripcion'] = $descripcion;
                    $contribuyentes[$key]['contribuyente_id'] = $value['id'];
                    $contribuyentes[$key]['ruc'] = $value['ruc'];
                    $contribuyentes[$key]['razon_social'] = $value['razon_social'];
                    $contribuyentes[$key]['idpago'] = $idpago;
                }
            }
        }

        return $this->respond($contribuyentes);
    }

    public function savePagoServidorAhora()
    {
        $contribuyente = new ContribuyenteModel();
        $pagoServidor = new PagoServidorModel();
        $servidor = new ServidorModel();

        try {
            $fecha = date('Y-m-d');

            $contribuyentes = $contribuyente->query("SELECT DISTINCT c.id, c.ruc, c.razon_social, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes c INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id INNER JOIN sistemas s ON sc.system_id = s.id WHERE s.`status` = 1 and c.estado = 1 and sc.system_id != 3 and c.tipoServicio = 'CONTABLE' and c.tipoSuscripcion = 'NO GRATUITO' order by c.id desc;")->getResultArray();

            $datos = array();

            foreach ($contribuyentes as $key => $value) {
                $pagos = $pagoServidor->where('contribuyente_id', $value['id'])->where('estado', 'pendiente')->orderBy('id', 'desc')->findAll();

                $pagos = $pagoServidor->query("SELECT * FROM pago_servidor WHERE contribuyente_id = " . $value['id'] . " AND (estado = 'pendiente' OR estado_nota = 'no') ORDER BY id DESC")->getResultArray();

                if ($pagos) {
                    $fecha_fin = $pagos[0]['fecha_fin'];
                    $fecha_inicio = $pagos[0]['fecha_inicio'];
                    $estado_nota = $pagos[0]['estado_nota'];
                    $idpago = $pagos[0]['id'];

                    $fecha_ = new \DateTime($fecha_fin);
                    $fecha_->modify('-15 days');
                    $fecha_noti = $fecha_->format('Y-m-d');

                    if ($estado_nota == 'no') {
                        if ($fecha_noti <= $fecha || $fecha_fin >= $fecha) {

                            $monto_server = $servidor->where('contribuyente_id', $value['id'])->first();

                            $monto_server = $monto_server['monto'];

                            $new_fecha_inicio = $this->sumFechaAnio($fecha_inicio);

                            $new_fecha_fin = $this->sumFechaAnioServidor($new_fecha_inicio);

                            $data_pago = array(
                                "contribuyente_id" => $value['id'],
                                "fecha_pago" => null,
                                "fecha_proceso" => null,
                                "monto_total" => $monto_server,
                                "fecha_inicio" => $new_fecha_inicio,
                                "fecha_fin" => $new_fecha_fin,
                                "monto_pendiente" => $monto_server,
                                "monto_pagado" => 0,
                                "usuario_id_cobra" => 1,
                                "estado" => 'pendiente',
                            );

                            //$pagoServidor->insert($data_pago);

                            $fi = new DateTime($fecha_inicio);
                            $dateInit = $fi->format('d-m-Y');

                            $ff = new DateTime($fecha_fin);
                            $dateEnd = $ff->format('d-m-Y');

                            $descripcion = "SERVICIO POR EL SERVIDOR DEL SISTEMA DE FACTURACION DEL PERIODO: " . $dateInit . " AL " . $dateEnd;

                            /*$contribuyentes[$key]['pagos'] = "ok";
                        $contribuyentes[$key]['fecha_inicio'] = $new_fecha_inicio;
                        $contribuyentes[$key]['fecha_fin'] = $new_fecha_fin;
                        $contribuyentes[$key]['monto'] = $monto_server;
                        $contribuyentes[$key]['descripcion'] = $descripcion;*/

                            $dataPago = array(
                                "contribuyente_id" => $value['id'],
                                "ruc" => $value['ruc'],
                                "razon_social" => $value['razon_social'],
                                "descripcion" => $descripcion,
                                "monto" => $monto_server,
                                "fecha_inicio" => $fecha_inicio,
                                "fecha_fin" => $fecha_fin,
                                "pagos" => "ok",
                                "idpago" => $idpago
                            );

                            array_push($datos, $dataPago);
                        }
                    }
                }
            }

            return $this->respond([
                'status' => 'success',
                'fielCount' => count($datos),
                'data' => $datos
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updatePagoServidorNotaEnviada()
    {
        $pagoServidor = new PagoServidorModel();

        try {
            $datos = $this->request->getJSON();
            $id = $datos->id;
            $numero_nota = $datos->numero_nota;
            $url_pdf = $datos->url_pdf;

            $data_pago = array(
                "numero_notas" => $numero_nota,
                "url_pdf_nota" => $url_pdf,
            );

            $pagoServidor->update($id, $data_pago);

            return $this->respond([
                'status' => 'success',
                'message' => 'Actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    function sumFechaAnioServidor($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('+1 year');

        $fecha_anio = $fecha->format('Y-m-d');

        $fecha_restar_un_dia = new \DateTime($fecha_anio);
        $fecha_restar_un_dia->modify('-1 day');

        $fecha_anio = $fecha_restar_un_dia->format('Y-m-d');

        return $fecha_anio;
    }

    function sumFechaAnio($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('+1 year');

        $fecha_anio = $fecha->format('Y-m-d');

        return $fecha_anio;
    }

    public function getFacturasHonorarios($id)
    {
        $facturas = new FacturasHonorariosModel();

        //$consulta = $facturas->query("SELECT * FROM facturas_honorarios as fh INNER JOIN contribuyentes as c ON c.id = fh.contribuyente_id WHERE fh.honorario_id = $id")->getResultArray();

        $consulta = $facturas->query("SELECT * FROM facturas_honorarios WHERE numero_comprobante BETWEEN '5307' and '5356'")->getResultArray();

        return $this->respond($consulta);
    }

    public function sendApiEnviarNotaCredito()
    {
        try {
            $datos = $this->request->getJSON();

            $serie_comprobante = $datos->serie_comprobante ?? null;
            $numero_comprobante = $datos->numero_comprobante ?? null;
            $monto = $datos->monto ?? null;

            $db = \Config\Database::connect('facturador');

            $query = $db->query("SELECT * FROM detalle_doc WHERE id_contribuyente = 42 and serie_comprobante = '$serie_comprobante' and numero_comprobante = '$numero_comprobante'")->getRowArray();

            $descripcion = $query['descripcion'];

            $data["contribuyente"] = array(
                "token_contribuyente"                         => getenv("API_KEY_GENERAR_FACTURA"), //Token del contribuyente
                "id_usuario_vendedor"                         => getenv("ID_USUARIO_VENDEDOR"), //Debes ingresar el ID de uno de tus vendedores (opcional)
                "tipo_proceso"                                 => getenv("TIPO_ENVIO_SUNAT"), //Funcional en una siguiente versión. El ambiente al que se enviará, puede ser: {prueba, produccion}
                "tipo_envio"                                 => "inmediato" //funcional en una siguiente versión. Aquí puedes definir si se enviará de inmediato a sunat
            );

            $data["cabecera_comprobante"] = array(
                "tipo_documento"                             => "07",  //{"07": NOTA DE CRÉDITO}
                "moneda"                                     => "PEN",  //{"USD", "PEN"}
                "idsucursal"                                 => getenv("ID_SUCURSAL"),  //{ID DE SUCURSAL}
                "id_condicionpago"                             => "",  //condicionpago_comprobante
                "fecha_comprobante"                         => date('d/m/Y'),  //fecha_comprobante
                "nro_placa"                                 => "",  //nro_placa_vehiculo
                "nro_orden"                                 => "",  //nro_orden
                "guia_remision"                             => "",  //guia_remision_manual
                "descuento_monto"                             => 0,  // (máximo 2 decimales) (monto total del descuento)
                "descuento_porcentaje"                         => 0,  // (máximo 2 decimales) (porcentaje total del descuento)
                "observacion"                                 => "",  //observacion_documento, 

                "doc_modifica_id_tipodoc_electronico"         => "01",
                "doc_modifica_serie_comprobante"             => $serie_comprobante,
                "doc_modifica_numero_comprobante"             => $numero_comprobante,
                "id_motivo_nota_credito"                     => "01", //MOTIVO DE LA NOTA DE CRÉDITO, SEGÚN TABLA SUNAT

            );

            $detalle[] = array(
                "idproducto"                                => 91282,
                "codigo"                                    => getenv("CODIGO_PRODUCTO"),
                "afecto_icbper"                             => "no",  //"afecto_icbper":"no",
                "id_tipoafectacionigv"                      => 20,  //"id_tipoafectacionigv":"10",
                "descripcion"                               => $descripcion,  //"descripcion":"Zapatos",
                "idunidadmedida"                            => 'NIU',  //{NIU para unidades, ZZ para servicio}
                "precio_venta"                              => $monto,
                "cantidad"                                  => 1,  //"cantidad":"1"
            );

            $data["detalle"] = $detalle;

            $ruta = "https://esfacturador.com/facturacionv7/api/procesar_notacredito";
            $data_json = json_encode($data);
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
            if (curl_error($ch)) {
                $error_msg = curl_error($ch);
            }
            curl_close($ch);
            if (isset($error_msg)) {
                $resp["respuesta"] = "error";
                $resp["titulo"] = "Error";
                $resp["data"] = "";
                $resp["encontrado"] = false;
                $resp["mensaje"] = "Error en Api de Búsqueda";
                $resp["errores_curl"] = $error_msg;
                echo json_encode($resp);
                exit();
            }

            return $this->respond($respuesta);
        } catch (\Exception $e) {
            return $this->respond([
                "respuesta" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function renderPdtAnualesFacturas()
    {
        $pdt = new PdtAnualModel();

        try {
            $datos = $pdt->where('estado_envio', 'Pendiente')->where('cargo', 1)->findAll();

            $array = array(
                "status" => "success",
                "data" => $datos
            );

            return $this->respond($array);
        } catch (\Exception $e) {
            return $this->respond([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function updatePagoAnual()
    {
        $pdt = new PdtAnualModel();

        try {

            $datos = $this->request->getJSON();

            $estado = "Generado";
            $id = $datos->id;
            $link_pdf = $datos->link_pdf;
            $link_ticket = $datos->link_ticket;

            $datos = [
                "estado_envio" => $estado,
                "link_pdf" => $link_pdf,
                "link_ticket" => $link_ticket
            ];

            $pdt->update($id, $datos);

            return $this->respond([
                "status" => "success",
                "message" => "Actualizado correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function actualizarMontosVentasComprasEstado()
    {
        $pdt = new PdtRentaModel();

        $datos = $pdt->where('anio', 11)->where('estado', 1)->findAll();

        $data = [];

        foreach ($datos as $key => $value) {
            if ($value['total_compras'] == 0.00 || $value['total_ventas'] == 0.00) {
                $update = array('estado_datos' => 0);
                $pdt->update($value['id_pdt_renta'], $update);

                $add = array(
                    "ruc" => $value['ruc_empresa'],
                    "total_ventas" => $value['total_ventas'],
                    "total_compras" => $value['total_compras']
                );

                array_push($data, $add);
            }
        }

        return $this->respond($data);
    }

    public function notificacionAfp($fecha)
    {
        $feriados = new FeriadoModel();

        $allFeriados = array_column($feriados->select('fecha')->findAll(), 'fecha');

        $contador = 0;
        $fecha = new DateTime($fecha);

        while ($contador < 5) {
            $diaSemana = $fecha->format('N'); // 1 = Lunes ... 7 = Domingo
            $fechaStr = $fecha->format('Y-m-d');

            if ($diaSemana >= 1 && $diaSemana <= 5 && !in_array($fechaStr, $allFeriados)) {
                $contador++;
                if ($contador === 5) {
                    $data = [
                        "status" => "ok",
                        "fecha" => $fechaStr
                    ];
                    return $data;
                }
            }

            $fecha->modify('+1 day');
        }

        $data = ["status" => "error", "mensaje" => "ocurrio algo inesperado"];

        return $data;
    }

    public function insert_fecha_declaracion_afp()
    {
        $fechas = [
            "2025-12-01",
            "2026-01-01",
            "2026-02-01",
            "2026-03-01",
            "2026-04-01",
            "2026-05-01",
            "2026-06-01",
            "2026-07-01",
            "2026-08-01",
            "2026-09-01",
            "2026-10-01",
            "2026-11-01",
            "2026-12-01",
            "2027-01-01",
            "2027-02-01",
            "2027-03-01",
            "2027-04-01",
            "2027-05-01",
            "2027-06-01",
            "2027-07-01",
            "2027-08-01",
            "2027-09-01",
            "2027-10-01",
            "2027-11-01",
            "2027-12-01",
            "2028-01-01",
            "2028-02-01",
            "2028-03-01",
            "2028-04-01",
            "2028-05-01",
            "2028-06-01",
            "2028-07-01",
            "2028-08-01",
            "2028-09-01",
            "2028-10-01",
            "2028-11-01",
            "2028-12-01",
            "2029-01-01"
        ];

        $month = new MesModel();
        $year = new AnioModel();
        $feriados = new FeriadoModel();
        $fecha_declaracion = new FechaDeclaracionModel();

        try {
            $allFeriados = array_column($feriados->select('fecha')->findAll(), 'fecha');

            $data_fechas = [];

            for ($i = 0; $i < count($fechas); $i++) {
                $fecha = $this->notificacionAfp($fechas[$i]);

                if ($fecha['status'] === 'ok') {

                    $fecha_exacta = $fecha['fecha'];

                    $d = new DateTime($fecha_exacta);
                    $d->modify('-1 month');

                    $mes_correspondiente = $d->format('m');
                    $anio_correspondiente = $d->format('Y');

                    $data_mes = $month->where('mes_fecha', $mes_correspondiente)->first();
                    $data_anio = $year->where('anio_descripcion', $anio_correspondiente)->first();

                    $dia_notificacion = $this->restarDiaHabil($fecha_exacta, $allFeriados);

                    for ($j = 1; $j < 11; $j++) {
                        $data_insert = [
                            "id_anio" => $data_anio['id_anio'],
                            "id_mes" => $data_mes['id_mes'],
                            "id_numero" => $j,
                            "fecha_exacta" => $fecha_exacta,
                            "fecha_declaracion_estado" => 1,
                            "id_tributo" => 22,
                            "dia_exacto" => date('d', strtotime($fecha_exacta)),
                            "fecha_notificar" => $dia_notificacion
                        ];

                        //array_push($data_fechas, $data_insert);
                        $fecha_declaracion->insert($data_insert);
                    }
                }
            }

            return $this->respond([
                "status" => "ok",
                "message" => "Se insertaron correctamente los datos"
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    function restarDiaHabil($fecha, $feriados = [])
    {
        // Convertimos la fecha a timestamp
        $timestamp = strtotime($fecha);

        // Restar hasta encontrar día hábil
        do {
            $timestamp = strtotime('-1 day', $timestamp);
            $diaSemana = date('N', $timestamp); // 1 (Lunes) - 7 (Domingo)
            $fechaCheck = date('Y-m-d', $timestamp);
        } while ($diaSemana >= 6 || in_array($fechaCheck, $feriados));
        // >=6 significa sábado(6) o domingo(7)

        return date('Y-m-d', $timestamp);
    }

    public function readBoletasPago()
    {
        $r08 = new R08PlameModel();

        $consulta = $r08->query("SELECT * FROM r08_plame inner join pdt_plame on r08_plame.plameId = pdt_plame.id_pdt_plame where pdt_plame.estado = 1 and pdt_plame.anio >= 12 and r08_plame.read_boleta = 0")->getResultArray();

        return $this->respond($consulta);
    }

    public function saveDataBoletasPago()
    {
        $r08 = new R08PlameModel();
        $job = new TrabajadoresContriModel();

        try {
            $datos = $this->request->getJSON();

            $id = $datos->id;
            $fecha_ingreso = $datos->fecha_ingreso;
            $numero_documento = $datos->numero_documento;
            $tipo_documento = $datos->tipo_documento;
            $nombres = $datos->nombres;
            $situacion = $datos->situacion;
            $ruc = $datos->ruc;

            //formatear fecha ingreso

            $fechaFormateada = DateTime::createFromFormat('d/m/Y', $fecha_ingreso)
                ->format('Y-m-d');

            $fecha_ingreso = $fechaFormateada;

            $consulta_job = $job->where('numero_documento', $numero_documento)->first();

            if (!$consulta_job) {
                $data_job = [
                    'numero_documento' => $numero_documento,
                    'tipo_documento' => $tipo_documento,
                    'nombres' => $nombres,
                    'estado' => 1,
                    'password' => $numero_documento
                ];

                $job->insert($data_job);
            }

            $data = [
                'fecha_ingreso' => $fecha_ingreso,
                'numero_documento' => $numero_documento,
                'tipo_documento' => $tipo_documento,
                'nombres' => $nombres,
                'situacion' => $situacion,
                'ruc' => $ruc,
                'read_boleta' => 1
            ];

            $r08->update($id, $data);

            return $this->respond([
                'status' => 'success',
                'message' => 'Boleta de pago guardada correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al guardar la boleta de pago: ' . $e->getMessage()
            ], 500);
        }
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
