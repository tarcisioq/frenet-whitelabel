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
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorBody = $response->getBody()->getContents();
                $errorData = json_decode($errorBody, true);
                $errorMessage = $errorData['message'] ?? 'An error occurred';
                $details = $this->formatErrors($errorData['details'] ?? []);
            } else {
                // Caso não haja resposta, você pode definir um código padrão ou tratar de forma específica
                $statusCode = null;
                $errorMessage = $e->getMessage();
                $details = '';
            }

            throw new ShipmentException("Error ({$statusCode}): {$errorMessage}. Details: {$details}");
        } catch (\Exception $e) {
            if (method_exists($e, 'getResponse') && $e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $errorBody = $response->getBody()->getContents();
                $errorData = json_decode($errorBody, true);
                $details = $this->formatErrors($errorData['details'] ?? []);
                if (strpos($details, "acesso negado para o cliente")) {
                    $details = mb_convert_encoding("Acesso negado. Credenciais inválidas.", 'UTF-8', 'ISO-8859-1');
                }
                $errorMessage = $errorData['message'] ?? $details;
            } else {
                $statusCode = null;
                $errorMessage = $e->getMessage();
                $details = '';
            }

            throw new FrenetException($errorMessage);
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