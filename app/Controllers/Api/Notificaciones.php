<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\FechaDeclaracionModel;
use App\Models\ContribuyenteModel;
use App\Models\ContactosContribuyenteModel;
use App\Models\EnviosModel;

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
            $emp = $contrib->query("SELECT c.id, c.ruc, c.razon_social, c.nombre_comercial, c.direccion_fiscal, nw.link FROM contribuyentes as c inner join numeros_whatsapp as nw ON nw.id = c.numeroWhatsappId WHERE c.tipoServicio = 'CONTABLE' AND c.estado = 1 and RIGHT(c.ruc, 1) = '$digito'")->getResult();

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

        $envio->update($id, ['fecha_envio' => $fecha_envio, 'estado' => 'enviado']);

        return $this->respond(['message' => "Mensaje actualizado correctamente"]);
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
