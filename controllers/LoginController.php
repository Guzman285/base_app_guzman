<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Usuarios;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('login/index', [], 'layouts/layoutLogin');
    }

    public static function loginAPI()
    {
        getHeadersApi();

        if (empty($_POST['usuario_correo'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El correo electrónico es obligatorio'
            ]);
            return;
        }

        if (empty($_POST['usuario_contra'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'La contraseña es obligatoria'
            ]);
            return;
        }

        if (!filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El formato del correo electrónico no es válido'
            ]);
            return;
        }

        try {
            $correo = filter_var($_POST['usuario_correo'], FILTER_SANITIZE_EMAIL);
            $contraseña = $_POST['usuario_contra'];
            $usuarios = Usuarios::where('usuario_correo', $correo);

            if (empty($usuarios)) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Credenciales incorrectas'
                ]);
                return;
            }

            /** @var Usuarios $usuario */
            $usuario = $usuarios[0];

            if ($usuario->usuario_situacion != 1) {
                http_response_code(403);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La cuenta de usuario está inactiva'
                ]);
                return;
            }

            if (!password_verify($contraseña, $usuario->usuario_contra)) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Credenciales incorrectas'
                ]);
                return;
            }

            // Generar nuevo token
            $nuevoToken = bin2hex(random_bytes(32));
            $usuario->usuario_token = $nuevoToken;
            $resultado = $usuario->guardar();

            if (!$resultado['resultado']) {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error interno del servidor'
                ]);
                return;
            }

            // Iniciar sesión
            session_start();
            $_SESSION['auth_user'] = true;
            $_SESSION['usuario_id'] = $usuario->usuario_id;
            $_SESSION['usuario_token'] = $nuevoToken;
            $_SESSION['usuario_nombre'] = $usuario->usuario_nom1 . ' ' . $usuario->usuario_ape1;
            $_SESSION['login'] = true;

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Inicio de sesión exitoso',
                'datos' => [
                    'usuario_id' => $usuario->usuario_id,
                    'nombre_completo' => $usuario->usuario_nom1 . ' ' . $usuario->usuario_ape1,
                    'redirect_url' => '/proyecto011/'
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function logout()
    {
        session_start();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        header('Location: /proyecto011/login');
        exit;
    }

    public static function verificarSesion()
    {
        getHeadersApi();
        session_start();

        if (!isset($_SESSION['auth_user'])) {
            http_response_code(401);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Sesión no válida',
                'redirect_url' => '/proyecto011/login'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'codigo' => 1,
            'mensaje' => 'Sesión válida',
            'datos' => [
                'usuario_id' => $_SESSION['usuario_id'],
                'nombre_completo' => $_SESSION['usuario_nombre']
            ]
        ]);
    }
}
