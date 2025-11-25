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
use App\Models\PdtRentaModel;
use App\Models\PdtPlameModel;
use App\Models\TipoCambioModel;
use App\Models\SistemaModel;
use App\Models\PagoServidorModel;
use App\Models\ServidorModel;
use App\Models\PdtAnualModel;

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
            $anio_des = (int) $value['anio_descripcion'];

            $digito = $id_numero - 1;

            $contribuyentes = $cont->select('id, razon_social, ruc, fechaContrato, IF(MONTH(fechaContrato) = MONTH(CURDATE()) AND YEAR(fechaContrato) <= YEAR(CURDATE()), "actual", "antiguo") AS tipo_contrato')->where('estado', 1)->where('RIGHT(ruc, 1)', $digito)->where('tipoServicio', 'CONTABLE')->findAll();

            foreach ($contribuyentes as $keys => $values) {
                $ruc = $values['ruc'];

                $mes = (int)date("m", strtotime($values['fechaContrato']));
                $anio = (int)date("Y", strtotime($values['fechaContrato']));

                if ($id_mes >= $mes && $anio_des >= $anio) {
                    $pdtRenta = $pdt->query("SELECT id_pdt_renta FROM pdt_renta where ruc_empresa = '$ruc' and periodo = $id_mes and anio = $id_anio and estado = 1")->getResultArray();

                    if (!$pdtRenta) {
                        $renta = $pdt->query("SELECT id_pdt_renta FROM pdt_renta where ruc_empresa = '$ruc'")->getResultArray();

                        $registro = 0;

                        if ($renta) {
                            $registro = 1;
                        }

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
                            'id_mes' => $id_mes,
                            'registro' => $registro
                        ];
                    }
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

        $pdts = $pdtRenta->query("SELECT pr.id_pdt_renta, pr.ruc_empresa, pr.periodo, pr.anio, pr.total_compras, pr.total_ventas, ap.id_archivos_pdt, ap.nombre_pdt FROM pdt_renta pr INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.estado = 1 AND ap.estado = 1 AND pr.anio = 11 and pr.periodo between 1 and 3 /*AND pr.total_ventas = 0*/ order by pr.periodo asc")->getResultArray();

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
                        $venta_no_gravada = $totalVentas - $venta_gravada + $descuentos;
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

    public function notificationPdtPlame()
    {
        $fechaDeclaracion = new FechaDeclaracionModel();
        $cont = new ContribuyenteModel();
        $pdt = new PdtPlameModel();

        $array = [];

        $vencimientos = $fechaDeclaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, DATE_SUB(fd.fecha_exacta, INTERVAL 2 DAY) AS nueva_fecha, m.mes_descripcion, a.anio_descripcion FROM fecha_declaracion fd INNER JOIN mes m ON m.id_mes = fd.id_mes INNER JOIN anio a ON a.id_anio = fd.id_anio where fd.id_tributo = 2 and fd.fecha_exacta BETWEEN '2025-07-01' and CURDATE() + INTERVAL 2 DAY")->getResultArray();

        foreach ($vencimientos as $key => $value) {
            $id_anio = $value['id_anio'];
            $id_mes = $value['id_mes'];
            $id_numero = $value['id_numero'];

            $digito = $id_numero - 1;

            /*$contribuyentes = $cont->select('id, razon_social, ruc, fechaContrato, IF(MONTH(fechaContrato) = MONTH(CURDATE()) AND YEAR(fechaContrato) = YEAR(CURDATE()), "actual", "antiguo") AS tipo_contrato')->where('estado', 1)->where('RIGHT(ruc, 1)', $digito)->where('tipoServicio', 'CONTABLE')->findAll();*/

            $contribuyentes = $cont->query("SELECT c.ruc, MAX(c.id) AS id, MAX(c.razon_social) AS razon_social, MAX(c.fechaContrato) AS fechaContrato, IF(MONTH(MAX(c.fechaContrato)) <= MONTH(CURDATE()) AND YEAR(MAX(c.fechaContrato)) = YEAR(CURDATE()), 'actual', 'antiguo') AS tipo_contrato FROM contribuyentes c INNER JOIN configuracion_notificacion cn ON cn.ruc_empresa_numero = c.ruc INNER JOIN tributo t ON t.id_tributo = cn.id_tributo WHERE c.estado = 1 AND RIGHT(c.ruc, 1) = $digito AND c.tipoServicio = 'CONTABLE' AND t.id_pdt = 2 GROUP BY c.ruc")->getResultArray();

            foreach ($contribuyentes as $keys => $values) {
                $ruc = $values['ruc'];

                $pdtPlame = $pdt->query("SELECT pp.id_pdt_plame, ap.archivo_constancia, pp.excluido FROM pdt_plame pp LEFT JOIN archivos_pdtplame ap ON ap.id_pdtplame = pp.id_pdt_plame where pp.ruc_empresa = '$ruc' and pp.periodo = $id_mes and pp.anio = $id_anio and pp.estado = 1 ORDER BY ap.id_archivos_pdtplame desc")->getRowArray();

                if ($pdtPlame) {
                    if (($pdtPlame['archivo_constancia'] === null || $pdtPlame['archivo_constancia'] === '') && $pdtPlame['excluido'] === 'NO') {
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
                } else {
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
        $sistema = new SistemaModel();
        $pagoServidor = new PagoServidorModel();
        $servidor = new ServidorModel();

        $fecha = date('Y-m-d');

        $contribuyentes = $contribuyente->query("SELECT DISTINCT c.id, c.ruc, c.razon_social, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes c INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id INNER JOIN sistemas s ON sc.system_id = s.id WHERE s.`status` = 1 and sc.system_id != 3 and c.tipoServicio = 'CONTABLE' order by c.id desc;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $pagos = $pagoServidor->where('contribuyente_id', $value['id'])->where('estado', 'pendiente')->orderBy('id', 'desc')->findAll();

            if ($pagos) {
                $fecha_fin = $pagos[0]['fecha_fin'];
                $fecha_inicio = $pagos[0]['fecha_inicio'];

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

                    $descripcion = "SERVICIO POR EL SERVIDOR DEL SISTEMA DE FACTURACION DEL PERIODO: " . $new_fecha_inicio . " AL " . $new_fecha_fin;

                    $contribuyentes[$key]['pagos'] = "ok";
                    $contribuyentes[$key]['fecha_inicio'] = $new_fecha_inicio;
                    $contribuyentes[$key]['fecha_fin'] = $new_fecha_fin;
                    $contribuyentes[$key]['monto'] = $monto_server;
                    $contribuyentes[$key]['descripcion'] = $descripcion;
                } else {
                    $contribuyentes[$key]['pagos'] = "no";
                }
            } else {
                $contribuyentes[$key]['pagos'] = "no";
            }
        }

        return $this->respond($contribuyentes);
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
