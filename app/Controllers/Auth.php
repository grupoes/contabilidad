<?php

namespace App\Controllers;

use App\Models\ProfileModel;
use App\Models\SedeModel;
use App\Models\UserModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->logged_in) {
			return redirect()->to(base_url('home'));
		}

        return view('auth/login');
    }

    public function userAll()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $model = new ProfileModel();
        $sede = new SedeModel();

        $profiles = $model->getAllProfiles();
        $sedes = $sede->allSedes();

        return view('auth/listaUsuarios', compact('profiles', 'sedes'));
    }

    public function login()
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $data = $this->request->getPost();

        $username = $data['username'];
        $password = $data['password'];

        if (empty($username) || empty($password)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Usuario y contraseña son obligatorios.']);
        }

        $model = new UserModel();
        $user = $model->getUserByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $session = session();
            $session->set([
                'id' => $user['id'],
                'username' => $user['correo'],
                'nombre' => $user['nombres'],
                'apellidos' => $user['apellidos'],
                'perfil' => $user['nombre_perfil'],
                'perfil_id' => $user['perfil_id'],
                'sede_id' => $user['sede_id'],
                'logged_in' => true
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Inicio de sesión exitoso.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Credenciales inválidas.']);
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return $this->response->setJSON(['status' => 'success']);
    }

    public function guardarUsuario()
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $data = $this->request->getPost();

        $tipoDocumento = $data['tipoDocumento'];
        $numeroDocumento = $data['numeroDocumento'];
        $nombres = $data['nombres'];
        $apellidos = $data['apellidos'];
        $fechaNacimiento = $data['fechaNacimiento'];
        $direccion = $data['direccion'];
        $celular = $data['celular'];
        $numeroCuenta = $data['numeroCuenta'];
        $sede = $data['sede'];
        $perfil = $data['perfil'];
        $correo = $data['correo'];
        $password = $data['password'];

        $newNameImageUser = "avatar-2.jpg";

        if ($this->request->getFile('foto')->isValid()) {
            $file = $this->request->getFile('foto');

            // Opcional: valida el tipo o tamaño del archivo
            if ($file->isValid() && !$file->hasMoved()) {
                $uploadPath = FCPATH . 'public/assets/images/user/';
                $newNameImageUser = $file->getRandomName();
                $file->move($uploadPath, $newNameImageUser);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo no es válido o ya fue movido']);
            }
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'El correo electrónico no es válido.']);
        }

        $model = new UserModel();

        if ($model->getUserByEmail($correo)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'El correo electrónico ya está registrado.']);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $datos = array(
            "correo" => $correo,
            "password" => $hashedPassword,
            "perfil_id" => $perfil,
            "sede_id" => $sede,
            "tipo_documento_id" => $tipoDocumento,
            "numero_documento" => $numeroDocumento,
            "nombres" => $nombres,
            "apellidos" => $apellidos,
            "telefono" => $celular,
            "direccion" => $direccion,
            "fecha_nacimiento" => $fechaNacimiento,
            "numero_cuenta" => $numeroCuenta,
            "estado" => 1,
            "path" => base_url('public/assets/images/user/'.$newNameImageUser)
        );

        $model->insert($datos);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Usuario registrado exitosamente.']);
    }

    public function api_dni_ruc($tipo,$numero)
    {
        $token = "facturalaya_erickpeso_05jFE7sAOudi8j0";

        $bloquear_busquedas = false;
		if ($bloquear_busquedas) {
			$resp['respuesta'] = 'error';
			$resp['titulo'] = 'Error';
			$resp['mensaje'] = 'Tenemos Problemas en los Servidores de SUNAT y RENIEC, ingresa los datos manualmente por favor...';
			return $this->response->setJSON($resp);
		}

        if ($tipo == 'dni') {
			$ruta = "https://facturalahoy.com/api/persona/" . $numero . '/' . $token . '/completa';
		} elseif ($tipo == 'ruc') {
			$ruta = "https://facturalahoy.com/api/empresa/" . $numero . '/' . $token . '/completa';
		} else {
			$resp['respuesta'] = 'error';
			$resp['titulo'] = 'Error';
			$resp['mensaje'] = 'Tipo de Documento Desconocido';
			return $this->response->setJSON($resp);
		}

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $ruta,
			CURLOPT_USERAGENT => 'Consulta Datos',
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_TIMEOUT => 400,
			CURLOPT_FAILONERROR => true
		));

		$data = curl_exec($curl);
		if (curl_error($curl)) {
			$error_msg = curl_error($curl);
		}

		curl_close($curl);

		if (isset($error_msg)) {
			$resp['respuesta'] = 'error';
			$resp['titulo'] = 'Error';
			$resp['data'] = $data;
			$resp['encontrado'] = false;
			$resp['mensaje'] = 'Error en Api de Búsqueda';
			$resp['errores_curl'] = $error_msg;
			return $this->response->setJSON($resp);
		}

		$data_resp = json_decode($data);
		if (!isset($data_resp->respuesta) || $data_resp->respuesta == 'error') {
			$resp['respuesta'] = 'error';
			$resp['titulo'] = 'Error';
			$resp['encontrado'] = false;
			$resp['data_resp'] = $data_resp;
			return $this->response->setJSON($resp);
		}

		$resp['respuesta'] = 'ok';
		$resp['encontrado'] = true;
		$resp['api'] = true;
		$resp['data'] = json_decode($data);

        return $this->response->setJSON($resp);
    }

    public function showUsers()
    {
        $user = new UserModel();

        $usuarios = $user->usersAll();

        return $this->response->setJSON($usuarios);
    }

}
