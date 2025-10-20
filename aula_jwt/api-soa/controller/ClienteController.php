<?php
require_once './model/Cliente.php';
require_once './auth/JwtService.php';

class ClienteController
{
    private $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
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
            $cliente = $this->clienteModel->find($params[0]);

            if (!$cliente) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'data' => $cliente]);

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
            $clientes = $this->clienteModel->findAll();
            $response::json(['status' => 'success', 'data' => $clientes]);

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

        if (!isset($data['nome']) || empty(trim($data['nome']))) {
            return $response::json(['status' => 'error', 'message' => 'Nome é obrigatório'], 400);
        }

        try {
            $id = $this->clienteModel->create($data['nome']);

            $response::json([
                'status' => 'success',
                'message' => 'Cliente criado com sucesso',
                'id' => $id
            ], 201);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao criar cliente'], 500);
        }
    }

    public function atualizar(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        $data = $request->bodyJson();
        $id = $params[0];

        if (!isset($data['nome']) || empty(trim($data['nome']))) {
            return $response::json(['status' => 'error', 'message' => 'Nome é obrigatório'], 400);
        }

        try {
            $rowCount = $this->clienteModel->update($id, $data['nome']);

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'message' => 'Cliente atualizado com sucesso']);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao atualizar cliente'], 500);
        }
    }

    public function deletar(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $rowCount = $this->clienteModel->delete($params[0]);

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'message' => 'Cliente deletado permanentemente']);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao deletar cliente'], 500);
        }
    }

    public function marcarInativo(Request $request, Response $response, $params)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $rowCount = $this->clienteModel->marcarInativo($params[0]);

            if ($rowCount === 0) {
                return $response::json(['status' => 'error', 'message' => 'Cliente não encontrado'], 404);
            }

            $response::json(['status' => 'success', 'message' => 'Cliente marcado como inativo']);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro ao marcar cliente como inativo'], 500);
        }
    }

    public function listarAtivos(Request $request, Response $response)
    {
        if (!$this->verificarToken($request)) {
            return $response::json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }

        try {
            $clientes = $this->clienteModel->findAtivos();
            $response::json(['status' => 'success', 'data' => $clientes]);

        } catch (\Exception $e) {
            $response::json(['status' => 'error', 'message' => 'Erro interno'], 500);
        }
    }
}