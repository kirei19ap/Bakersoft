<?php

class controladorPedidos
{
    private $modelo;

    public function __construct()
    {
        require_once("../modelo/modeloPedidos.php");
        $this->modelo = new modeloPedidos();
    }

    public function obtenerProductosVenta()
    {
        return $this->modelo->obtenerProductosVenta();
    }

    public function listarPedidos()
    {
        return $this->modelo->listarPedidosVenta();
    }

    public function obtenerPedidoCompleto(int $idPedidoVenta)
    {
        return $this->modelo->obtenerPedidoCompleto($idPedidoVenta);
    }

    /**
     * Búsqueda de clientes para el buscador (AJAX, JSON).
     */
    public function buscarClientes()
    {
        $termino = trim($_GET['q'] ?? '');
        header('Content-Type: application/json; charset=utf-8');

        if ($termino === '') {
            echo json_encode([]);
            return;
        }

        $resultado = $this->modelo->buscarClientes($termino);
        echo json_encode($resultado);
    }

    /**
     * Alta de pedido:
     * - Si modoCliente = 'existente' y idCliente > 0 → usa cliente existente.
     * - Si no → crea cliente nuevo (flujo anterior).
     */
    public function crearPedido()
    {
        // Datos del cliente
        $nombre      = trim($_POST['clienteNombre'] ?? '');
        $email       = trim($_POST['clienteEmail'] ?? '');
        $telefono    = trim($_POST['clienteTelefono'] ?? '');
        $calle       = trim($_POST['clienteCalle'] ?? '');
        $alturaInput = trim($_POST['clienteAltura'] ?? '');

        // Control de modo cliente
        $modoCliente = $_POST['modoCliente'] ?? 'nuevo';
        $idCliente   = (int)($_POST['idCliente'] ?? 0);

        // Datos del pedido
        $fechaPedido   = $_POST['fechaPedido'] ?? date('Y-m-d');
        $observaciones = trim($_POST['observaciones'] ?? '');

        // Detalle
        $idsProducto = $_POST['idProducto'] ?? [];
        $cantidades  = $_POST['cantidad'] ?? [];
        $precios     = $_POST['precioUnitario'] ?? [];

        // Validación mínima de cliente
        if ($modoCliente === 'existente') {
            if ($idCliente <= 0) {
                $this->redirConError('Debe seleccionar un cliente existente o registrar uno nuevo.');
            }
        } else {
            // Cliente nuevo: seguimos exigiendo nombre
            if ($nombre === '') {
                $this->redirConError('Debe indicar el nombre del cliente.');
            }
        }

        // Armar detalle y total
        $detalle = [];
        $total   = 0;

        if (is_array($idsProducto)) {
            $n = count($idsProducto);
            for ($i = 0; $i < $n; $i++) {
                $idProd = (int)($idsProducto[$i] ?? 0);
                $cant   = isset($cantidades[$i]) ? floatval(str_replace(',', '.', $cantidades[$i])) : 0;
                $precio = isset($precios[$i]) ? floatval(str_replace(',', '.', $precios[$i])) : 0;

                if ($idProd > 0 && $cant > 0) {
                    $subtotal = $cant * $precio;
                    $detalle[] = [
                        'idProducto'    => $idProd,
                        'cantidad'      => $cant,
                        'precioUnitario' => $precio,
                        'subtotal'      => $subtotal,
                    ];
                    $total += $subtotal;
                }
            }
        }

        if (empty($detalle)) {
            $this->redirConError('El pedido debe contener al menos un producto con cantidad mayor a cero.');
        }

        // Fecha datetime
        $fechaBase      = $fechaPedido ?: date('Y-m-d');
        $fechaDateTime  = $fechaBase . ' 00:00:00';

        $pedido = [
            'fechaPedido'   => $fechaDateTime,
            'estado'        => 70, // Generado
            'observaciones' => $observaciones,
            'total'         => $total,
        ];

        if ($modoCliente === 'existente' && $idCliente > 0) {
            // === CLIENTE EXISTENTE ===
            // Si el nombre no viene vacío, asumimos que los datos del formulario
            // son la versión actualizada y los grabamos en la tabla clientes.
            if ($nombre !== '') {
                $clienteExistente = [
                    'idCliente' => $idCliente,
                    'nombre'    => $nombre,
                    'email'     => $email,
                    'telefono'  => $telefono,
                    'calle'     => $calle,
                    'altura'    => $alturaInput !== '' ? (int)$alturaInput : 0,
                ];
                $this->modelo->actualizarClienteBasico($clienteExistente);
            }

            $resultado = $this->modelo->registrarPedidoParaClienteExistente($idCliente, $pedido, $detalle);
        } else {
            // === CLIENTE NUEVO (flujo anterior) ===
            $cliente = [
                'nombre'    => $nombre,
                'email'     => $email,
                'telefono'  => $telefono,
                'calle'     => $calle,
                'altura'    => $alturaInput !== '' ? (int)$alturaInput : 0,
                'provincia' => 1,
                'localidad' => 1,
                'estado'    => 'Activo',
            ];

            $resultado = $this->modelo->registrarPedidoCompleto($cliente, $pedido, $detalle);
        }


        if ($resultado['ok']) {
            $this->redirConOk('Pedido registrado correctamente.', $resultado['idPedidoVenta']);
        } else {
            $this->redirConError('Ocurrió un error al registrar el pedido: ' . $resultado['error']);
        }
    }

