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
        $sql = "SELECT
                c.id_cliente,
                c.nombre,
                c.email,
                c.telefono,
                c.calle,
                c.altura,
                c.provincia,
                c.localidad,
                c.estado,
                p.provincia AS provincia_nombre,
                l.localidad AS localidad_nombre
            FROM clientes c
            LEFT JOIN provincias p ON p.id_provincia = c.provincia
            LEFT JOIN localidades l ON l.id_localidad = c.localidad
            WHERE c.id_cliente = :id
            LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idCliente]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }


    public function actualizar(array $c): bool
    {
        $sql = "UPDATE clientes
            SET nombre     = :nombre,
                email      = :email,
                telefono   = :telefono,
                calle      = :calle,
                altura     = :altura,
                provincia  = :provincia,
                localidad  = :localidad
            WHERE id_cliente = :idCliente";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $c['nombre'],
            ':email'     => $c['email'],
            ':telefono'  => $c['telefono'],
            ':calle'     => $c['calle'],
            ':altura'    => (int)$c['altura'],
            ':provincia' => (int)$c['provincia'],
            ':localidad' => (int)$c['localidad'],
            ':idCliente' => (int)$c['idCliente'],
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

    public function listarProvincias(): array
    {
        $sql = "SELECT id_provincia, provincia
            FROM provincias
            ORDER BY provincia";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarLocalidadesPorProvincia(int $idProvincia): array
    {
        $sql = "SELECT id_localidad, id_provincia, localidad
            FROM localidades
            WHERE id_provincia = :id_provincia
            ORDER BY localidad";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_provincia' => $idProvincia]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function actualizarCompleto(array $c): bool
    {
        $sql = "UPDATE clientes
            SET nombre    = :nombre,
                email     = :email,
                telefono  = :telefono,
                calle     = :calle,
                altura    = :altura,
                provincia = :provincia,
                localidad = :localidad
            WHERE id_cliente = :idCliente";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $c['nombre'],
            ':email'     => $c['email'],
            ':telefono'  => $c['telefono'],
            ':calle'     => $c['calle'],
            ':altura'    => (int)$c['altura'],
            ':provincia' => (int)$c['provincia'],
            ':localidad' => (int)$c['localidad'],
            ':idCliente' => (int)$c['idCliente'],
        ]);
    }

    public function validarLocalidadPerteneceAProvincia(int $idProvincia, int $idLocalidad): bool
    {
        $sql = "SELECT 1
            FROM localidades
            WHERE id_localidad = :idLocalidad AND id_provincia = :idProvincia
            LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idLocalidad' => $idLocalidad,
            ':idProvincia' => $idProvincia
        ]);
        return (bool)$stmt->fetchColumn();
    }
}
