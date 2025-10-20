<?php
require_once './model/RegistroEstacionamento.php';
require_once './auth/JwtService.php';

class EstacionamentoController
{
    private $registroModel;

    public function __construct()
    {
        $this->registroModel = new RegistroEstacionamento();
    }

    private function verificarToken($request)
    {
        $token = str_replace('Bearer ', '', $request::header('Authorization'));
        return JwtService::verificarJWT($token);
    }

    public function entrada(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        $id_veiculo = $params[0];

        try {
            // Verificar se veículo existe
            if (!$this->registroModel->veiculoExiste($id_veiculo)) {
                return $response::json(['status' => 'error', 'message' => 'Veículo não encontrado'], 404);
            }

            // Verificar se já existe registro aberto para este veículo
            if ($this->registroModel->veiculoEstaNoEstacionamento($id_veiculo)) {
                return $response::json(['status' => 'error', 'message' => 'Veículo já está no estacionamento'], 400);
            }

            // Registrar entrada
            $id_registro = $this->registroModel->registrarEntrada($id_veiculo);

            $response::json([
                'status' => 'success',
                'message' => 'Entrada registrada com sucesso',
                'id' => $id_registro
            ], 201);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao registrar entrada'], 500);
        }
    }

    public function saida(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        $id_veiculo = $params[0];

        try {
            // Buscar registro aberto
            $registro = $this->registroModel->findRegistroAberto($id_veiculo);

            if (!$registro) {
                return $response::json(['status' => 'error', 'message' => 'Nenhuma entrada registrada para este veículo'], 404);
            }

            // Calcular tempo e valor
            $hora_entrada = new DateTime($registro['hora_entrada']);
            $hora_saida = new DateTime();
            $diff = $hora_entrada->diff($hora_saida);
            
            $total_minutos = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Calcular valor (R$ 2,00 a cada 15 minutos)
            $fracoes = ceil($total_minutos / 15);
            $valor_pago = $fracoes * 2.00;

            // Registrar saída
            $rowCount = $this->registroModel->registrarSaida($id_veiculo, $total_minutos, $valor_pago);

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Erro ao registrar saída'], 500);
            }

            $response::json([
                'status' => 'success',
                'message' => 'Saída registrada com sucesso',
                'data' => [
                    'tempo_total_minutos' => $total_minutos,
                    'valor_pago' => $valor_pago,
                    'fracoes_15min' => $fracoes
                ]
            ]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao registrar saída'], 500);
        }
    }

    public function historico(Request $request, Response $response)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $historico = $this->registroModel->getHistorico();
            $response::json(['status' => 'success', 'data' => $historico]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao buscar histórico'], 500);
        }
    }

    public function veiculosAtivos(Request $request, Response $response)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $ativos = $this->registroModel->getVeiculosAtivos();
            $response::json(['status' => 'success', 'data' => $ativos]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao buscar veículos ativos'], 500);
        }
    }
}