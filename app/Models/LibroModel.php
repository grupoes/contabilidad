<?php namespace App\Models;

    use CodeIgniter\Model;

    class LibroModel extends Model
    {
        protected $table      = 'libro';
        protected $primaryKey = 'lib_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = [
            'lib_id', 'con_id', 'tli_id', 'tri_id', 'lib_numero', 'lib_sede', 'sede_id', 'lib_notaria', 'not_id', 'lib_fecha_legalizacion',
            'lib_kardex', 'lib_stan', 'stan_id', 'lib_folio_inicial', 'lib_folio_final', 'lib_total_hojas', 'lib_minimo',
            'lib_observacion', 'lib_estado',
            'lib_user_add', 'lib_fecha_add', 'lib_user_edit', 'lib_fecha_edit', 'lib_user_delete', 'lib_fecha_delete',
        ];

        protected $useTimestamps = false;

        /**
         * Libros activos con cliente y tipo, más las cifras calculadas
         * (impresas, simuladas, restantes, estado).
         */
        public function listar($libId = null, $conId = null)
        {
            $builder = $this->select('libro.*, tipo_libro.tli_nombre, contribuyentes.razon_social, contribuyentes.ruc, tributo.tri_descripcion AS regimen, COALESCE(sede.nombre_sede, libro.lib_sede) AS sede_nombre, COALESCE(notaria.not_razon_social, libro.lib_notaria) AS notaria_nombre, COALESCE(stan.stan_numero, libro.lib_stan) AS stan_nombre, '
                    . $this->nombreUsuario('libro', 'lib_user_add', 'usuario_add') . ', '
                    . $this->nombreUsuario('libro', 'lib_user_edit', 'usuario_edit'), false)
                ->join('tipo_libro', 'tipo_libro.tli_id = libro.tli_id')
                ->join('contribuyentes', 'contribuyentes.id = libro.con_id')
                // Sede (tabla sede); los libros sin sede_id muestran el texto antiguo de lib_sede
                ->join('sede', 'sede.id = libro.sede_id', 'left')
                // Notaría elegida; los libros antiguos solo tienen el texto en lib_notaria
                ->join('notaria', 'notaria.not_id = libro.not_id', 'left')
                // Stan elegido; los libros antiguos solo tienen el texto en lib_stan
                ->join('stan', 'stan.stan_id = libro.stan_id', 'left')
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

        /** Subconsulta con el nombre del usuario guardado en una columna de auditoría (usuario.id). */
        private function nombreUsuario($tabla, $col, $alias)
        {
            return "(SELECT TRIM(CONCAT(u.nombres, ' ', u.apellidos)) FROM usuario u WHERE u.id = $tabla.$col) AS $alias";
        }

        /** Sedes activas (tabla sede) para los selects de sede. */
        public function sedes()
        {
            return $this->db->table('sede')->select('id, nombre_sede')->where('estado', 1)->orderBy('nombre_sede', 'ASC')->get()->getResultArray();
        }

        /** Una sede activa por id, o null. */
        public function sedeActiva($id)
        {
            return $this->db->table('sede')->select('id, nombre_sede')->where('estado', 1)->where('id', (int) $id)->get()->getRowArray();
        }

        /** Regímenes de Renta disponibles para elegir (PDT RENTA). */
        public function tributosRenta()
        {
            return $this->db->table('tributo')->select('id_tributo, tri_descripcion')->where('id_pdt', 1)->where('tri_estado', 1)->orderBy('id_tributo', 'ASC')->get()->getResultArray();
        }

        /** Id del régimen de Renta que el contribuyente tiene configurado hoy (null si no tiene). */
        public function regimenDe($conId)
        {
            $fila = $this->db->query('SELECT (' . $this->sqlRegimen('c.ruc') . ') AS regimen FROM contribuyentes c WHERE c.id = ?', [$conId])->getRowArray();

            return $fila['regimen'] ?? null;
        }

        /** Mapa id de contribuyente => id del régimen que tiene configurado, para preseleccionarlo en el modal. */
        public function regimenes()
        {
            $filas = $this->db->query('SELECT c.id, (' . $this->sqlRegimen('c.ruc') . ') AS regimen FROM contribuyentes c WHERE c.estado > 0 AND c.tipoServicio = \'CONTABLE\'')->getResultArray();

            return array_column($filas, 'regimen', 'id');
        }

        public function impresiones($libId)
        {
            return (new LibroImpresionModel())
                ->select('libro_impresion.*, ' . $this->nombreUsuario('libro_impresion', 'lim_user_add', 'usuario_add'), false)
                ->where('lib_id', $libId)->where('lim_estado', 1)->orderBy('lim_folio_desde', 'ASC')->findAll();
        }

        /**
         * Periodo (YYYY-MM) donde quedó la última impresión activa que tenga periodo, o null.
         * Ni las impresiones ni las simulaciones nuevas pueden ir en un periodo anterior.
         */
        public function ultimoPeriodoImpreso($libId)
        {
            foreach (array_reverse($this->impresiones($libId)) as $imp) {
                if ($imp['lim_periodo_hasta'] || $imp['lim_periodo_desde']) {
                    return $imp['lim_periodo_hasta'] ?: $imp['lim_periodo_desde'];
                }
            }

            return null;
        }

        /**
         * Libro anterior de la misma serie (mismo contribuyente y mismo tipo, activo y registrado antes)
         * que todavía tiene hojas en blanco sin imprimir. Mientras exista, el libro siguiente no puede
         * registrar impresiones ni simular. Devuelve null si no hay ninguno.
         */
        public function libroAnteriorConHojas($libro)
        {
            $anteriores = $this->select('libro.*, tipo_libro.tli_nombre')
                ->join('tipo_libro', 'tipo_libro.tli_id = libro.tli_id')
                ->where('libro.con_id', $libro['con_id'])
                ->where('libro.tli_id', $libro['tli_id'])
                ->where('libro.lib_estado', 1)
                ->where('libro.lib_id <', $libro['lib_id'])
                ->orderBy('libro.lib_id', 'DESC')
                ->findAll();

            foreach ($anteriores as $anterior) {
                $enBlanco = $this->calcular($anterior)['en_blanco'];

                if ($enBlanco > 0) {
                    return [
                        'lib_id' => $anterior['lib_id'],
                        'nombre' => $anterior['tli_nombre'] . ($anterior['lib_numero'] ? ' N° ' . $anterior['lib_numero'] : ''),
                        'en_blanco' => $enBlanco,
                    ];
                }
            }

            return null;
        }

        /**
         * Libro activo del mismo contribuyente y tipo que ya usa ese N° de libro (null si está libre).
         * Se ignora mayúsculas/espacios y ceros a la izquierda en números ("01" = "1"). $excluirId evita compararse consigo mismo al editar.
         */
        public function libroConNumero($conId, $tipoId, $numero, $excluirId = 0)
        {
            $normalizar = fn($n) => ctype_digit(trim((string) $n)) ? (string) (int) trim((string) $n) : mb_strtolower(trim((string) $n));
            $buscado = $normalizar($numero);

            $candidatos = $this->select('libro.lib_id, libro.lib_numero, tipo_libro.tli_nombre')
                ->join('tipo_libro', 'tipo_libro.tli_id = libro.tli_id')
                ->where('libro.con_id', $conId)
                ->where('libro.tli_id', $tipoId)
                ->where('libro.lib_estado', 1)
                ->findAll();

            foreach ($candidatos as $c) {
                if ((int) $c['lib_id'] !== (int) $excluirId && $normalizar($c['lib_numero']) === $buscado) {
                    return ['lib_id' => $c['lib_id'], 'nombre' => $c['tli_nombre'] . ' N° ' . $c['lib_numero']];
                }
            }

            return null;
        }

        public function simulaciones($libId)
        {
            return (new LibroSimulacionModel())
                ->select('libro_simulacion.*, ' . $this->nombreUsuario('libro_simulacion', 'lsi_user_add', 'usuario_add'), false)
                ->where('lib_id', $libId)->where('lsi_estado', 1)->orderBy('lsi_periodo', 'DESC')->orderBy('lsi_id', 'DESC')->findAll();
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
