<?php

use PHPUnit\Framework\TestCase;
use Frenet\Config;
use Frenet\Services\Carriers;
use Frenet\Exceptions\CarrierException;
use Frenet\Http\Client;

class CarriersTest extends TestCase
{
    private $carriers;

    protected function setUp(): void
    {
        $config = new Config('apiKey', 'partnerToken', 'sandbox');
        $client = new Client($config->getConfigurationUri(), $config->getApiKey(), $config->getPartnerToken());
        $this->carriers = new Carriers($client);
    }

    public function testGetServicesSuccess()
    {
        $services = $this->carriers->getServices();
        $this->assertIsArray($services);
        $this->assertGreaterThanOrEqual(1, count($services));
        $this->assertInstanceOf(\Frenet\Services\Carrier::class, $services[0]);
    }

}