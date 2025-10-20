<?php
require_once './model/Veiculo.php';
require_once './auth/JwtService.php';

class VeiculoController
{
    private $veiculoModel;

    public function __construct()
    {
        $this->veiculoModel = new Veiculo();
    }

    private function verificarToken($request)
    {
        $token = str_replace('Bearer ', '', $request::header('Authorization'));
        return JwtService::verificarJWT($token);
    }

    public function find(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $veiculo = $this->veiculoModel->find($params[0]);

            if (!$veiculo) {
                return $response::json(['status' => 'error', 'message' => 'Veículo não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'data' => $veiculo]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }

    public function listar(Request $request, Response $response)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $veiculos = $this->veiculoModel->findAll();
            $response::json(['status' => 'success', 'data' => $veiculos]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }

    public function criar(Request $request, Response $response)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        $data = $request->bodyJson();

        $required = ['id_cliente', 'placa', 'modelo', 'cor'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return $response::json(['status' => 'error', 'message' => "Campo $field é obrigatório"], 400);
            }
        }

        try {
            // Verificar se cliente existe
            if (!$this->veiculoModel->clienteExiste($data['id_cliente'])) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado ou inativo'], 404);
            }

            $id = $this->veiculoModel->create(
                $data['id_cliente'],
                $data['placa'],
                $data['modelo'],
                $data['cor']
            );

            $response::json([
                'status' => 'success',
                'message' => 'Veículo criado com sucesso',
                'id' => $id
            ], 201);

        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $response::json(['status' => 'error', 'message' => 'Placa já cadastrada'], 400);
            } else {
                $response::json(['status' => 'error', 'message' => 'Erro ao criar veículo'], 500);
            }
        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao criar veículo'], 500);
        }
    }

    public function atualizar(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        $data = $request->bodyJson();
        $id = $params[0];

        $required = ['id_cliente', 'placa', 'modelo', 'cor'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return $response::json(['status' => 'error', 'message' => "Campo $field é obrigatório"], 400);
            }
        }

        try {
            // Verificar se cliente existe
            if (!$this->veiculoModel->clienteExiste($data['id_cliente'])) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado ou inativo'], 404);
            }

            $rowCount = $this->veiculoModel->update(
                $id,
                $data['id_cliente'],
                $data['placa'],
                $data['modelo'],
                $data['cor']
            );

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Veículo não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'message' => 'Veículo atualizado com sucesso']);

        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $response::json(['status' => 'error', 'message' => 'Placa já cadastrada'], 400);
            } else {
                $response::json(['status' => 'error', 'message' => 'Erro ao atualizar veículo'], 500);
            }
        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao atualizar veículo'], 500);
        }
    }

    public function deletar(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $rowCount = $this->veiculoModel->delete($params[0]);

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Veículo não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'message' => 'Veículo deletado com sucesso']);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao deletar veículo'], 500);
        }
    }

    public function listarPorCliente(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $veiculos = $this->veiculoModel->findByCliente($params[0]);
            $response::json(['status' => 'success', 'data' => $veiculos]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }
}