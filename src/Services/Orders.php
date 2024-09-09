<?php
namespace Frenet\Services;

use Frenet\Exceptions\FrenetException;
use Frenet\Http\Client;
use Frenet\Exceptions\ShipmentException;
use Frenet\Exceptions\ValidationException;

class Orders extends BaseService {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Cria um novo shipment.
     *
     * @param array $params
     * @return mixed
     * @throws \Frenet\Exceptions\ValidationException
     */
    public function createOrder(array $params) {
        $this->validateShipmentParams($params);
        $response = $this->client->request('POST', 'orders', [$params]);
        return $this->processCreateShipmentResponse($response);
    }

    public function createOrderOneClick(array $params) {
        $this->validateShipmentParams($params);
        $response = $this->client->request('POST', 'orders/oneclick', [$params]);
        return $this->processCreateShipmentResponse($response);
    }

    /**
     * Busca um shipment pelo ID.
     *
     * @param string $shipmentId
     * @return mixed
     */
    public function getOrderById(string $shipmentId) {
        return $this->client->request('GET', "orders/{$shipmentId}");
    }

    /**
     * Cancela um shipment pelo ID.
     *
     * @param string $shipmentId
     * @return mixed
     */
    public function cancelShipment(string $shipmentId) {
        return $this->client->request('POST', "shipments/{$shipmentId}/cancel");
    }

    /**
     * Deleta um shipment pelo ID.
     *
     * @param string $shipmentId
     * @return mixed
     */
    public function deleteShipment(string $shipmentId) {
        return $this->client->request('DELETE', "shipments/{$shipmentId}");
    }

    /**
     * Obtem a label de um shipment pelo ID.
     *
     * @param string $shipmentId
     * @return mixed
     */
    public function getShipmentLabelById(string $shipmentId) {
        return $this->client->request('GET', "shipments/{$shipmentId}/label");
    }

    /**
     * Processa a resposta ao criar um shipment.
     *
     * @param array $response
     * @return array
     * @throws \Frenet\Exceptions\ShipmentException
     */
    private function processCreateShipmentResponse(array $response) {
        if (isset($response['statusBatch'])) {
            $items = $response['items'] ?? [];
            if (empty($items)) {
                throw new ShipmentException('Shipment created, but no items found in the response.');
            }

            $item = $items[0]; // Supondo que sempre haverá pelo menos um item
            if (isset($item['errors']) && !empty($item['errors'])) {
                $errors = $this->formatErrors($item['errors']);
                throw new ShipmentException("Errors occurred during shipment creation: {$errors}");
            }
            if ($response['statusBatch'] === 'Processado') {
                return [
                    'shipmentId' => $item['shipmentId'],
                    'trackingUrl' => $item['trackingUrl'],
                    'labelUrl' => $item['labelUrl'],
                    'declarationUrl' => $item['declarationUrl'],
                    'receiptNotificationUrl' => $item['receiptNotificationUrl'],
                    'validThrough' => $item['validThrough'],
                    'status' => $item['shipmentStatus']
                ];
            }
        }

        if (isset($response['error'])) {
            $errorMessage = $response['error']['message'] ?? 'An unknown error occurred.';
            $details = $this->formatErrors($response['error']['details'] ?? []);
            throw new ShipmentException("Shipment creation failed: {$errorMessage}. Details: {$details}");
        }


        throw new ShipmentException('Unknown response structure.');
    }


    /**
     * Valida os parâmetros para a criação de um shipment.
     *
     * @param array $params
     * @throws \Frenet\Exceptions\ValidationException
     */
    private function validateShipmentParams(array $params) {
        $requiredParams = [
//            'shipmentId' => 'numeric',
//            'trackingNotificationUrl' => 'string',
//            'statusNotificationUrl' => 'string',
//            'branchCode' => 'string',
            'order' => 'array',
            'volumes' => 'array',
            'quotation' => 'array',
//            'shipmentStatus' => 'numeric',
//            'settings' => 'array'
        ];

        $this->validateParams($params, $requiredParams);
        $this->validateOrder($params['order']);
        $this->validateVolumes($params['volumes']);
        $this->validateQuotation($params['quotation']);
    }

