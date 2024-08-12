<?php
namespace Frenet\Services;

class Carrier
{
    private $serviceCode;
    private $serviceDescription;
    private $carrier;
    private $carrierCode;
    private $enabled;
    private $declaredValue;
    private $receiptNotification;
    private $inPersonDelivery;

    public function __construct(array $data)
    {
        $this->serviceCode = $data['ServiceCode'] ?? '';
        $this->serviceDescription = $data['ServiceDescription'] ?? '';
        $this->carrier = $data['Carrier'] ?? '';
        $this->carrierCode = $data['CarrierCode'] ?? '';
        $this->enabled = (bool)($data['Enabled'] ?? false);
        $this->declaredValue = (bool)($data['DeclaredValue'] ?? false);
        $this->receiptNotification = (bool)($data['ReceiptNotification'] ?? false);
        $this->inPersonDelivery = (bool)($data['InPersonDelivery'] ?? false);
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    public function getServiceDescription(): string
    {
        return $this->serviceDescription;
    }

    public function getCarrier(): string
    {
        return $this->carrier;
    }

    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function hasDeclaredValue(): bool
    {
        return $this->declaredValue;
    }

    public function hasReceiptNotification(): bool
    {
        return $this->receiptNotification;
    }

    public function hasInPersonDelivery(): bool
    {
        return $this->inPersonDelivery;
    }
}