<?php
class controladorPedido
{
    private $modelo;
    public function __construct()
    {
        require_once("../modelo/modeloPedido.php");
        $this->modelo = new modeloPedido();
    }

    public function proveedoresTodos()
    {
        return ($this->modelo->traerProveedores() ? $this->modelo->traerProveedores() : false);
    }

    public function mostrarMPporProveedor($id_mp)
    {
        return ($this->modelo->traerMPbyProveed($id_mp) ? $this->modelo->traerMPbyProveed($id_mp) : false);
    }

    public function nroultimopedido()
    {
        $resultado = $this->modelo->traerNroUltimoPedido();
        return $resultado ? $resultado['idPedido'] : 0;
    }

    public function guardarPedido($idProveedor, $items)
    {
        $idPedido = $this->modelo->insertarPedido($idProveedor);
        if ($idPedido) {
            foreach ($items as $item) {
                $this->modelo->insertarDetallePedido($idPedido, $item['id'], $item['cantidad']);
            }
            return true;
        }
        return false;
    }

    public function traerPedidos()
    {
        return ($this->modelo->pedidosTodos() ? $this->modelo->pedidosTodos() : false);
    }

    public function traerDetallePedido($idPedido)
    {
        return ($this->modelo->detallePedido($idPedido) ? $this->modelo->detallePedido($idPedido) : false);
    }

    public function cancelarPedido($idPedido)
    {
        return ($this->modelo->cancelarPedido($idPedido) ? true : false);
    }

    public function traerProveedorporNombre($nombreProveedor)
    {
        return ($this->modelo->traerProveedorporNombre($nombreProveedor) ? $this->modelo->traerProveedorporNombre($nombreProveedor) : false);
    }

    public function recibirPedido($idPedido, $idUsuario)
    {
        return ($this->modelo->recibirPedido($idPedido, $idUsuario) ? true : false);
    }

        /**
     * Registra un cliente nuevo desde el formulario de pedido (AJAX).
     * No crea pedido, sólo cliente.
     */
    public function registrarClienteDesdePedido()
    {
        header('Content-Type: application/json; charset=utf-8');

        $nombre   = trim($_POST['clienteNombre']   ?? '');
        $telefono = trim($_POST['clienteTelefono'] ?? '');
        $calle    = trim($_POST['clienteCalle']    ?? '');
        $altura   = trim($_POST['clienteAltura']   ?? '');
        $email    = trim($_POST['clienteEmail']    ?? '');

        // Validaciones de negocio: nombre, dirección y teléfono obligatorios
        if ($nombre === '' || $telefono === '' || $calle === '' || $altura === '') {
            echo json_encode([
                'ok'    => false,
                'error' => 'Nombre, teléfono, calle y altura del cliente son obligatorios para registrarlo.'
            ]);
            return;
        }

        $cliente = [
            'nombre'    => $nombre,
            'email'     => $email,
            'telefono'  => $telefono,
            'calle'     => $calle,
            'altura'    => (int)$altura,
            'provincia' => 1,
            'localidad' => 1,
            'estado'    => 'Activo',
        ];

        $resultado = $this->modelo->crearClienteBasico($cliente);

        if ($resultado['ok']) {
            echo json_encode([
                'ok'        => true,
                'idCliente' => $resultado['idCliente'],
                'mensaje'   => 'Cliente registrado correctamente.'
            ]);
        } else {
            echo json_encode([
                'ok'    => false,
                'error' => 'Error al registrar el cliente: ' . $resultado['error']
            ]);
        }
    }

}