    private function validateOrder(array $order) {
        $requiredOrderParams = [
            'id' => 'string',
            'value' => 'numeric',
            'created' => 'string',
//            'useFrenetRegistration' => 'boolean',
            'items' => 'array',
            'from' => 'array',
            'to' => 'array',
//            'invoice' => 'array'
        ];

        $this->validateParams($order, $requiredOrderParams);
        $this->validateItems($order['items']);
        $this->validateFrom($order['from']);
        $this->validateTo($order['to']);
        if (isset($order['invoice']))
            $this->validateInvoice($order['invoice']);
    }

    private function validateItems(array $items) {
        $requiredItemParams = [
            'orderId' => 'string',
            'itemId' => 'string',
            'productId' => 'string',
//            'productOptions' => 'string',
//            'productType' => 'string',
            'weight' => 'numeric',
            'length' => 'numeric',
            'height' => 'numeric',
            'width' => 'numeric',
            'quantity' => 'numeric',
            'price' => 'numeric',
//            'isFragile' => 'boolean',
            'productName' => 'string',
            'sku' => 'string',
//            'category' => 'string'
        ];

        $this->validateArrayParams($items, $requiredItemParams);
    }

    private function validateFrom(array $from) {
        $requiredFromParams = [
            'name' => 'string',
//            'phone' => 'string',
//            'cellphone' => 'string',
            'document' => 'string',
//            'ie' => 'string',
            'address' => 'array',
        ];
        $this->validateParams($from, $requiredFromParams);
        $this->validateAddress($from["address"]);
    }

    private function validateTo(array $to) {
        $requiredToParams = [
            'name' => 'string',
//            'phone' => 'string',
//            'cellphone' => 'string',
            'document' => 'string',
//            'ie' => 'string',
            'address' => 'array',
        ];
        $this->validateParams($to, $requiredToParams);
        $this->validateAddress($to["address"]);
    }

    private function validateAddress(array $address) {
        $requiredAddressParams = [
            'zipCode' => 'string',
            'city' => 'string',
            'street' => 'string',
            'addressNumber' => 'string',
            'addressComplement' => 'string',
//            'addressQuarter' => 'string',
            'addressState' => 'string',
//            'postalBoxCode' => 'string',
            'country' => 'string'
        ];

        $this->validateParams($address, $requiredAddressParams);
    }

    private function validateInvoice(array $invoice) {
        $requiredInvoiceParams = [
            'value' => 'numeric',
            'number' => 'string',
            'series' => 'string',
            'key' => 'string',
            'date' => 'string',
            'cfop' => 'string'
        ];

        $this->validateParams($invoice, $requiredInvoiceParams);
    }

    private function validateVolumes(array $volumes) {
        $requiredVolumeParams = [
            'volumeId' => 'numeric',
            'weight' => 'numeric',
            'length' => 'numeric',
            'height' => 'numeric',
            'width' => 'numeric',
            'price' => 'numeric',
            'declaredValue' => 'numeric',
            'orderItemsId' => 'array'
        ];

        $this->validateParams($volumes, $requiredVolumeParams);
    }

    private function validateQuotation(array $quotation) {
        $requiredQuotationParams = [
            'shippingServiceCode' => 'string',
            'shippingServiceName' => 'string',
            'platformShippingPrice' => 'numeric',
            'deliveryTime' => 'numeric',
            'carrier' => 'string',
            'carrierCode' => 'string',
            'shippingPrice' => 'numeric',
//            'shippingCompetitorPrice' => 'numeric',
//            'services' => 'array'
        ];

        $this->validateParams($quotation, $requiredQuotationParams);
        if (isset($quotation['services']))
            $this->validateServices($quotation['services']);
    }

    private function validateServices(array $services) {
        if (isset($services["declaredValue"]))
        $this->validateParams($services, ['declaredValue' => 'boolean']);
        if (isset($services["receiptNotification"]))
            $this->validateParams($services, ['receiptNotification' => 'boolean']);
        if (isset($services["ownHand"]))
            $this->validateParams($services, ['ownHand' => 'boolean']);
    }
}