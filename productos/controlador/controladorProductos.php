<?php
class controladorProducto
{
    private $modelo;
    public function __construct()
    {
        require_once("../modelo/modeloProductos.php");
        $this->modelo = new modeloProducto();
    }

    // Verifica rol (Admin Producción)
    private function checkRol()
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin Producción') {
            http_response_code(403);
            exit('Acceso denegado');
        }
    }

    public function index()
    {
        //$this->checkRol();
        include __DIR__ . '/../vista/productos.php';
    }

    public function listar()
    {
        //$this->checkRol();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->modelo->listarProductos());
    }

    public function validarNombre()
    {
        //$this->checkRol();
        $nombre = trim($_GET['nombre'] ?? '');
        $existe = $nombre !== '' ? $this->modelo->existeNombre($nombre) : false;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['existe' => $existe]);
    }

    public function categorias()
    {
        //$this->checkRol();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->modelo->obtenerCategoriasProd());
    }

    public function materias()
    {
        //$this->checkRol();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->modelo->obtenerMateriasPrimas());
    }

    public function crear()
    {
        //$this->checkRol();
        try {
            $nombre        = trim($_POST['nombre'] ?? '');
            $categoriaProd = (int)($_POST['categoriaProd'] ?? 0);
            $unidad        = trim($_POST['unidad_medida'] ?? '');
            $descripcion   = trim($_POST['descripcion'] ?? '');

            // <<< nuevo: precio_venta
            $precio_venta  = isset($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : 0.0;

            if ($nombre === '' || $categoriaProd <= 0 || $unidad === '') {
                throw new Exception("Datos obligatorios incompletos");
            }
            if ($this->modelo->existeNombre($nombre)) {
                throw new Exception("Ya existe un producto con ese nombre");
            }
            if ($precio_venta < 0) {
                throw new Exception("El precio de venta no puede ser negativo");
            }

            $componentes = json_decode($_POST['componentes'] ?? '[]', true);
            if (!is_array($componentes) || count($componentes) < 1) {
                throw new Exception("Debe agregar al menos una materia prima con cantidad");
            }

            $id = $this->modelo->crearProducto(
                [
                    'nombre'        => $nombre,
                    'categoriaProd' => $categoriaProd,
                    'unidad_medida' => $unidad,
                    'descripcion'   => $descripcion ?: null,
                    'precio_venta'  => $precio_venta,  // <<< pasa al modelo
                ],
                $componentes
            );

            $_SESSION['flash_success'] = "Producto #$id registrado correctamente.";
            header('Location: ./index.php');
            exit();
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Error al registrar: " . $e->getMessage();
            header('Location: ./index.php');
            exit();
        }
    }

    public function ver(int $id): array
    {
        return $this->modelo->obtener($id);
    }

    // <<< firma actualizada con precio_venta
    public function editar(
        int $id,
        string $nombre,
        string $categoria,
        string $unidad,
        float $precio_venta,
        array $componentes
    ): bool {
        return $this->modelo->editar($id, $nombre, $categoria, $unidad, $precio_venta, $componentes);
    }

    public function desactivar(int $id): bool
    {
        return $this->modelo->desactivar($id);
    }

    public function activar($idProducto)
    {
        try {
            $ok = $this->modelo->activar($idProducto);
            return ['ok' => $ok];
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /* ====== ESTADÍSTICAS (JSON para AJAX) ====== */
    public function estadisticas()
    {
        //$this->checkRol();
        header('Content-Type: application/json; charset=utf-8');

        $desde = $_POST['fecha_desde'] ?? '';
        $hasta = $_POST['fecha_hasta'] ?? '';

        if ($desde === '' || $hasta === '') {
            echo json_encode([
                'ok'    => false,
                'error' => 'Debe seleccionar ambas fechas.'
            ]);
            return;
        }

        if (strtotime($desde) === false || strtotime($hasta) === false || $desde > $hasta) {
            echo json_encode([
                'ok'    => false,
                'error' => 'Rango de fechas inválido.'
            ]);
            return;
        }

        try {
            $data = $this->modelo->estadisticasProductos($desde, $hasta);
            echo json_encode([
                'ok'   => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /* ====== REPORTES (JSON para AJAX) ====== */
    public function reporte()
    {
        //$this->checkRol();
        header('Content-Type: application/json; charset=utf-8');

        $modo  = $_POST['modo'] ?? 'todos'; // 'todos' | 'rango'
        $desde = $_POST['fecha_desde'] ?? null;
        $hasta = $_POST['fecha_hasta'] ?? null;

        if ($modo === 'rango') {
            if (empty($desde) || empty($hasta)) {
                echo json_encode([
                    'ok'    => false,
                    'error' => 'Debe seleccionar ambas fechas para filtrar por rango.'
                ]);
                return;
            }
            if (strtotime($desde) === false || strtotime($hasta) === false || $desde > $hasta) {
                echo json_encode([
                    'ok'    => false,
                    'error' => 'Rango de fechas inválido.'
                ]);
                return;
            }
        } else {
            $desde = null;
            $hasta = null;
        }

        try {
            $rows = $this->modelo->productosReporte($desde, $hasta);
            echo json_encode([
                'ok'   => true,
                'data' => $rows
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'ok'    => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
