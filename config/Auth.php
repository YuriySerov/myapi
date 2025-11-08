<?php
class Auth
{
    private $pdo;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    private function getApiKeyFromHeader()
    {
        $headers = getallheaders();
        if (isset($headers['X-API-Key'])) return $headers['X-API-Key'];
        if (isset($headers['x-api-key'])) return $headers['x-api-key'];
        return null;
    }


    public function requireApiKey()
    {
        $key = $this->getApiKeyFromHeader();
        if (!$key) {
            http_response_code(401);
            echo json_encode(['error' => 'API key required']);
            exit;
        }


        $stmt = $this->pdo->prepare('SELECT api_key_hash, is_active FROM api_keys WHERE is_active = 1');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach ($rows as $r) {
            if (password_verify($key, $r['api_key_hash'])) {
                return true;
            }
        }


        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
}
