<?php
namespace Frenet;

use Frenet\Http\Client;
use Frenet\Services\Quotes;
use Frenet\Services\Shipments;

class FrenetSDK {
    private $config;
    private $client;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->client = new Client($config->getBaseUri(), $config->getApiKey(), $config->getPartnerToken());
    }

    public function quotes() {
        return new Quotes($this->client);
    }

    public function shipments() {
        return new Shipments($this->client);
    }
}