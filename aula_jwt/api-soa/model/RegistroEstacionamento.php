<?php
require_once './core/Conexao.php';

class RegistroEstacionamento
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstance();
    }

    public function registrarEntrada($id_veiculo)
    {
        $stmt = $this->pdo->prepare("INSERT INTO registros_estacionamento (id_veiculo, hora_entrada) VALUES (?, NOW())");
        $stmt->execute([$id_veiculo]);
        return $this->pdo->lastInsertId();
    }

    public function registrarSaida($id_veiculo, $tempo_total, $valor_pago)
    {
        $stmt = $this->pdo->prepare("
            UPDATE registros_estacionamento 
            SET hora_saida = NOW(), status = 'fechado', tempo_total_minutos = ?, valor_pago = ? 
            WHERE id_veiculo = ? AND status = 'aberto'
        ");
        $stmt->execute([$tempo_total, $valor_pago, $id_veiculo]);
        return $stmt->rowCount();
    }

    public function findRegistroAberto($id_veiculo)
    {
        $stmt = $this->pdo->prepare("SELECT id, hora_entrada FROM registros_estacionamento WHERE id_veiculo = ? AND status = 'aberto'");
        $stmt->execute([$id_veiculo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function veiculoEstaNoEstacionamento($id_veiculo)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM registros_estacionamento WHERE id_veiculo = ? AND status = 'aberto'");
        $stmt->execute([$id_veiculo]);
        return (bool) $stmt->fetch();
    }

    public function getHistorico()
    {
        $stmt = $this->pdo->query("
            SELECT re.*, v.placa, v.modelo, v.cor, c.nome as cliente_nome
            FROM registros_estacionamento re
            LEFT JOIN veiculos v ON re.id_veiculo = v.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE re.status = 'fechado'
            ORDER BY re.hora_saida DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVeiculosAtivos()
    {
        $stmt = $this->pdo->query("
            SELECT re.*, v.placa, v.modelo, v.cor, c.nome as cliente_nome
            FROM registros_estacionamento re
            LEFT JOIN veiculos v ON re.id_veiculo = v.id
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE re.status = 'aberto'
            ORDER BY re.hora_entrada DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function veiculoExiste($id_veiculo)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM veiculos WHERE id = ?");
        $stmt->execute([$id_veiculo]);
        return (bool) $stmt->fetch();
    }
}