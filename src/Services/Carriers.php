<?php
namespace Frenet\Services;

use Frenet\Http\Client;
use Frenet\Exceptions\CarrierException;
use Frenet\Services\Carrier;

class Carriers extends BaseService {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Obtém todos os serviços de transporte.
     *
     * @return array
     * @throws \Frenet\Exceptions\CarrierException
     */
    public function getServices() {
        $response = $this->client->request('GET', 'carriers/services');
        return $this->processGetServicesResponse($response);
    }

    /**
     * Processa a resposta da API para a requisição GET /carriers/services.
     *
     * @param array $response
     * @return array
     * @throws \Frenet\Exceptions\ServiceException
     */
    private function processGetServicesResponse(array $response) {
        if (isset($response['shippingServices'])) {
            $carriers = [];
            foreach ($response['shippingServices'] as $carrierData) {
                $carrier = new Carrier($carrierData);
                if ($carrier->isEnabled()) $carriers[] = $carrier;
            }
            return $carriers;
        }

        if (isset($response['Message'])) {
            $errorMessage = $response['Message'];
            $details = $this->formatErrors($response['Details'] ?? []);
            throw new CarrierException("Error fetching services: {$errorMessage}. Details: {$details}");
        }

        throw new CarrierException('Unknown response structure.');
    }

    /**
     * Formata erros em uma string legível.
     *
     * @param array $errors
     * @return string
     */
    private function formatErrors(array $errors) {
        $formatted = [];
        foreach ($errors as $error) {
            $code = $error['Code'] ?? 'unknown';
            $message = $error['Message'] ?? 'No message provided';
            $formatted[] = "Code {$code}: {$message}";
        }
        return implode('; ', $formatted);
    }
}