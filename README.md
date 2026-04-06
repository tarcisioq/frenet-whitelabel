# Frenet WhiteLabel SDK

SDK PHP nao oficial para integracao com a API Frenet WhiteLabel.

## Visao geral

Este pacote encapsula chamadas HTTP para a API da Frenet e faz validacao basica dos payloads antes do envio.

Hoje a SDK cobre principalmente:

- Cotacao de frete
- Criacao e consulta de shipments
- Cancelamento e remocao de shipments
- Consulta de servicos de transporte

## Instalacao

```bash
composer require tarcisioq/frenet-whitelabel-sdk
```

## Requisitos

- PHP com suporte a `mbstring`
- Composer
- Credenciais da Frenet WhiteLabel:
- `token`
- `x-partner-token`

## Configuracao

```php
<?php

require 'vendor/autoload.php';

use Frenet\Config;
use Frenet\FrenetSDK;

$config = new Config(
    'SEU_API_KEY',
    'SEU_PARTNER_TOKEN',
    'sandbox' // ou 'production'
);

$frenet = new FrenetSDK($config);
```

Ambientes disponiveis:

- `sandbox`: `https://whitelabel-hml.frenet.dev/v1/`
- `production`: `https://whitelabel.frenet.com.br/v1/`

Para endpoints de configuracao de transportadoras, a SDK usa:

- `sandbox`: `https://whitelabel-configuration-hml.frenet.dev/v1/`
- `production`: `https://whitelabel-configuration.frenet.com.br/v1/`

## Uso rapido

### 1. Cotar frete

```php
<?php

use Frenet\Exceptions\QuoteException;
use Frenet\Exceptions\ValidationException;

try {
    $quote = $frenet->quotes()->getQuote([
        'senderZipCode' => '01001-000',
        'recipientZipCode' => '20010-000',
        'recipientCountry' => 'BR',
        'shipmentItemValue' => 199.90,
        'services' => [
            'declaredValue' => true,
            'receiptNotification' => false,
            'ownHand' => false,
        ],
        'volumes' => [
            [
                'weight' => 1.5,
                'length' => 20,
                'height' => 10,
                'width' => 15,
                'isFragile' => false,
            ],
        ],
    ]);

    print_r($quote);
} catch (ValidationException | QuoteException $e) {
    echo $e->getMessage();
}
```

Resposta processada:

```php
[
    'sessionId' => '...',
    'quotations' => [
        [
            'shippingServiceCode' => '...',
            'shippingServiceName' => '...',
            'platformShippingPrice' => 0.0,
            'deliveryTime' => 0,
            'carrier' => '...',
            'carrierCode' => '...',
            'shippingPrice' => 0.0,
            'shippingCompetitorPrice' => 0.0,
            'services' => [],
        ],
    ],
]
```

### 2. Criar shipment

```php
<?php

use Frenet\Exceptions\ShipmentException;
use Frenet\Exceptions\ValidationException;

try {
    $shipment = $frenet->shipments()->createShipmentOneClick([
        'order' => [
            'id' => '152378',
            'value' => 549.74,
            'created' => '2024-07-26 16:20:49',
            'items' => [
                [
                    'orderId' => '152378',
                    'itemId' => '2968',
                    'productId' => '100',
                    'weight' => 11.0,
                    'length' => 2,
                    'height' => 3,
                    'width' => 5,
                    'quantity' => 1,
                    'price' => 123.20,
                    'productName' => 'Produto Exemplo',
                    'sku' => 'SKU-001',
                ],
            ],
            'from' => [
                'name' => 'Loja Origem',
                'document' => '08323617000150',
                'address' => [
                    'zipCode' => '86020-110',
                    'city' => 'Londrina',
                    'street' => 'Rua Exemplo',
                    'addressNumber' => '620',
                    'addressComplement' => '',
                    'addressState' => 'PR',
                    'country' => 'BR',
                ],
            ],
            'to' => [
                'name' => 'Cliente Final',
                'document' => '07513601631',
                'address' => [
                    'zipCode' => '04120-020',
                    'city' => 'Sao Paulo',
                    'street' => 'Rua Destino',
                    'addressNumber' => '100',
                    'addressComplement' => '',
                    'addressState' => 'SP',
                    'country' => 'BR',
                ],
            ],
        ],
        'volumes' => [
            'volumeId' => 1,
            'weight' => 23.34,
            'length' => 18,
            'height' => 13,
            'width' => 18,
            'price' => 549.74,
            'declaredValue' => 549.74,
            'orderItemsId' => ['2968'],
        ],
        'quotation' => [
            'shippingServiceCode' => '03298',
            'shippingServiceName' => 'PAC',
            'platformShippingPrice' => 10.93,
            'deliveryTime' => 3,
            'carrier' => 'Correios',
            'carrierCode' => 'COR',
            'shippingPrice' => 10.93,
            'services' => [
                'declaredValue' => false,
                'receiptNotification' => false,
                'ownHand' => false,
            ],
        ],
    ]);

    print_r($shipment);
} catch (ValidationException | ShipmentException $e) {
    echo $e->getMessage();
}
```

