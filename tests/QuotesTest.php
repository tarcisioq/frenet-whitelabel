<?php

use PHPUnit\Framework\TestCase;
use Frenet\Config;
use Frenet\FrenetSDK;
use Frenet\Services\Quotes;
use Frenet\Exceptions\ValidationException;
use Frenet\Exceptions\QuoteException;
use Frenet\Http\Client;

class QuotesTest extends TestCase
{
    private $quotes;

    protected function setUp(): void
    {
        $config = new Config('apikey', 'partnerToken', 'sandbox');
        $client = new Client($config->getBaseUri(), $config->getApiKey(), $config->getPartnerToken());
        $this->quotes = new Quotes($client);
    }

    public function testGetQuoteSuccess()
    {
        $params = [
            'senderZipCode' => '01001-000',
            'recipientZipCode' => '20010-000',
            'recipientCountry' => 'BR',
            'shipmentItemValue' => 100.00,
            'services' => [
                'declaredValue' => true,
                'receiptNotification' => true,
                'ownHand' => true
            ],
            'volumes' => [
                [
                    'weight' => 1.5,
                    'length' => 10,
                    'height' => 10,
                    'width' => 15,
                    'isFragile' => true
                ]
            ]
        ];

        $response = $this->quotes->getQuote($params);

        // Verifique se a cotação foi retornada corretamente
        $this->assertIsArray($response);
        $this->assertArrayHasKey('sessionId', $response);
        $this->assertArrayHasKey('quotations', $response);
        $this->assertNotEmpty($response['quotations']);
    }

    public function testGetQuoteMissingSenderZipCode()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The senderZipCode parameter is required.');

        $params = [
            'recipientZipCode' => '20010-000',
            'recipientCountry' => 'BR',
            'shipmentItemValue' => 100.00,
            'services' => [
                'declaredValue' => true,
                'receiptNotification' => true,
                'ownHand' => true
            ],
            'volumes' => [
                [
                    'weight' => 1.5,
                    'length' => 10,
                    'height' => 10,
                    'width' => 15,
                    'isFragile' => true
                ]
            ]
        ];

        $this->quotes->getQuote($params);
    }

    public function testGetQuoteInvalidShipmentItemValue()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The shipmentItemValue parameter must be numeric.');

        $params = [
            'senderZipCode' => '01001-000',
            'recipientZipCode' => '20010-000',
            'recipientCountry' => 'BR',
            'shipmentItemValue' => 'invalid',
            'services' => [
                'declaredValue' => true,
                'receiptNotification' => true,
                'ownHand' => true
            ],
            'volumes' => [
                [
                    'weight' => 1.5,
                    'length' => 10,
                    'height' => 10,
                    'width' => 15,
                    'isFragile' => true
                ]
            ]
        ];

        $this->quotes->getQuote($params);
    }

//    public function testGetQuoteNoQuotationsFound()
//    {
//        $this->expectException(QuoteException::class);
//        $this->expectExceptionMessage('No quotations found in the response.');
//
//        $params = [
//            'senderZipCode' => '01001-000',
//            'recipientZipCode' => '20010-000',
//            'recipientCountry' => 'BR',
//            'shipmentItemValue' => 100.00,
//            'services' => [
//                'declaredValue' => true,
//                'receiptNotification' => true,
//                'ownHand' => true
//            ],
//            'volumes' => [
//                [
//                    'weight' => 1.5,
//                    'length' => 10,
//                    'height' => 10,
//                    'width' => 15,
//                    'isFragile' => true
//                ]
//            ]
//        ];
//
//        $this->quotes->getQuote($params);
//    }
}