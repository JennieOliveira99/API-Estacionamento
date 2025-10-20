<?php
// Passo 1 - definir constante chave privada (não trocar depois que tokens forem gerados)
define('CHAVE_PRIVADA', 'fatec2024');

class JwtService {
    // Gerar token JWT
    public static function gerarJWT($id_usuario, $usuario) {
        // 1 - Header
        $header = json_encode(['alg'=>'HS256','typ'=>'JWT']);

        // 2 - Payload
        $payload = json_encode([
            'id_usuario'=>$id_usuario,
            'usuario'=>$usuario,
            'iat'=>time(),
            'exp'=>time() + 3600
        ]);

        // 3 - Base64 URL-safe
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        // 4 - Assinatura HMAC-SHA256
        $assinatura = hash_hmac('SHA256', "$base64Header.$base64Payload", CHAVE_PRIVADA, true);
        $base64Assinatura = self::base64UrlEncode($assinatura);

        // 5 - Retorna token
        return "$base64Header.$base64Payload.$base64Assinatura";
    }

    // Validar token JWT
    public static function verificarJWT($token) {
        $partes = explode('.', $token);
        if(count($partes) !== 3) return false;

        [$base64Header, $base64Payload, $base64Assinatura] = $partes;

        // Recria assinatura
        $assinaturaCheck = hash_hmac('SHA256', "$base64Header.$base64Payload", CHAVE_PRIVADA, true);
        $assinaturaCheck = self::base64UrlEncode($assinaturaCheck);

        if($assinaturaCheck !== $base64Assinatura) return false;

        // Decodifica payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);
        if($payload['exp'] < time()) return false;

        return $payload; // retorna dados do usuário
    }

    // Funções auxiliares
    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) $data .= str_repeat('=', 4 - $remainder);
        return base64_decode(str_replace(['-','_'], ['+','/'], $data));
    }
}