Resposta processada:

```php
[
    'shipmentId' => '...',
    'trackingUrl' => '...',
    'labelUrl' => '...',
    'declarationUrl' => '...',
    'receiptNotificationUrl' => '...',
    'validThrough' => '...',
    'status' => '...',
]
```

### 3. Consultar, cancelar e remover shipment

```php
<?php

$shipmentId = 'ID_DO_SHIPMENT';

$details = $frenet->shipments()->getShipmentById($shipmentId);
$label = $frenet->shipments()->getShipmentLabelById($shipmentId);
$frenet->shipments()->cancelShipment($shipmentId);
$frenet->shipments()->deleteShipment($shipmentId);
```

## Consulta de servicos de transporte

`FrenetSDK` expoe apenas `quotes()` e `shipments()`. Para consultar servicos de transporte, instancie `Carriers` diretamente com a URL de configuracao.

```php
<?php

use Frenet\Config;
use Frenet\Http\Client;
use Frenet\Services\Carriers;

$config = new Config('SEU_API_KEY', 'SEU_PARTNER_TOKEN', 'sandbox');
$client = new Client(
    $config->getConfigurationUri(),
    $config->getApiKey(),
    $config->getPartnerToken()
);

$carriers = new Carriers($client);
$services = $carriers->getServices();

foreach ($services as $service) {
    echo $service->getServiceCode() . ' - ' . $service->getServiceDescription() . PHP_EOL;
}
```

Cada item retornado e uma instancia de `Frenet\Services\Carrier`, com metodos como:

- `getServiceCode()`
- `getServiceDescription()`
- `getCarrier()`
- `getCarrierCode()`
- `isEnabled()`
- `hasDeclaredValue()`
- `hasReceiptNotification()`
- `hasInPersonDelivery()`

## Excecoes

A SDK lanca excecoes especificas para facilitar o tratamento de erro:

- `Frenet\Exceptions\ValidationException`
- `Frenet\Exceptions\QuoteException`
- `Frenet\Exceptions\ShipmentException`
- `Frenet\Exceptions\CarrierException`
- `Frenet\Exceptions\FrenetException`

Exemplo:

```php
<?php

use Frenet\Exceptions\FrenetException;

try {
    $result = $frenet->quotes()->getQuote($payload);
} catch (FrenetException $e) {
    echo $e->getMessage();
}
```

## Estrutura publica atual

Classes principais:

- `Frenet\Config`
- `Frenet\FrenetSDK`
- `Frenet\Services\Quotes`
- `Frenet\Services\Shipments`
- `Frenet\Services\Carriers`
- `Frenet\Services\Orders`

Facade disponivel via `FrenetSDK`:

- `quotes()`
- `shipments()`

## Limitacoes atuais da SDK

- `FrenetSDK` nao expoe `carriers()` nem `orders()`
- O servico `Orders` existe no codigo, mas nao esta integrado na facade principal
- A validacao de payload e parcial e cobre apenas os campos obrigatorios implementados na SDK
- Os testes do repositorio fazem chamadas reais a API, entao dependem de credenciais validas

## Testes

```bash
composer test
```

## Licenca

MIT. Veja [LICENSE](LICENSE).
