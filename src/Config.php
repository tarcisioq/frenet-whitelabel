<?php
namespace Frenet;

class Config {
    private $apiKey;
    private $partnerToken;
    private $environment;

    public function __construct($apiKey, $partnerToken, $environment) {
        $this->apiKey = $apiKey;
        $this->partnerToken = $partnerToken;
        $this->environment = $environment;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getPartnerToken() {
        return $this->partnerToken;
    }

    public function getConfigurationUri() {
        return $this->environment === 'sandbox' ?
            'https://whitelabel-configuration-hml.frenet.dev/v1/' :
            'https://whitelabel-configuration-hml.frenet.dev/v1/';
    }

    public function getBaseUri() {
        return $this->environment === 'sandbox' ?
            'https://whitelabel-hml.frenet.dev/v1/' :
            'https://production-url.frenet.dev/v1/';
    }
}