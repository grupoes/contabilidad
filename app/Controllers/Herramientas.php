<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Herramientas extends BaseController
{
    public function compararProductos()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url());
        }

        $excelPath   = ROOTPATH . 'ingresados.xlsx';
        $spreadsheet = IOFactory::load($excelPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray();

        $headerRow = $rows[0];
        $colIndex  = array_search('DESCRIPCION EN FORMATO', $headerRow);

        $excelDescs = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) continue;
            $val = trim($row[$colIndex] ?? '');
            if ($val !== '') {
                $excelDescs[] = $val;
            }
        }

        $db       = \Config\Database::connect('facturador');
        $productos = $db->query(
            "SELECT idproducto, nombre FROM producto WHERE nombre IS NOT NULL AND nombre != ''"
        )->getResultArray();
        $dbNames = array_column($productos, 'nombre');
        $dbIds   = array_column($productos, 'idproducto');

        $resultados = [];
        foreach ($excelDescs as $desc) {
            $descNorm = strtolower(trim($desc));

            // Núcleo: parte antes de ", CON MARCA ..." — es el nombre real del producto
            // Ej: "PARLANTE GTS-2176, CON MARCA GTS, MODELO 2176..." → "parlante gts-2176"
            $coreNorm = strtolower(trim(preg_replace('/,?\s*con marca.*/i', '', $desc)));

            $bestMatch = '';
            $bestId    = null;
            $bestScore = 0;
            $matchType = 'none';

            foreach ($dbNames as $idx => $nombre) {
                $nombreNorm = strtolower(trim($nombre));

                // 1. Exacto con descripción completa
                if ($descNorm === $nombreNorm) {
                    $bestMatch = $nombre;
                    $bestId    = $dbIds[$idx];
                    $bestScore = 100;
                    $matchType = 'exact';
                    break;
                }

                // 2. DB es igual al núcleo del Excel — coincidencia estructural máxima
                //    Ej: DB="PARLANTE GTS-2176" núcleo="PARLANTE GTS-2176"
                if ($nombreNorm === $coreNorm) {
                    if (97 > $bestScore) {
                        $bestMatch = $nombre;
                        $bestId    = $dbIds[$idx];
                        $bestScore = 97;
                        $matchType = 'core_exact';
                    }
                    continue;
                }

                // 3. Prefijo estructural respecto al núcleo (en cualquier dirección):
                //    a) DB es prefijo del núcleo: DB="PARLANTE KTS-2145" núcleo="PARLANTE KTS-2145 SIN NUMERO..."
                //    b) Núcleo es prefijo del DB: núcleo="EXTENSION MULTIPLE X6" DB="EXTENSION MULTIPLE X6 150196"
                if (strlen($nombreNorm) >= 4 &&
                    (str_starts_with($coreNorm, $nombreNorm) || str_starts_with($nombreNorm, $coreNorm))
                ) {
                    $lenDb   = strlen($nombreNorm);
                    $lenCore = strlen($coreNorm);
                    $ratio   = min($lenDb, $lenCore) / max($lenDb, $lenCore);
                    $score   = (int) round(85 + $ratio * 14);
                    if ($score > $bestScore) {
                        $bestMatch = $nombre;
                        $bestId    = $dbIds[$idx];
                        $bestScore = $score;
                        $matchType = 'prefix_match';
                    }
                    continue;
                }

                // 4. Fuzzy contra el núcleo (no el Excel completo, para evitar que las
                //    palabras del boilerplate "CON MARCA / MODELO / COLOR" inflen el score)
                similar_text($coreNorm, $nombreNorm, $pct);
                $score = (int) round($pct);
                if ($score > $bestScore) {
                    $bestMatch = $nombre;
                    $bestId    = $dbIds[$idx];
                    $bestScore = $score;
                    $matchType = 'fuzzy';
                }
            }

            $resultados[] = [
                'excel'      => $desc,
                'db_match'   => $bestMatch,
                'db_id'      => $bestId,
                'score'      => $bestScore,
                'match_type' => $matchType,
            ];
        }

        return view('herramientas/comparar_productos', ['resultados' => $resultados]);
    }
}
