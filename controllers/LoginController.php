<?php
namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Usuarios;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina($router)
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

        try {
            $correo = filter_var($_POST['usuario_correo'], FILTER_SANITIZE_EMAIL);
            $contraseña = $_POST['usuario_contra'];

            $query = "SELECT 
                        usuario_id, 
                        usuario_nom1, 
                        usuario_ape1, 
                        usuario_contra,
                        usuario_correo 
                      FROM usuario 
                      WHERE usuario_correo = '$correo' 
                        AND usuario_situacion = 1";

            $usuarioDB = self::fetchFirst($query);

            if (!$usuarioDB) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Credenciales incorrectas'
                ]);
                return;
            }

            if (!password_verify($contraseña, $usuarioDB['usuario_contra'])) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Credenciales incorrectas'
                ]);
                return;
            }

            $queryPermisos = "SELECT 
                                p.permiso_clave, 
                                p.permiso_nombre
                              FROM asig_permisos ap
                              INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id
                              WHERE ap.asignacion_usuario_id = {$usuarioDB['usuario_id']} 
                                AND ap.asignacion_situacion = 1
                                AND p.permiso_situacion = 1
                              LIMIT 1";

            $permiso = self::fetchFirst($queryPermisos);

            session_start();
            $_SESSION['auth_user'] = true;
            $_SESSION['usuario_id'] = $usuarioDB['usuario_id'];
            $_SESSION['usuario_correo'] = $usuarioDB['usuario_correo'];
            $_SESSION['usuario_nombre'] = trim($usuarioDB['usuario_nom1'] . ' ' . $usuarioDB['usuario_ape1']);
            $_SESSION['login'] = true;

            if ($permiso) {
                $_SESSION['rol'] = $permiso['permiso_clave'];
                $_SESSION['rol_nombre'] = $permiso['permiso_nombre'];
            } else {
                $_SESSION['rol'] = 'USER';
                $_SESSION['rol_nombre'] = 'Usuario Básico';
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuario autenticado exitosamente',
                'datos' => [
                    'usuario_id' => $usuarioDB['usuario_id'],
                    'usuario_correo' => $usuarioDB['usuario_correo'],
                    'nombre_completo' => $_SESSION['usuario_nombre'],
                    'rol' => $_SESSION['rol'],
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

        if (isset($_SESSION['auth_user']) && $_SESSION['auth_user'] === true) {
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Sesión activa',
                'datos' => [
                    'usuario_id' => $_SESSION['usuario_id'],
                    'usuario_nombre' => $_SESSION['usuario_nombre'],
                    'rol' => $_SESSION['rol']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Sesión inactiva'
            ]);
        }
    }
}