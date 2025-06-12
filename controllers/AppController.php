<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use MVC\Router;

class AppController extends ActiveRecord
{
    public static function index(Router $router)
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
            $contrasena = htmlspecialchars($_POST['usuario_contra']);

            $query = "SELECT usuario_id, usuario_nom1, usuario_ape1, usuario_contra FROM usuario 
                      WHERE usuario_correo = '$correo' AND usuario_situacion = 1";

            $usuarioDB = self::fetchFirst($query);

            if ($usuarioDB) {
                if (password_verify($contrasena, $usuarioDB['usuario_contra'])) {
                    session_start();

                    $_SESSION['user'] = $usuarioDB['usuario_nom1'] . ' ' . $usuarioDB['usuario_ape1'];
                    $_SESSION['user_id'] = $usuarioDB['usuario_id'];
                    $_SESSION['login'] = true;

                    // Buscar permisos asignados (considerados como "rol")
                    $sqlPermisos = "
                        SELECT p.permiso_clave, p.permiso_nombre
                        FROM asig_permisos ap
                        INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id
                        WHERE ap.asignacion_usuario_id = {$usuarioDB['usuario_id']} 
                          AND ap.asignacion_situacion = 1
                          AND p.permiso_situacion = 1
                        LIMIT 1
                    ";

                    $permiso = self::fetchFirst($sqlPermisos);

                    if ($permiso) {
                        $_SESSION['rol'] = $permiso['permiso_clave']; // Ej: 'ADMIN', 'USER'
                    } else {
                        $_SESSION['rol'] = 'USER'; // valor por defecto
                    }

                    http_response_code(200);
                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario logueado exitosamente',
                        'datos' => [
                            'usuario_nombre' => $_SESSION['user'],
                            'rol' => $_SESSION['rol'],
                            'redirect_url' => '/proyecto01/inicio'
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Contraseña incorrecta'
                    ]);
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario no existe o está inactivo'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar loguearse',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function verificarSesion()
    {
        getHeadersApi();
        session_start();

        if (!isset($_SESSION['login'])) {
            http_response_code(401);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Sesión no válida',
                'redirect_url' => '/proyecto01/'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'codigo' => 1,
            'mensaje' => 'Sesión válida',
            'datos' => [
                'usuario_nombre' => $_SESSION['user'],
                'rol' => $_SESSION['rol']
            ]
        ]);
    }

     public static function logout()
    {
        isAuth();
        $_SESSION = [];
        header('Location: /proyecto011/');

    }

    public static function renderInicio(Router $router)
    {
        session_start();

        if (!isset($_SESSION['login'])) {
            header('Location: /proyecto01/');
            exit;
        }

        $router->render('pages/index', [
            'usuario_nombre' => $_SESSION['user'],
            'rol' => $_SESSION['rol']
        ]);
    }
}
