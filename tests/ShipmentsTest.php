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
        $config = new Config('D248AC23R5AA3R477CR9953R35A168E8DCCC', '6985E2BDIDE95I4678IBB30I95AF2D3B1ADB', 'sandbox');
        $client = new Client($config->getBaseUri(), $config->getApiKey(), $config->getPartnerToken());
        $this->shipments = new Shipments($client);
        $this->shipmentId = '';
    }

    public function testCreateShipmentAndGetId()
    {
        $params =
            [
                "order" => [
                    "id" => "152378",
                    "value" => 549.74,
                    "created" => "2024-07-26 16:20:49",
                    "useFrenetRegistration" => false,
                    "items" => [
                        [
                            "orderId" => "152378",
                            "itemId" => "2968",
                            "productId" => "100",
                            "weight" => "11.000",
                            "length" => 2,
                            "height" => 3,
                            "width" => 5,
                            "quantity" => 1,
                            "price" => 123.2,
                            "productName" => "RINNAI/AQUECEDOR",
                            "sku" => "276811"
                        ],
                        [
                            "orderId" => "152378",
                            "itemId" => "2969",
                            "productId" => "103",
                            "weight" => "11.000",
                            "length" => 8,
                            "height" => 18,
                            "width" => 4,
                            "quantity" => 1,
                            "price" => 122.11,
                            "productName" => "Case Anti-impacto Fosco para Samsung S20 Ultra - Prote??o Elegante",
                            "sku" => "XXX0101"
                        ],
                        [
                            "orderId" => "152378",
                            "itemId" => "2970",
                            "productId" => "84",
                            "weight" => "0.600",
                            "length" => 15,
                            "height" => 7,
                            "width" => 1,
                            "quantity" => 1,
                            "price" => 169.9,
                            "productName" => "Adega vinhos simples 32 garrafas",
                            "sku" => "BSC12022"
                        ],
                        [
                            "orderId" => "152378",
                            "itemId" => "2971",
                            "productId" => "62",
                            "weight" => "0.400",
                            "length" => 11,
                            "height" => 11,
                            "width" => 15,
                            "quantity" => 1,
                            "price" => 13.5,
                            "productName" => "Aroma de kit para carro",
                            "sku" => "SKU0030001"
                        ],
                        [
                            "orderId" => "152378",
                            "itemId" => "2972",
                            "productId" => "50",
                            "weight" => "0.340",
                            "length" => 11,
                            "height" => 2,
                            "width" => 16,
                            "quantity" => 1,
                            "price" => 2.08,
                            "productName" => "Monitor 19 AOC - alterado",
                            "sku" => "MCNAJHJ1"
                        ]
                    ],
                    "from" => [
                        "email" => "tarcisio@iset.com.br",
                        "name" => "iSET - Loj? Virtu?l de pe?as",
                        "phone" => "(31) 9847-72252",
                        "document" => "08323617000150",
                        "address" => [
                            "zipCode" => "86020-110",
                            "city" => "Londrina",
                            "street" => "Rua Prefeito Hugo Cabral",
                            "addressNumber" => "620",
                            "addressComplement" => "",
                            "addressQuarter" => "Centro",
                            "addressState" => "PR",
                            "country" => "BR"
                        ]
                    ],
                    "to" => [
                        "email" => "email@domain.com",
                        "name" => "Samp? Ssss????",
                        "cellphone" => "22222-1222",
                        "document" => "07513601631",
                        "address" => [
                            "zipCode" => "04120-020",
                            "city" => "S?o Paulo",
                            "street" => "Rua Maur?cio Francisco Klabin",
                            "addressNumber" => "22222",
                            "addressComplement" => "",
                            "addressQuarter" => "Vila Marian?",
                            "addressState" => "SP",
                            "country" => "BR"
                        ]
                    ]
                ],
                "volumes" => [
                    "volumeId" => 1,
                    "weight" => 23.34,
                    "length" => 18,
                    "height" => 13,
                    "width" => 18,
                    "price" => 549.74,
                    "declaredValue" => 549.74,
                    "orderItemsId" => [
                        "2968",
                        "2969",
                        "2970",
                        "2971",
                        "2972"
                    ]
                ],
                "quotation" => [
                    "shippingServiceCode" => "03298",
                    "shippingServiceName" => "PAC",
                    "platformShippingPrice" => 10.93,
                    "deliveryTime" => 3,
                    "carrier" => "Correios",
                    "carrierCode" => "COR",
                    "shippingPrice" => 10.93,
                    "shippingCompetitorPrice" => null,
                    "services" => [
                        "declaredValue" => false,
                        "receiptNotification" => false,
                        "ownHand" => false
                    ]
                ],
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
        var_dump($response);
        // Verifica se o cancelamento foi bem-sucedido
        $this->assertNull($response);
    }
}