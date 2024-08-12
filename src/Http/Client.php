<?php
namespace Frenet\Http;

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
        } catch (\Exception $e) {
            throw new FrenetException($e->getMessage());
        }
    }
}