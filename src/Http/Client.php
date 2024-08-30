<?php
namespace Frenet\Http;

use Frenet\Exceptions\ShipmentException;
use GuzzleHttp\Client as GuzzleClient;
use Frenet\Exceptions\FrenetException;

class Client {
    private $client;
    private $apiKey;
    private $partnerToken;

    public function __construct($baseUri, $apiKey, $partnerToken) {
        $this->client = new GuzzleClient(['base_uri' => $baseUri]);
        $this->apiKey = $apiKey;
        $this->partnerToken = $partnerToken;
    }

    public function request($method, $endpoint, $params = []) {
        try {
            array_walk_recursive($params, function (&$item) {
                if (is_string($item)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'auto');
                }
            });
            $response = $this->client->request($method, $endpoint, [
                'headers' => [
                    'token' => $this->apiKey,
                    'x-partner-token' => $this->partnerToken,
                    'Content-Type' => 'application/json',
//                    'accept' => 'text/plain'
                ],
                'json' => $params
            ]);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            // Captura o código de status HTTP
            $statusCode = $e->getResponse()->getStatusCode();

            // Captura o corpo da resposta, que geralmente é onde a mensagem de erro está
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);

            // Verifica se a resposta contém uma mensagem de erro
            $errorMessage = $errorData['message'] ?? 'An error occurred';
            $details = $this->formatErrors($errorData['details'] ?? []);

            throw new ShipmentException("Error ({$statusCode}): {$errorMessage}. Details: {$details}");

        } catch (\Exception $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            // Captura o corpo da resposta, que geralmente é onde a mensagem de erro está
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);

            // Verifica se a resposta contém uma mensagem de erro
            $errorMessage = $errorData['message'] ?? 'An error occurred';
            $details = $this->formatErrors($errorData['details'] ?? []);

            throw new FrenetException("{$errorMessage} Details: {$details}");
//            throw new FrenetException($e->getMessage());
        }
    }
    protected function formatErrors(array $errors) {
        $formatted = [];
        foreach ($errors as $error) {
            $code = $error['code'] ?? 'unknown';
            $message = $error['message'] ?? 'No message provided';
            $formatted[] = "({$code}) - {$message}";
        }
        return implode('; ', $formatted);
    }
}