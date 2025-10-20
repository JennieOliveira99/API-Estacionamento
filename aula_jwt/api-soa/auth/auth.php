<?php
// Chave privada para assinar os tokens
define('CHAVE_PRIVADA', 'fatec2024');

/**
 * Classe responsável por gerar e validar JWT
 */
class JwtService {

    /**
     * Gera um token JWT para o usuário
     * @param int $id_usuario ID do usuário no banco
     * @param string $usuario Nome ou login do usuário
     * @return string Token JWT
     */
    public static function gerarJWT($id_usuario, $usuario){
        // 1- criar o header do token
        $header = json_encode([
            'alg' => 'HS256',  // algoritmo de assinatura
            'typ' => 'JWT'     // tipo do token
        ]);

        // 2- criar o payload (corpo do token) com informações do usuário
        $payload = json_encode([
            'id_usuario' => $id_usuario,
            'usuario' => $usuario,
            'iat' => time(),          // timestamp da criação
            'exp' => time() + 3600    // expira em 1 hora
        ]);

        // 3- converter header e payload para base64-url-safe
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        // 4- criar assinatura usando HMAC-SHA256
        $assinatura = hash_hmac('SHA256', "$base64Header.$base64Payload", CHAVE_PRIVADA, true);
        $base64Assinatura = self::base64UrlEncode($assinatura);

        // 5- retornar token final
        return "$base64Header.$base64Payload.$base64Assinatura";
    }

    /**
     * Valida o token JWT recebido
     * @param string $token Token enviado pelo cliente
     * @return array|false Retorna payload decodificado ou false se inválido
     */
    public static function verificarJWT($token){
        $partes = explode('.', $token);
        if(count($partes) != 3) return false;

        list($base64Header, $base64Payload, $base64Assinatura) = $partes;

        // recalcula assinatura
        $assinaturaCheck = hash_hmac('SHA256', "$base64Header.$base64Payload", CHAVE_PRIVADA, true);
        $assinaturaCheck = self::base64UrlEncode($assinaturaCheck);

        if($assinaturaCheck !== $base64Assinatura) return false; // token adulterado

        // decodifica payload
        $payloadJson = base64_decode(str_replace(['-','_'], ['+','/'], $base64Payload));
        $payload = json_decode($payloadJson, true);

        // verifica expiração
        if($payload['exp'] < time()) return false;

        return $payload;
    }

    /**
     * Converte string para base64-url-safe
     * @param string $dados
     * @return string
     */
    private static function base64UrlEncode($dados){
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($dados));
    }
}

/**
 * Função helper para autenticar rotas
 * @param PDO $pdo
 * @return array|false Retorna usuário do banco ou false se não autenticado
 */
function autenticar($pdo){
    $headers = getallheaders();
    if(!isset($headers['Authorization'])) return false;

    $authHeader = $headers['Authorization'];
    if(strpos($authHeader, 'Bearer ') !== 0) return false;

    $token = substr($authHeader, 7);
    $payload = JwtService::verificarJWT($token);
    if(!$payload) return false;

    // busca usuário real no banco
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
    $stmt->execute([$payload['id_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario ?: false;
}
