<?php
use PHPUnit\Framework\TestCase;
use Frenet\Config;
use Frenet\Services\Shipments;
use Frenet\Exceptions\ShipmentException;
use Frenet\Http\Client;

class ShipmentsTest extends TestCase
{
    private $shipments;
    private $shipmentId;

    protected function setUp(): void
    {
        $config = new Config('apiKey', 'partnerToken', 'sandbox');
        $client = new Client($config->getBaseUri(), $config->getApiKey(), $config->getPartnerToken());
        $this->shipments = new Shipments($client);
        $this->shipmentId = '';
    }

    public function testCreateShipmentAndGetId()
    {
        $params = [
            "trackingNotificationUrl" => "http://example.com/tracking",
            "statusNotificationUrl" => "http://example.com/status",
            "branchCode" => "BR001",
            "order" => [
                "id" => "ORD123",
                "value" => 200.00,
                "created" => "2024-08-12T14:04:09.120Z",
                "useFrenetRegistration" => true,
                "items" => [
                    [
                        "orderId" => "ORD123",
                        "itemId" => "ITEM123",
                        "productId" => "PROD123",
                        "productOptions" => "Option1",
                        "productType" => "TypeA",
                        "weight" => 2.5,
                        "length" => 20,
                        "height" => 10,
                        "width" => 15,
                        "quantity" => 1,
                        "price" => 100.00,
                        "isFragile" => true,
                        "productName" => "Product Name",
                        "sku" => "SKU123",
                        "category" => "Category1"
                    ]
                ],
                "from" => [
                    "email" => "sender@example.com",
                    "name" => "Sender Name",
                    "phone" => "111-111-1111",
                    "cellphone" => "111-111-1112",
                    "document" => "123456789",
                    "ie" => "12345678",
                    "address" => [
                        "zipCode" => "01001-000",
                        "city" => "São Paulo",
                        "street" => "Rua A",
                        "addressNumber" => "100",
                        "addressComplement" => "Apt 1",
                        "addressQuarter" => "Centro",
                        "addressState" => "SP",
                        "postalBoxCode" => "",
                        "country" => "BR"
                    ]
                ],
                "to" => [
                    "email" => "recipient@example.com",
                    "name" => "Recipient Name",
                    "phone" => "222-222-2222",
                    "cellphone" => "222-222-2223",
                    "document" => "987654321",
                    "ie" => "87654321",
                    "address" => [
                        "zipCode" => "20010-000",
                        "city" => "Rio de Janeiro",
                        "street" => "Rua B",
                        "addressNumber" => "200",
                        "addressComplement" => "Apt 2",
                        "addressQuarter" => "Centro",
                        "addressState" => "RJ",
                        "postalBoxCode" => "",
                        "country" => "BR"
                    ]
                ],
                "invoice" => [
                    "value" => 200.00,
                    "number" => "INV123",
                    "series" => "S1",
                    "key" => "KEY123",
                    "date" => "2024-08-12T14:04:09.120Z",
                    "cfop" => "5102"
                ]
            ],
            "volumes" => [
                "volumeId" => 1,
                "weight" => 2.5,
                "length" => 20,
                "height" => 10,
                "width" => 15,
                "price" => 100.00,
                "declaredValue" => 100.00,
                "orderItemsId" => ["ITEM123"]
            ],
            "quotation" => [
                "shippingServiceCode" => "03298",
                "shippingServiceName" => "PAC",
                "platformShippingPrice" => 20.00,
                "deliveryTime" => 3,
                "carrier" => "Correios",
                "carrierCode" => "COR",
                "shippingPrice" => 15.00,
                "shippingCompetitorPrice" => 18.00,
                "services" => [
                    "declaredValue" => false,
                    "receiptNotification" => false,
                    "ownHand" => false
                ]
            ],
            "shipmentStatus" => 1,
            "settings" => [
                "channel" => "Frenet"
            ]
        ];

        $response = $this->shipments->createShipmentOneClick($params);
        // Verifica se o ID do shipment foi retornado
        $this->assertIsArray($response);
        $this->assertArrayHasKey('shipmentId', $response);

        echo "\nNew shipment: ".$response['shipmentId']."\n";

        return $response['shipmentId'];
    }

    /**
     * @depends testCreateShipmentAndGetId
     */
    public function testGetShipmentById($shipmentId)
    {
        $this->assertNotEmpty($shipmentId, 'Shipment ID não deve estar vazio.');
        echo "\n\nGetting $shipmentId...\n";
        $response = $this->shipments->getShipmentById($shipmentId);
        print_r($response);

        // Verifica se o shipment foi recuperado corretamente
        $this->assertIsArray($response);
        $this->assertArrayHasKey('shipmentId', $response);
        $this->assertEquals($shipmentId, $response['shipmentId']);

        return $shipmentId;
    }

    /**
     * @depends testCreateShipmentAndGetId
     */
    public function testCancelShipment($shipmentId)
    {
        $this->assertNotEmpty($shipmentId, 'Shipment ID não deve estar vazio.');
        echo "\n\nCanceling $shipmentId...\n";
        $response = $this->shipments->cancelShipment($shipmentId);
        print_r($response);
        // Verifica se o cancelamento foi bem-sucedido
        $this->assertIsArray($response);
        $this->assertArrayHasKey('canceled', $response);
        $this->assertTrue($response['canceled']);
    }
}