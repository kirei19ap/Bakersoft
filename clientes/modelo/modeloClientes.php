<?php

class modeloClientes
{
    private $pdo;

    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->pdo = $con->conexion();
    }

    public function listarActivos()
    {
        $sql = "SELECT id_cliente, nombre, email, telefono, calle, altura, estado
                FROM clientes
                WHERE estado = 'Activo'
                ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listar()
    {
        $sql = "SELECT id_cliente, nombre, email, telefono, calle, altura, estado
            FROM clientes
            ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function obtenerPorId(int $idCliente)
    {
        $sql = "SELECT id_cliente, nombre, email, telefono, calle, altura, provincia, localidad, estado
                FROM clientes
                WHERE id_cliente = :id
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function actualizarBasico(array $c): bool
    {
        $sql = "UPDATE clientes
                SET nombre   = :nombre,
                    email    = :email,
                    telefono = :telefono,
                    calle    = :calle,
                    altura   = :altura
                WHERE id_cliente = :idCliente";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $c['nombre'],
            ':email'     => $c['email'],
            ':telefono'  => $c['telefono'],
            ':calle'     => $c['calle'],
            ':altura'    => $c['altura'],
            ':idCliente' => $c['idCliente'],
        ]);
    }

    public function bajaLogica(int $idCliente, string $estado = 'Eliminado'): bool
    {
        // Nota: usamos un string porque tu campo estado es varchar(20)
        $sql = "UPDATE clientes
                SET estado = :estado
                WHERE id_cliente = :idCliente";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':estado'    => $estado,
            ':idCliente' => $idCliente
        ]);
    }
}
