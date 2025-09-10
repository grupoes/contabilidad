<?php

namespace App\Controllers;

use App\Models\HonorariosModel;
use App\Models\FacturasHonorariosModel;

class Facturas extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('facturas/index', compact('menu'));
    }

    public function listarFacturasPeriodo()
    {
        $honorarios = new HonorariosModel();

        $facturas = $honorarios->query("SELECT h.id, h.descripcion, COUNT(fh.honorario_id) as total_facturas FROM honorarios h LEFT JOIN facturas_honorarios fh ON fh.honorario_id = h.id WHERE h.estado = 1 GROUP BY h.id, h.descripcion ORDER BY h.id DESC ")->getResult();

        return $this->response->setJSON($facturas);
    }

    public function facturasLista($id)
    {
        $facturas = new FacturasHonorariosModel();

        $facturas = $facturas->query("SELECT c.ruc, c.razon_social, c.tipoServicio, c.tipoPago, fh.serie_comprobante, fh.numero_comprobante, fh.url_absoluta_a4, fh.url_absoluta_ticket, fh.monto FROM facturas_honorarios fh INNER JOIN contribuyentes c ON c.id = fh.contribuyente_id WHERE fh.honorario_id = $id")->getResult();

        return $this->response->setJSON($facturas);
    }

    public function getIdFactura($id)
    {
        $facturas = new FacturasHonorariosModel();

        $consulta = $facturas->query("SELECT * FROM facturas_honorarios as fh INNER JOIN contribuyentes as c ON c.id = fh.contribuyente_id WHERE fh.id = $id")->getRowArray();

        $tipoCliente = $consulta['tipoServicio'];
        $serie_comprobante = $consulta['serie_comprobante'];
        $numero_comprobante = $consulta['numero_comprobante'];

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
            "fecha_comprobante"                         => date('Y-m-d'),  //fecha_comprobante
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
            "precio_venta"                              => $consulta['monto'],
            "cantidad"                                  => 1,  //"cantidad":"1"
        );

        $data["detalle"] = $detalle;

        return $this->response->setJSON($data);

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

        return $this->response->setJSON($respuesta);
    }
}
