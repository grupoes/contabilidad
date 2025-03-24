<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Models\FechaDeclaracionModel;
use App\Models\ContribuyenteModel;

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
        $date = date('Y-m-d');

        $consulta = $fecha->query("SELECT id_mes, id_numero, MIN(fecha_notificar) AS fecha_notificar, GROUP_CONCAT(tipo ORDER BY tipo SEPARATOR ', ') AS tipo, id_tributo, MIN(fecha_exacta) AS fecha_exacta FROM ( SELECT id_mes, id_numero, fecha_notificar, 'notificar' AS tipo, id_tributo, fecha_exacta FROM fecha_declaracion WHERE fecha_notificar = 'date' UNION SELECT id_mes, id_numero, fecha_exacta, 'ultimo_dia' AS tipo, id_tributo, fecha_exacta FROM fecha_declaracion WHERE fecha_exacta = '$date' ) AS subquery GROUP BY id_numero;")->getResult();

        foreach ($consulta as $key => $value) {
            $digito = $value->id_numero - 1;
            $emp = $contrib->query("SELECT * FROM contribuyentes WHERE estado = 1 and RIGHT(ruc, 1) = '$digito'")->getResult();
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
