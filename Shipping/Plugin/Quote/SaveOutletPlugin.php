<?php
namespace SM\Shipping\Plugin\Quote;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;
use SM\Shipping\Model\Carrier\RetailStorePickUp;

class SaveOutletPlugin
{
    protected $quoteRepository;

    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getData('shipping_address');
        if ($addressInformation->getData('shipping_method_code') == RetailStorePickUp::METHOD_CODE
            && $shippingAddress->getExtensionAttributes()->getOutletAddress()) {
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setOutletId($shippingAddress->getExtensionAttributes()->getOutletAddress());
        }
    }
}
