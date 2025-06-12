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

        // Validaciones básicas
        if (empty($_POST['usu_codigo'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El código de usuario es obligatorio'
            ]);
            return;
        }

        if (empty($_POST['usu_password'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'La contraseña es obligatoria'
            ]);
            return;
        }

        try {
            $usuario = filter_var($_POST['usu_codigo'], FILTER_SANITIZE_NUMBER_INT);
            $contrasena = htmlspecialchars($_POST['usu_password']);

            $queryExisteUser = "SELECT usu_id, usu_nombre, usu_password FROM usuario_login2025 
                               WHERE usu_codigo = $usuario AND usu_situacion = 1";

            $existeUsuario = self::fetchFirst($queryExisteUser);

            if ($existeUsuario) {
                $passDB = $existeUsuario['usu_password'];

                if (password_verify($contrasena, $passDB)) {
                    session_start();

                    $nombreUser = $existeUsuario['usu_nombre'];
                    $usuarioId = $existeUsuario['usu_id'];

                    $_SESSION['user'] = $nombreUser;
                    $_SESSION['user_id'] = $usuarioId;
                    $_SESSION['login'] = true;
                               
                    // Obtener rol del usuario
                    $sqlPermisos = "SELECT permiso_rol, rol_nombre_ct FROM permiso_login2025
                                   INNER JOIN rol_login2025 ON rol_id = permiso_rol
                                   INNER JOIN usuario_login2025 ON usu_id = permiso_usuario
                                   WHERE usu_codigo = $usuario AND permiso_login2025.permiso_situacion = 1";

                    $permiso = self::fetchFirst($sqlPermisos);

                    if ($permiso) {
                        $_SESSION['rol'] = $permiso['rol_nombre_ct'];
                        $_SESSION['rol_id'] = $permiso['permiso_rol'];
                    } else {
                        $_SESSION['rol'] = 'USER';
                        $_SESSION['rol_id'] = 2;
                    }

                    http_response_code(200);
                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario logueado exitosamente',
                        'datos' => [
                            'usuario_nombre' => $nombreUser,
                            'rol' => $_SESSION['rol'],
                            'redirect_url' => '/proyecto01/inicio'
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La contraseña que ingresó es incorrecta'
                    ]);
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario que intenta loguearse NO EXISTE'
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

    public static function logout()
    {
        isAuth();
        $_SESSION = [];
        header('Location: /proyecto01/');

    }
        
    public static function renderInicio(Router $router)
    {
        session_start();
        
        // Verificar si hay sesión activa
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