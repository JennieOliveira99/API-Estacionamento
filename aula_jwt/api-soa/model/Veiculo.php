<?php
require_once './core/Conexao.php';

class Veiculo
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstance();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT v.*, c.nome as cliente_nome 
            FROM veiculos v 
            LEFT JOIN clientes c ON v.id_cliente = c.id 
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("
            SELECT v.*, c.nome as cliente_nome 
            FROM veiculos v 
            LEFT JOIN clientes c ON v.id_cliente = c.id 
            ORDER BY v.criado_em DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByCliente($id_cliente)
    {
        $stmt = $this->pdo->prepare("
            SELECT v.*, c.nome as cliente_nome 
            FROM veiculos v 
            LEFT JOIN clientes c ON v.id_cliente = c.id 
            WHERE v.id_cliente = ? 
            ORDER BY v.criado_em DESC
        ");
        $stmt->execute([$id_cliente]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($id_cliente, $placa, $modelo, $cor)
    {
        $stmt = $this->pdo->prepare("INSERT INTO veiculos (id_cliente, placa, modelo, cor) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_cliente, trim($placa), trim($modelo), trim($cor)]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $id_cliente, $placa, $modelo, $cor)
    {
        $stmt = $this->pdo->prepare("UPDATE veiculos SET id_cliente = ?, placa = ?, modelo = ?, cor = ? WHERE id = ?");
        $stmt->execute([$id_cliente, trim($placa), trim($modelo), trim($cor), $id]);
        return $stmt->rowCount();
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM veiculos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function clienteExiste($id_cliente)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM clientes WHERE id = ? AND inativo = 0");
        $stmt->execute([$id_cliente]);
        return (bool) $stmt->fetch();
    }
}