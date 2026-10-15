<?php namespace App\Models;

    use CodeIgniter\Model;

    class LibroModel extends Model
    {
        protected $table      = 'libro';
        protected $primaryKey = 'lib_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = [
            'lib_id', 'con_id', 'tli_id', 'tri_id', 'lib_numero', 'lib_sede', 'lib_notaria', 'lib_fecha_legalizacion',
            'lib_kardex', 'lib_stan', 'lib_folio_inicial', 'lib_folio_final', 'lib_total_hojas', 'lib_minimo',
            'lib_observacion', 'lib_estado',
        ];

        protected $useTimestamps = false;

        /**
         * Libros activos con cliente y tipo, más las cifras calculadas
         * (impresas, simuladas, restantes, estado).
         */
        public function listar($libId = null, $conId = null)
        {
            $builder = $this->select('libro.*, tipo_libro.tli_nombre, contribuyentes.razon_social, contribuyentes.ruc, tributo.tri_descripcion AS regimen', false)
                ->join('tipo_libro', 'tipo_libro.tli_id = libro.tli_id')
                ->join('contribuyentes', 'contribuyentes.id = libro.con_id')
                // Régimen del libro; si no se eligió uno, el que tiene configurado el contribuyente
                ->join('tributo', 'tributo.id_tributo = COALESCE(libro.tri_id, (' . $this->sqlRegimen('contribuyentes.ruc') . '))', 'left', false)
                ->where('libro.lib_estado', 1);

            if ($libId) {
                $builder->where('libro.lib_id', $libId);
            }

            if ($conId) {
                $builder->where('libro.con_id', $conId);
            }

            $libros = $builder->orderBy('contribuyentes.razon_social', 'ASC')->orderBy('libro.lib_id', 'ASC')->findAll();

            foreach ($libros as $k => $libro) {
                $libros[$k] = array_merge($libro, $this->calcular($libro));
            }

            return $libros;
        }

        /**
         * Subconsulta con el id del régimen de Renta que el contribuyente tiene marcado: tributos del
         * PDT RENTA (tributo.id_pdt = 1) en configuracion_notificacion (general, especial, amazonia, mype, agrario).
         */
        private function sqlRegimen($colRuc)
        {
            return "SELECT MIN(t.id_tributo)
                FROM configuracion_notificacion cn
                INNER JOIN tributo t ON t.id_tributo = cn.id_tributo AND t.id_pdt = 1
                WHERE cn.ruc_empresa_numero = $colRuc";
        }

        /** Regímenes de Renta disponibles para elegir (PDT RENTA). */
        public function tributosRenta()
        {
            return $this->db->table('tributo')->select('id_tributo, tri_descripcion')->where('id_pdt', 1)->where('tri_estado', 1)->orderBy('id_tributo', 'ASC')->get()->getResultArray();
        }

        /** Mapa id de contribuyente => id del régimen que tiene configurado, para preseleccionarlo en el modal. */
        public function regimenes()
        {
            $filas = $this->db->query('SELECT c.id, (' . $this->sqlRegimen('c.ruc') . ') AS regimen FROM contribuyentes c WHERE c.estado = 1 AND c.tipoServicio = \'CONTABLE\'')->getResultArray();

            return array_column($filas, 'regimen', 'id');
        }

        public function impresiones($libId)
        {
            return (new LibroImpresionModel())->where('lib_id', $libId)->where('lim_estado', 1)->orderBy('lim_folio_desde', 'ASC')->findAll();
        }

        public function simulaciones($libId)
        {
            return (new LibroSimulacionModel())->where('lib_id', $libId)->where('lsi_estado', 1)->orderBy('lsi_periodo', 'DESC')->findAll();
        }

        /**
         * Misma lógica del control original: usadas = impresas + simuladas,
         * restantes = total - usadas, estado según mínimo de alerta.
         */
        public function calcular($libro)
        {
            $libId = $libro['lib_id'];
            $imps = $this->impresiones($libId);
            $sims = $this->simulaciones($libId);

            $impresas = 0;
            $maxFolio = (int) $libro['lib_folio_inicial'] - 1;
            foreach ($imps as $i) {
                $impresas += $i['lim_folio_hasta'] - $i['lim_folio_desde'] + 1;
                $maxFolio = max($maxFolio, (int) $i['lim_folio_hasta']);
            }

            $simuladas = array_sum(array_column($sims, 'lsi_hojas'));
            $total = (int) $libro['lib_total_hojas'];
            $restantes = $total - $impresas - $simuladas;
            $minimo = (int) $libro['lib_minimo'];

            if ($restantes <= 0) {
                $estado = 'Agotado';
                $color = 'danger';
            } elseif ($restantes <= $minimo) {
                $estado = 'Stock bajo';
                $color = 'warning';
            } else {
                $estado = 'OK';
                $color = 'success';
            }

            $proyeccion = '';
            if (count($sims) > 0) {
                $prom = $simuladas / count($sims);
                if ($restantes <= $minimo) {
                    $proyeccion = 'Límite alcanzado en ' . $this->periodoTexto($sims[0]['lsi_periodo']);
                } elseif ($prom > 0) {
                    $proyeccion = 'Al ritmo de ~' . round($prom) . ' hojas/periodo llegas al límite en ~' . (int) ceil(($restantes - $minimo) / $prom) . ' periodos más';
                }
            }

            return [
                'impresas' => $impresas,
                'simuladas' => $simuladas,
                'restantes' => max($restantes, 0),
                'en_blanco' => max($total - $impresas, 0),
                'folio_sig' => $maxFolio + 1,
                'estado' => $estado,
                'color' => $color,
                'porcentaje' => $total > 0 ? min(100, round(($impresas + $simuladas) / $total * 100)) : 0,
                'proyeccion' => $proyeccion,
                'ultima_simulacion' => $sims ? $this->periodoTexto($sims[0]['lsi_periodo']) : '',
            ];
        }

        public function periodoTexto($p)
        {
            if (!$p) {
                return '';
            }

            $nombres = ['Apertura', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre', 'Cierre 13', 'Cierre 14'];
            [$y, $m] = explode('-', $p) + [1 => '00'];

            return ($nombres[(int) $m] ?? $m) . ' ' . $y;
        }
    }

?>
