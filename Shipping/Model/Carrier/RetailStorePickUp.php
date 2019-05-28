<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SM\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory;

/**
 * Flat rate shipping model
 */
class RetailStorePickUp extends AbstractCarrier implements CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'smstorepickup';
    const METHOD_CODE = 'smstorepickup';

    protected $outletCollectionFactory;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * RetailShipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory    $outletCollectionFactory
     * @param array                                                       $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CollectionFactory $outletCollectionFactory,
        array $data = []
    ) {
        $this->rateResultFactory  = $rateResultFactory;
        $this->rateMethodFactory  = $rateMethodFactory;
        $this->outletCollectionFactory = $outletCollectionFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }


    /**
     * @return array
     */
    public function getAllowedMethods()
    {
    $collection = $this->outletCollectionFactory->create();
        if ($collection->getSize() == 0) {
            return [];
        } else {
            return [$this->_code => $this->getConfigData('name')];
        }
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
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}
