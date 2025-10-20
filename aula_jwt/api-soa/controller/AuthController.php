<?php
require_once './auth/JwtService.php';
require_once './core/Conexao.php';

class AuthController
{
    public function login(Request $request, Response $response)
    {
        $data = $request->bodyJson();
        
        if (!isset($data['email']) || !isset($data['senha'])) {
            return $response::json([
                'status' => 'error',
                'message' => 'Email e senha são obrigatórios'
            ], 400);
        }

        try {
            $pdo = Conexao::getInstance();
            $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$data['email']]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$usuario || md5($data['senha']) !== $usuario['senha']) {
                return $response::json([
                    'status' => 'error',
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $token = JwtService::gerarJWT($usuario['id'], $usuario['email']);

            $response::json([
                'status' => 'success',
                'token' => $token,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email']
                ]
            ]);

        } catch (\Exception $e) {
            $response::json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}