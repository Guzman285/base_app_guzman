<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Usuarios;

class AppController extends ActiveRecord
{
    public static function index(Router $router)
    {
        $router->render('pages/index', [], 'layouts/layout');
    }

    public static function renderLoginCodigo(Router $router)
    {
        $router->render('logincodigo/index', [], 'layouts/layoutLogin');
    }

    public static function loginCodigoAPI()
    {
        getHeadersApi();

        if (empty($_POST['usuario_codigo'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El código de usuario es obligatorio'
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
            $codigo = filter_var($_POST['usuario_codigo'], FILTER_SANITIZE_NUMBER_INT);
            $contraseña = $_POST['usuario_contra'];

            $query = "SELECT 
                        usuario_id, 
                        usuario_nom1, 
                        usuario_ape1, 
                        usuario_contra,
                        usuario_dpi 
                      FROM usuario 
                      WHERE usuario_dpi = '$codigo' 
                        AND usuario_situacion = 1";

            $usuarioDB = self::fetchFirst($query);

            if (!$usuarioDB) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El código de usuario no existe o está inactivo'
                ]);
                return;
            }

            if (!password_verify($contraseña, $usuarioDB['usuario_contra'])) {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Contraseña incorrecta'
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
            $_SESSION['usuario_codigo'] = $usuarioDB['usuario_dpi'];
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
                    'usuario_codigo' => $usuarioDB['usuario_dpi'],
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

    public static function logoutCodigo()
    {
        session_start();
        session_destroy();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        header('Location: /proyecto011/logincodigo');
        exit;
    }
}