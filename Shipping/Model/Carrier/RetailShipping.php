<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SM\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use SM\Sales\Repositories\OrderManagement;

/**
 * Flat rate shipping model
 */
class RetailShipping extends AbstractCarrier implements CarrierInterface
{

    const RETAIL_SHIPPING_AMOUNT_KEY = 'retail_shipping_amount';
    /**
     * @var string
     */
    protected $_code = 'retailshipping';

    /**
     * @var bool
     */
    protected $isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ItemPriceCalculator
     */
    private $itemPriceCalculator;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param ItemPriceCalculator                                         $itemPriceCalculator
     * @param \Magento\Framework\Registry                                 $registry
     * @param array                                                       $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ItemPriceCalculator $itemPriceCalculator,
        Registry $registry,
        array $data = []
    ) {
        $this->registry            = $registry;
        $this->rateResultFactory  = $rateResultFactory;
        $this->rateMethodFactory  = $rateMethodFactory;
        $this->itemPriceCalculator = $itemPriceCalculator;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     *
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        if (!OrderManagement::$FROM_API) {
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        $shippingPrice = $this->getShippingPrice($request);

        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['flatrate' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @param int         $freeBoxes
     *
     * @return bool|float
     */
    private function getShippingPrice(RateRequest $request)
    {
        $shippingPrice = $this->registry->registry(self::RETAIL_SHIPPING_AMOUNT_KEY);
        if (!$shippingPrice) {
            $shippingPrice = 0;
        }

        return $shippingPrice;
    }

    /**
     * @param int|float $shippingPrice
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('retailshipping');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('retailshipping');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }
}
