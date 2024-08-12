<?php
namespace Frenet\Services;

use Frenet\Http\Client;
use Frenet\Exceptions\ValidationException;
use Frenet\Exceptions\QuoteException;

class Quotes extends BaseService {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function getQuote(array $params) {
        $requiredParams = [
            'senderZipCode' => 'string',
            'recipientZipCode' => 'string',
            'recipientCountry' => 'string',
            'shipmentItemValue' => 'numeric',
            'services' => 'array',
            'volumes' => 'array'
        ];

        $this->validateParams($params, $requiredParams);
        $this->validateServices($params['services']);
        $this->validateVolumes($params['volumes']);
        $response = $this->client->request('POST', 'quotes', $params);
        return $this->processQuoteResponse($response);
    }

    /**
     * Processa a resposta da cotação.
     *
     * @param array $response
     * @return array
     * @throws \Frenet\Exceptions\QuoteException
     */
    private function processQuoteResponse(array $response) {
        if (!isset($response['quotations']) || empty($response['quotations'])) {
            throw new QuoteException('No quotations found in the response.');
        }

        $processedQuotations = [];
        foreach ($response['quotations'] as $quotation) {
            if (isset($quotation['error']) && $quotation['error'] === true) {
                throw new QuoteException("Error in quotation: {$quotation['msg']}");
            }

            $processedQuotations[] = [
                'shippingServiceCode' => $quotation['shippingServiceCode'],
                'shippingServiceName' => $quotation['shippingServiceName'],
                'platformShippingPrice' => $quotation['platformShippingPrice'],
                'deliveryTime' => $quotation['deliveryTime'],
                'carrier' => $quotation['carrier'],
                'carrierCode' => $quotation['carrierCode'],
                'shippingPrice' => $quotation['shippingPrice'],
                'shippingCompetitorPrice' => $quotation['shippingCompetitorPrice'],
                'services' => $quotation['services'],
            ];
        }

        return [
            'sessionId' => $response['sessionId'] ?? null,
            'quotations' => $processedQuotations,
        ];
    }

    private function validateServices(array $services) {
        $requiredServices = [
            'declaredValue' => 'boolean',
            'receiptNotification' => 'boolean',
            'ownHand' => 'boolean'
        ];

        $this->validateParams($services, $requiredServices);
    }

    private function validateVolumes(array $volumes) {
        $requiredVolumeParams = [
            'weight' => 'numeric',
            'length' => 'numeric',
            'height' => 'numeric',
            'width' => 'numeric',
            'isFragile' => 'boolean'
        ];

        $this->validateArrayParams($volumes, $requiredVolumeParams);
    }
}