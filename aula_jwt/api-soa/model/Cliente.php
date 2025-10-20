<?php
require_once './core/Conexao.php';

class Cliente
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstance();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM clientes ORDER BY criado_em DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findAtivos()
    {
        $stmt = $this->pdo->query("SELECT * FROM clientes WHERE inativo = 0 ORDER BY criado_em DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($nome)
    {
        $stmt = $this->pdo->prepare("INSERT INTO clientes (nome) VALUES (?)");
        $stmt->execute([trim($nome)]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $nome)
    {
        $stmt = $this->pdo->prepare("UPDATE clientes SET nome = ? WHERE id = ?");
        $stmt->execute([trim($nome), $id]);
        return $stmt->rowCount();
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function marcarInativo($id)
    {
        $stmt = $this->pdo->prepare("UPDATE clientes SET inativo = 1 WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}