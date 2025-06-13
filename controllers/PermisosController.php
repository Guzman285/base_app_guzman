<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Permisos;

class PermisosController extends ActiveRecord
{
    private static $permisos_sistema = [
        'ADMINISTRADOR' => [
            'clave' => 'ADMIN_FULL',
            'descripcion' => 'Acceso completo al sistema',
            'tipo' => 'ADMIN'
        ],
        'DESARROLLADOR' => [
            'clave' => 'DEV_ACCESS',
            'descripcion' => 'Acceso a desarrollo y configuración',
            'tipo' => 'FUNCIONAL'
        ],
        'EMPLEADO' => [
            'clave' => 'EMP_BASIC',
            'descripcion' => 'Acceso básico de consulta',
            'tipo' => 'LECTURA'
        ]
    ];

    public static function renderizarPagina(Router $router)
    {
        $router->render('permisos/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validar datos de entrada
            $usuario_id = filter_var($_POST['usuario_id'], FILTER_SANITIZE_NUMBER_INT);
            $permiso_nombre = strtoupper(trim($_POST['permiso_nombre']));

            if ($usuario_id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un usuario válido'
                ]);
                exit;
            }

            // Validar tipo de permiso
            $permisos_validos = ['ADMINISTRADOR', 'DESARROLLADOR', 'EMPLEADO'];
            if (!in_array($permiso_nombre, $permisos_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Tipo de permiso no válido'
                ]);
                exit;
            }

            // Verificar si usuario ya tiene permiso
            $sql_verificar = "SELECT COUNT(*) as total FROM permiso WHERE usuario_id = $usuario_id AND permiso_situacion = 1";
            $resultado = self::fetchFirst($sql_verificar);

            if ($resultado && $resultado['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario ya tiene un rol asignado'
                ]);
                exit;
            }

            // Insertar nuevo permiso
            $sql_insertar = "INSERT INTO permiso (usuario_id, permiso_app_id, permiso_nombre, permiso_clave, permiso_desc, permiso_situacion) 
                        VALUES ($usuario_id, 1, '$permiso_nombre', 'ROL_$permiso_nombre', 'Rol de $permiso_nombre', 1)";

            $resultado_insercion = self::SQL($sql_insertar);

            if ($resultado_insercion) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Rol asignado correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al insertar en base de datos'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;

            $condiciones = ["p.permiso_situacion = 1"];

            if ($usuario_id) {
                $condiciones[] = "p.usuario_id = {$usuario_id}";
            }

            $where = implode(" AND ", $condiciones);
            $sql = "SELECT 
                    p.permiso_id,
                    p.usuario_id,
                    p.permiso_app_id,
                    p.permiso_nombre,
                    p.permiso_clave,
                    p.permiso_desc,
                    p.permiso_fecha,
                    p.permiso_situacion,
                    u.usuario_nom1,
                    u.usuario_ape1
                FROM permiso p 
                INNER JOIN usuario u ON p.usuario_id = u.usuario_id
                WHERE $where 
                ORDER BY p.permiso_fecha DESC";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Permisos obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los permisos',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['permiso_id'];
        $_POST['usuario_id'] = filter_var($_POST['usuario_id'], FILTER_SANITIZE_NUMBER_INT);

        if ($_POST['usuario_id'] <= 0) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Debe seleccionar un usuario válido'
            ]);
            return;
        }

        $_POST['permiso_nombre'] = strtoupper(trim(htmlspecialchars($_POST['permiso_nombre'])));

        if (!array_key_exists($_POST['permiso_nombre'], self::$permisos_sistema)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Permiso no válido. Use: ADMINISTRADOR, DESARROLLADOR o EMPLEADO'
            ]);
            return;
        }

        try {
            $permiso_data = self::$permisos_sistema[$_POST['permiso_nombre']];

            $data = Permisos::find($id);
            $data->sincronizar([
                'usuario_id' => $_POST['usuario_id'],
                'permiso_nombre' => $_POST['permiso_nombre'],
                'permiso_clave' => $permiso_data['clave'],
                'permiso_desc' => $permiso_data['descripcion'],
                'permiso_tipo' => $permiso_data['tipo'],
                'permiso_situacion' => 1
            ]);
            $data->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'El rol ha sido modificado exitosamente'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al guardar',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function EliminarAPI()
    {
        try {
            $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            $ejecutar = Permisos::EliminarPermiso($id);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'El registro ha sido eliminado correctamente'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al Eliminar',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarUsuariosAPI()
    {
        try {
            $sql = "SELECT usuario_id, usuario_nom1, usuario_ape1 
                    FROM usuario 
                    WHERE usuario_situacion = 1 
                    ORDER BY usuario_nom1";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los usuarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
