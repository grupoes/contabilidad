<?php

namespace App\Controllers\Api;

use App\Models\AnioModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\R08PlameModel;
use App\Models\ContribuyenteModel;
use App\Models\MesModel;
use App\Models\PdtAnualModel;
use App\Models\PdtPlameModel;
use App\Models\PdtRentaModel;
use App\Models\TrabajadoresContriModel;
use App\Models\TributoModel;
use App\Models\UserModel;
use Exception;

use setasign\Fpdi\Fpdi;

class AppUser extends ResourceController
{
    protected $format = 'json';

    public function empresas()
    {
        try {
            $data = $this->request->getJSON(true);

            $usuario = $data['usuario'];

            $r08 = new R08PlameModel();
            $empresa = new ContribuyenteModel();

            $consulta_rucs = $r08->query("SELECT ruc FROM `r08_plame` where numero_documento = '$usuario' GROUP BY ruc")->getResultArray();

            foreach ($consulta_rucs as $key => $value) {
                $ruc = $value['ruc'];

                $datos_empresa = $empresa->select('id, ruc, razon_social, direccion_fiscal')->where('ruc', $ruc)->first();

                $consulta_rucs[$key]['datos_empresa'] = $datos_empresa;
            }

            return $this->respond([
                'status' => true,
                'message' => 'Empresas encontradas',
                'empresas' => $consulta_rucs
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener las empresas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAnios()
    {
        try {
            $anio = new AnioModel();
            $anioActual = date('Y');
            $anios = $anio->where('anio_descripcion >=', 2024)->where('anio_descripcion <=', $anioActual)->orderBy('anio_descripcion', 'DESC')->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Años encontrados',
                'anios' => $anios
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener los años: ' . $e->getMessage()
            ], 500);
        }
    }

    public function itemBoletas()
    {
        try {
            $datos = $this->request->getJSON(true);

            $ruc = $datos['ruc'];
            $anio = $datos['anio'];
            $mes = $datos['mes'];
            $usuario = $datos['usuario'];

            $r08 = new R08PlameModel();
            $year = new AnioModel();
            $month = new MesModel();

            $anio_actual = date('Y');

            if ($anio == 0) {
                $anios = $year->where('anio_descripcion >=', 2025)->where('anio_descripcion <=', $anio_actual)->orderBy('anio_descripcion', 'DESC')->findAll();
            } else {
                $anios = [$year->find($anio)];
            }

            if ($mes == 0) {
                $sqlMeses = "";
            } else {
                $sqlMeses = "AND p.periodo = $mes";
            }

            foreach ($anios as $key => $value) {
                $idanio = $value['id_anio'];
                $boletas = $r08->query("SELECT r.id,r.nameFile, m.mes_descripcion, r.ruc FROM r08_plame r INNER JOIN pdt_plame p ON p.id_pdt_plame = r.plameId INNER JOIN mes m ON m.id_mes = p.periodo WHERE r.ruc = '$ruc' AND p.anio = $idanio $sqlMeses AND r.numero_documento = '$usuario' AND p.estado = 1 AND r.status = 1 ORDER BY p.periodo DESC")->getResultArray();

                $anios[$key]['boletas'] = $boletas;
            }

            return $this->respond([
                'status' => true,
                'message' => 'Boletas encontradas',
                'boletas' => $anios
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener las boletas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmpresa($ruc)
    {
        try {
            $empresaModel = new ContribuyenteModel();
            $empresa = $empresaModel->where('ruc', $ruc)->first();

            if (!$empresa) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Empresa encontrada',
                'empresa' => $empresa
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener la empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSelloFirma()
    {
        try {
            $file = $this->request->getFile('imagen');
            $ruc = $this->request->getPost('ruc');

            if (!$file || !$file->isValid()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se recibió ninguna imagen'
                ], 400);
            }

            // Validar tipo y tamaño (solo PNG, JPG, JPEG)
            $validation = \Config\Services::validation();
            $validation->setRules([
                'imagen' => [
                    'label' => 'Imagen',
                    'rules' => 'uploaded[imagen]|max_size[imagen,2048]|is_image[imagen]|mime_in[imagen,image/png,image/jpg,image/jpeg]'
                ]
            ]);


            if (!$validation->withRequest($this->request)->run()) {
                return $this->respond([
                    'status' => false,
                    'errors' => implode(' | ', $validation->getErrors())
                ], 400);
            }

            $mimeType = $file->getMimeType();
            $rutaOriginal = $file->getTempName();
            $tieneTransparencia = false;

            $codigo = str_pad(random_int(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $nombreSalida = $ruc . "_" . $codigo . '.png';
            $rutaSalida = FCPATH . 'archivos/sellos/' . $nombreSalida;

            // Solo verificar transparencia si es PNG
            if ($mimeType === 'image/png') {
                $tieneTransparencia = $this->pngTieneTransparencia($rutaOriginal);
            }

            if ($tieneTransparencia == false) {
                $this->quitarFondoSelloFirma($rutaOriginal, $rutaSalida);
            } else {
                $file->move(FCPATH . 'archivos/sellos', $nombreSalida);
            }

            $contri = new ContribuyenteModel();

            $contri->set('file_sello_firma', $nombreSalida);
            $contri->where('ruc', $ruc);
            $contri->update();

            return $this->respond([
                'status' => true,
                'message' => 'Imagen guardada correctamente',
                'filename' => $nombreSalida
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al guardar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    private function quitarFondoSelloFirma($rutaOriginal, $rutaSalida)
    {
        // Cargar imagen (JPG o PNG)
        $mime = mime_content_type($rutaOriginal);
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $src = imagecreatefromjpeg($rutaOriginal);
        } else {
            $src = imagecreatefrompng($rutaOriginal);
        }

        if (!$src) {
            throw new \Exception('No se pudo cargar la imagen');
        }

        $width  = imagesx($src);
        $height = imagesy($src);

        // Crear imagen destino con transparencia
        $dest = imagecreatetruecolor($width, $height);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);

        // Fondo transparente
        $transparente = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparente);

        // Umbral (ya vimos que ~140 te funciona)
        $umbral = 140;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {

                $rgb = imagecolorat($src, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Luminosidad promedio
                $luminosidad = ($r + $g + $b) / 3;

                // 1) Fondo claro → transparente
                if ($luminosidad > $umbral) {
                    imagesetpixel($dest, $x, $y, $transparente);
                    continue;
                }

                // 2) Detectar azul (firma) → mantener color
                // Condición de "azul dominante"
                if ($b > $r + 20 && $b > $g + 20) {
                    $colorAzul = imagecolorallocatealpha($dest, $r, $g, $b, 0);
                    imagesetpixel($dest, $x, $y, $colorAzul);
                } else {
                    // 3) Todo lo demás oscuro → negro (sello/texto)
                    $negro = imagecolorallocatealpha($dest, 0, 0, 0, 0);
                    imagesetpixel($dest, $x, $y, $negro);
                }
            }
        }

        // Suavizar bordes ligeramente
        imagefilter($dest, IMG_FILTER_SMOOTH, 4);

        // Guardar PNG con transparencia
        imagepng($dest, $rutaSalida);

        imagedestroy($src);
        imagedestroy($dest);
    }

    public function pngTieneTransparencia($ruta)
    {
        $img = imagecreatefrompng($ruta);
        if (!$img) return false;

        $width  = imagesx($img);
        $height = imagesy($img);

        for ($x = 0; $x < $width; $x += 10) {
            for ($y = 0; $y < $height; $y += 10) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;

                if ($alpha > 0) {
                    imagedestroy($img);
                    return true;
                }
            }
        }

        imagedestroy($img);
        return false;
    }

    public function getSelloFirma($ruc)
    {
        try {
            $contri = new ContribuyenteModel();

            $user = $contri->where('ruc', $ruc)->first();

            if (!$user || empty($user['file_sello_firma'])) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Sello o firma no encontrado'
                ], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Sello o firma encontrado',
                'filename' => $user['file_sello_firma']
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener el sello o firma: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarPdfSellado($id, $ruc)
    {
        try {
            $r08 = new R08PlameModel();
            $empresa = new ContribuyenteModel();


            $sello_data = $empresa->select('file_sello_firma')->where('ruc', $ruc)->first();

            $boleta = $r08->where('id', $id)->first();

            if (!$boleta || empty($boleta['nameFile'])) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Boleta no encontrada'
                ], 404);
            }

            $boletaPath = FCPATH . 'archivos/pdt/' . $boleta['nameFile'];

            if (!file_exists($boletaPath)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Archivo no encontrado en el servidor',
                    'path' => $boletaPath,
                    'exists' => file_exists($boletaPath),
                    'readable' => is_readable($boletaPath)
                ], 404);
            }

            // 📌 Verificar si existe sello
            $selloPath = null;
            if (!empty($sello_data['file_sello_firma'])) {
                $temp = FCPATH . 'archivos/sellos/' . $sello_data['file_sello_firma'];
                if (file_exists($temp)) {
                    $selloPath = $temp;
                }
            }

            $pdf = new FPDI();

            $pageCount = $pdf->setSourceFile($boletaPath);

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                // Agregar sello SOLO en la primera página
                if ($i === 1 && $selloPath) {
                    $pdf->Image(
                        $selloPath,
                        25, // X
                        $size['height'] - 60, // Y
                        40 // ancho
                    );
                }
            }

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="documento_sellado.pdf"')
                ->setBody($pdf->Output('S'));
        } catch (Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al descargar la boleta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function consultaPdtRenta()
    {
        $pdtRenta = new PdtRentaModel();

        try {
            $datos = $this->request->getJSON(true);

            $anio = $datos['anio'];
            $mesInicial = $datos['mes_inicial'];
            $mesFinal = $datos['mes_final'];
            $ruc = $datos['ruc'];

            if ($mesFinal < $mesInicial) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'El mes final no puede ser menor que el mes inicial'
                ]);
            }

            $consulta = $pdtRenta->query("SELECT ar.nombre_pdt, ar.nombre_constancia, a.anio_descripcion, m.mes_descripcion FROM pdt_renta pr INNER JOIN archivos_pdt0621 ar ON ar.id_pdt_renta = pr.id_pdt_renta INNER JOIN anio a ON a.id_anio = pr.anio INNER JOIN mes m ON m.id_mes = pr.periodo WHERE pr.anio = $anio AND (pr.periodo BETWEEN $mesInicial AND $mesFinal) AND pr.estado = 1 AND ar.estado = 1 AND pr.ruc_empresa = $ruc ORDER BY m.id_mes asc")->getResultArray();

            return $this->respond([
                'status' => 'ok',
                'data' => $consulta
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al consultar ' . $e->getMessage()
            ], 500);
        }
    }

    public function consultaPdtPlame()
    {
        $pdtPlame = new PdtPlameModel();

        try {
            $datos = $this->request->getJSON(true);

            $mes = $datos['mes'];
            $anio = $datos['anio'];
            $ruc = $datos['ruc'];

            $consulta = $pdtPlame->query("SELECT
            pdt_plame.periodo,pdt_plame.anio,archivos_pdtplame.id_archivos_pdtplame,archivos_pdtplame.archivo_planilla,archivos_pdtplame.archivo_honorarios,archivos_pdtplame.archivo_constancia,archivos_pdtplame.estado,archivos_pdtplame.id_pdtplame,anio.anio_descripcion,mes.mes_descripcion, pdt_plame.id_pdt_plame
            FROM pdt_plame
            INNER JOIN archivos_pdtplame ON archivos_pdtplame.id_pdtplame = pdt_plame.id_pdt_plame
            INNER JOIN anio ON pdt_plame.anio = anio.id_anio
            INNER JOIN mes ON mes.id_mes = pdt_plame.periodo
            WHERE pdt_plame.ruc_empresa = $ruc AND pdt_plame.anio = $anio AND pdt_plame.periodo = $mes AND archivos_pdtplame.estado = 1")->getRow();

            if ($consulta) {
                $idpdt = $consulta->id_pdt_plame;

                $r08 = new R08PlameModel();

                $consultaR08 = $r08->where('plameId', $idpdt)->where('status', 1)->findAll();

                if ($consultaR08) {
                    $consulta->r08 = "1";
                    $consulta->r08_data = $consultaR08;
                } else {
                    $consulta->r08 = "0";
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Se obtenió correctamente la consulta',
                'data' => $consulta
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al consultar ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword()
    {
        $datos = $this->request->getJSON(true);

        $currentPassword = $datos['currentPassword'];
        $newPassword = $datos['newPassword'];
        $confirmPassword = $datos['confirmPassword'];
        $usuario = $datos['usuario'];

        $contrib = new ContribuyenteModel();
        $job = new TrabajadoresContriModel();

        try {

            if (strlen($usuario) == 11) {
                $query = $contrib->where('ruc', $usuario)->where('acceso', $currentPassword)->first();

                if (!$query) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'La contraseña actual no coincide'
                    ]);
                }

                if ($newPassword !== $confirmPassword) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'No coinciden las contraseñas'
                    ]);
                }

                $update = array(
                    "acceso" => $newPassword
                );

                $contrib->update($query['id'], $update);
            } else {
                $query = $job->where('numero_documento', $usuario)->where('password', $currentPassword)->first();

                if (!$query) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'La contraseña actual no coincide'
                    ]);
                }

                if ($newPassword !== $confirmPassword) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'No coinciden las contraseñas'
                    ]);
                }

                $update = array(
                    "password" => $newPassword
                );

                $job->update($query['id'], $update);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Se cambio la contraseña correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al cambiar contraseña ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPdt($ruc)
    {
        $tributo = new TributoModel();

        try {
            $consulta = $tributo->query("SELECT tributo.tri_descripcion,pdt.pdt_descripcion,configuracion_notificacion.ruc_empresa_numero,configuracion_notificacion.id_tributo,tributo.id_pdt
            FROM tributo
            INNER JOIN configuracion_notificacion ON configuracion_notificacion.id_tributo = tributo.id_tributo
            INNER JOIN pdt ON tributo.id_pdt = pdt.id_pdt
            WHERE configuracion_notificacion.ruc_empresa_numero = $ruc and (tributo.id_pdt = 3 or tributo.id_pdt = 4 or tributo.id_pdt = 5 or tributo.id_pdt = 6)")->getResult();

            return $this->respond([
                'status' => 'success',
                'message' => 'verificación exitosa',
                'data' => $consulta
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'No se pudo verificar ' . $e->getMessage()
            ], 500);
        }
    }

    public function consultaPdtAnual()
    {
        $pdtAnual = new PdtAnualModel();

        try {
            $datos = $this->request->getJSON(true);

            $anio = $datos['anio'];
            $ruc = $datos['ruc'];
            $tipoPdt = $datos['tipoPdt'];

            $sql = "AND pdt_anual.periodo = $anio";

            if ($tipoPdt != "0") {
                $sql = "AND pdt_anual.periodo = $anio AND pdt_anual.id_pdt_tipo = $tipoPdt";
            }

            $consulta = $pdtAnual->query("SELECT pdt.pdt_descripcion,pdt_anual.ruc_empresa,pdt_anual.periodo,pdt_anual.id_pdt_tipo,archivos_pdtanual.id_pdt_anual,archivos_pdtanual.id_archivo_anual,archivos_pdtanual.pdt,archivos_pdtanual.constancia,anio.anio_descripcion
            FROM pdt_anual
            INNER JOIN pdt ON pdt_anual.id_pdt_tipo = pdt.id_pdt
            INNER JOIN archivos_pdtanual ON pdt_anual.id_pdt_anual = archivos_pdtanual.id_pdt_anual
            INNER JOIN anio ON pdt_anual.periodo = anio.id_anio
            WHERE pdt_anual.ruc_empresa = $ruc AND archivos_pdtanual.estado = 1 $sql")->getResult();

            return $this->respond([
                'status' => 'success',
                'message' => 'Se realizo la consulta correctamente',
                'data' => $consulta
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'error al realizar la consulta ' . $e->getMessage()
            ], 500);
        }
    }

    public function consultaAnalisisMovimientos()
    {
        $pdt = new PdtRentaModel();

        try {

            $datos = $this->request->getJSON(true);

            $anio = $datos['anio'];
            $ruc = $datos['ruc'];

            $data = $pdt->query("SELECT pr.id_pdt_renta, pr.periodo, pr.anio, FORMAT(pr.total_compras, 2, 'es_PE') as total_compras_decimal, FORMAT(pr.total_ventas, 2, 'es_PE') as total_ventas_decimal, FORMAT(pr.compras_gravadas, 2, 'es_PE') as compras_gravadas_decimal, FORMAT(pr.compras_no_gravadas, 2, 'es_PE') as compras_no_gravadas_decimal, FORMAT(pr.ventas_gravadas, 2, 'es_PE') as ventas_gravadas_decimal, FORMAT(pr.ventas_no_gravadas, 2, 'es_PE') as ventas_no_gravadas_decimal, pr.total_compras, pr.total_ventas, c.razon_social, pr.ruc_empresa, m.mes_descripcion, a.anio_descripcion, ap.nombre_pdt, pr.estado_datos FROM pdt_renta pr INNER JOIN contribuyentes c ON c.ruc = pr.ruc_empresa INNER JOIN mes m ON m.id_mes = pr.periodo INNER JOIN anio a ON a.id_anio = pr.anio INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.ruc_empresa = '$ruc' AND pr.anio = '$anio' AND pr.estado = 1 AND ap.estado = 1 ORDER BY pr.periodo asc")->getResultArray();

            return $this->respond([
                'status' => 'success',
                'message' => 'Se hizo la consulta correctamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'error en la consulta ' . $e->getMessage()
            ], 500);
        }
    }
}