    public function actualizarPedido()
    {
        $idPedidoVenta = (int)($_POST['idPedidoVenta'] ?? 0);
        $idCliente     = (int)($_POST['idCliente'] ?? 0);

        if ($idPedidoVenta <= 0 || $idCliente <= 0) {
            $this->redirConError('Datos de pedido o cliente inválidos.');
        }

        $nombre      = trim($_POST['clienteNombre'] ?? '');
        $email       = trim($_POST['clienteEmail'] ?? '');
        $telefono    = trim($_POST['clienteTelefono'] ?? '');
        $calle       = trim($_POST['clienteCalle'] ?? '');
        $alturaInput = trim($_POST['clienteAltura'] ?? '');

        $fechaPedido   = $_POST['fechaPedido'] ?? date('Y-m-d');
        $observaciones = trim($_POST['observaciones'] ?? '');

        $idsProducto = $_POST['idProducto'] ?? [];
        $cantidades  = $_POST['cantidad'] ?? [];
        $precios     = $_POST['precioUnitario'] ?? [];

        if ($nombre === '') {
            $this->redirConError('Debe indicar el nombre del cliente.');
        }

        $detalle = [];
        $total   = 0;

        if (is_array($idsProducto)) {
            $n = count($idsProducto);
            for ($i = 0; $i < $n; $i++) {
                $idProd = (int)($idsProducto[$i] ?? 0);
                $cant   = isset($cantidades[$i]) ? floatval(str_replace(',', '.', $cantidades[$i])) : 0;
                $precio = isset($precios[$i]) ? floatval(str_replace(',', '.', $precios[$i])) : 0;

                if ($idProd > 0 && $cant > 0) {
                    $subtotal = $cant * $precio;
                    $detalle[] = [
                        'idProducto'    => $idProd,
                        'cantidad'      => $cant,
                        'precioUnitario' => $precio,
                        'subtotal'      => $subtotal,
                    ];
                    $total += $subtotal;
                }
            }
        }

        if (empty($detalle)) {
            $this->redirConError('El pedido debe contener al menos un producto con cantidad mayor a cero.');
        }

        $cliente = [
            'idCliente' => $idCliente,
            'nombre'    => $nombre,
            'email'     => $email,
            'telefono'  => $telefono,
            'calle'     => $calle,
            'altura'    => $alturaInput !== '' ? (int)$alturaInput : 0,
            'provincia' => 1,
            'localidad' => 1,
        ];

        $fechaBase      = $fechaPedido ?: date('Y-m-d');
        $fechaDateTime  = $fechaBase . ' 00:00:00';

        $pedido = [
            'fechaPedido'   => $fechaDateTime,
            'observaciones' => $observaciones,
            'total'         => $total,
        ];

        $resultado = $this->modelo->actualizarPedidoCompleto($idPedidoVenta, $cliente, $pedido, $detalle);

        if ($resultado['ok']) {
            $this->redirConOk('Pedido actualizado correctamente.', $idPedidoVenta);
        } else {
            $this->redirConError('Ocurrió un error al actualizar el pedido: ' . $resultado['error']);
        }
    }

        public function cambiarEstadoPedido()
    {
        $idPedidoVenta = (int)($_POST['idPedidoVenta'] ?? 0);
        $estadoActual  = (int)($_POST['estadoActual'] ?? 0);
        $nuevoEstado   = (int)($_POST['nuevoEstado'] ?? 0);

        if ($idPedidoVenta <= 0 || $nuevoEstado <= 0) {
            $this->redirConError('Datos de pedido o estado inválidos.');
        }

        // Transiciones válidas:
        // 70 (Generado)   -> 80 (Confirmado), 60 (Cancelado)
        // 80 (Confirmado) -> 90 (Preparado),  60 (Cancelado)
        // 90 (Preparado)  -> 100 (Entregado), 60 (Cancelado)
        $transiciones = [
            70 => [80, 60],
            80 => [90, 60],
            90 => [100, 60],
        ];

        if (!isset($transiciones[$estadoActual]) || !in_array($nuevoEstado, $transiciones[$estadoActual])) {
            $this->redirConError('Transición de estado no válida para este pedido.');
        }

        // Si pasamos de CONFIRMADO (80) a PREPARADO (90),
        // además de cambiar el estado, descontamos las materias primas.
        if ($estadoActual === 80 && $nuevoEstado === 90) {
            $ok = $this->modelo->prepararPedidoConDescuentoStock($idPedidoVenta);
            if (!$ok) {
                $this->redirConError('No es posible preparar el pedido: falta stock de materia prima o se produjo un error al descontar el stock.');
            }
        } else {
            // Resto de transiciones: solo cambio de estado
            $ok = $this->modelo->actualizarEstadoPedido($idPedidoVenta, $nuevoEstado);
            if (!$ok) {
                $this->redirConError('No se pudo actualizar el estado del pedido.');
            }
        }

        $this->redirConOk('Estado del pedido actualizado correctamente.', $idPedidoVenta);
    }


