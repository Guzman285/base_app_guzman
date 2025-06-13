<?php

namespace Controllers;

use Model\ActiveRecord;
use MVC\Router;
use Exception;

class LoginController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('login/index', [], $layout = 'layout/layoutlogin');
    }

    public static function login()
    {
        getHeadersApi();

        try {
            $dpi = htmlspecialchars($_POST['usu_codigo']);
            $contrasena = htmlspecialchars($_POST['usu_password']);

            $queryExisteUser = "SELECT usuario_id, usuario_nom1, usuario_contra FROM usuario WHERE usuario_dpi = '$dpi' AND usuario_situacion = 1";

            $existeUsuario = ActiveRecord::fetchArray($queryExisteUser)[0];

            if ($existeUsuario) {
                $passDB = $existeUsuario['usuario_contra'];

                if (password_verify($contrasena, $passDB)) {
                    session_start();

                    $nombreUser = $existeUsuario['usuario_nom1'];
                    $usuarioId = $existeUsuario['usuario_id'];

                    $_SESSION['user'] = $nombreUser;
                    $_SESSION['dpi'] = $dpi;
                    $_SESSION['usuario_id'] = $usuarioId;

                    // Consulta corregida según tu estructura real
                    $sqlpermisos = "SELECT p.permiso_nombre 
                FROM asig_permisos ap 
                INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id 
                WHERE ap.asignacion_usuario_id = $usuarioId 
                AND ap.asignacion_situacion = 1 
                AND p.permiso_situacion = 1";

                    $permisos = ActiveRecord::fetchArray($sqlpermisos);

                    if (!empty($permisos)) {
                        $_SESSION['rol'] = $permisos[0]['permiso_nombre'];
                        $_SESSION['permisos'] = array_column($permisos, 'permiso_nombre');
                    } else {
                        $_SESSION['rol'] = 'USUARIO_BASICO';
                        $_SESSION['permisos'] = [];
                    }

                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario iniciado exitosamente',
                    ]);
                } else {
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La contraseña que ingreso es incorrecta',
                    ]);
                }
            } else {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario que intenta ingresar no existe',
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar ingresar',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function logout()
    {
        isAuth();
        $_SESSION = [];
        $login = $_ENV['APP_NAME'];
        header("Location: /$login");
    }
}