    private function redirConOk(string $mensaje, int $idPedidoVenta)
    {
        $msg = urlencode($mensaje);
        header("Location: ../vista/index.php?msg={$msg}&tipo=success&id={$idPedidoVenta}");
        exit();
    }

    private function redirConError(string $mensaje)
    {
        $msg = urlencode($mensaje);
        header("Location: ../vista/index.php?msg={$msg}&tipo=error");
        exit();
    }

    /**
     * API JSON: resumen de pedidos por estado.
     */
    public function apiResumenPedidosPorEstado()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Rango por defecto: últimos 30 días
        $hoy   = date('Y-m-d');
        $hace30 = date('Y-m-d', strtotime('-30 days'));

        $desde = $_GET['desde'] ?? $hace30;
        $hasta = $_GET['hasta'] ?? $hoy;

        $desdeCompleto = $desde . ' 00:00:00';
        $hastaCompleto = $hasta . ' 23:59:59';

        $data = $this->modelo->resumenPedidosPorEstado($desdeCompleto, $hastaCompleto);
        echo json_encode($data);
    }

    /**
     * API JSON: cantidad de pedidos por día.
     */
    public function apiResumenPedidosPorDia()
    {
        header('Content-Type: application/json; charset=utf-8');

        $hoy   = date('Y-m-d');
        $hace30 = date('Y-m-d', strtotime('-30 days'));

        $desde = $_GET['desde'] ?? $hace30;
        $hasta = $_GET['hasta'] ?? $hoy;

        $desdeCompleto = $desde . ' 00:00:00';
        $hastaCompleto = $hasta . ' 23:59:59';

        $data = $this->modelo->resumenPedidosPorDia($desdeCompleto, $hastaCompleto);
        echo json_encode($data);
    }

    /**
     * API JSON: facturación (total) por día.
     */
    public function apiResumenFacturacionPorDia()
    {
        header('Content-Type: application/json; charset=utf-8');

        $hoy   = date('Y-m-d');
        $hace30 = date('Y-m-d', strtotime('-30 days'));

        $desde = $_GET['desde'] ?? $hace30;
        $hasta = $_GET['hasta'] ?? $hoy;

        $desdeCompleto = $desde . ' 00:00:00';
        $hastaCompleto = $hasta . ' 23:59:59';

        $data = $this->modelo->resumenFacturacionPorDia($desdeCompleto, $hastaCompleto);
        echo json_encode($data);
    }

        /**
     * Devuelve un listado de pedidos filtrado por fechas y estado (para reportes).
     * $desde y $hasta en formato 'Y-m-d' (solo fecha).
     */
    public function obtenerPedidosFiltrados(?string $desde, ?string $hasta, ?int $estado = null)
    {
        // Rango por defecto: últimos 30 días
        $hoy    = date('Y-m-d');
        $hace30 = date('Y-m-d', strtotime('-30 days'));

        if (!$desde) {
            $desde = $hace30;
        }
        if (!$hasta) {
            $hasta = $hoy;
        }

        $desdeCompleto = $desde . ' 00:00:00';
        $hastaCompleto = $hasta . ' 23:59:59';

        return $this->modelo->listarPedidosFiltrados($desdeCompleto, $hastaCompleto, $estado);
    }

        /**
     * Devuelve el resumen de stock de MP para un pedido (usa modelo->evaluarStockPedido).
     */
    public function obtenerResumenStockPedido(int $idPedidoVenta): array
    {
        return $this->modelo->evaluarStockPedido($idPedidoVenta);
    }


}

/**
 * Router POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $ctrl = new controladorPedidos();

    switch ($_POST['accion']) {
        case 'crearPedido':
            $ctrl->crearPedido();
            break;
        case 'actualizarPedido':
            $ctrl->actualizarPedido();
            break;
        case 'cambiarEstado':
            $ctrl->cambiarEstadoPedido();
            break;
    }
}

/**
 * Router GET para AJAX (búsqueda de clientes)
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion'])) {
    $ctrl = new controladorPedidos();

    switch ($_GET['accion']) {
        case 'buscarClientes':
            $ctrl->buscarClientes();
            break;

        case 'resumenEstados':
            $ctrl->apiResumenPedidosPorEstado();
            break;

        case 'resumenPorDia':
            $ctrl->apiResumenPedidosPorDia();
            break;

        case 'resumenFacturacion':
            $ctrl->apiResumenFacturacionPorDia();
            break;
    }
}
