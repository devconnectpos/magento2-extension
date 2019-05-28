<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/12/2016
 * Time: 09:17
 */

namespace SM\Payment\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;

class RetailMultiple extends AbstractMethod
{
    const PAYMENT_METHOD_RETAILMULTIPLE_CODE = 'retailmultiple';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_RETAILMULTIPLE_CODE;

    /**
     * @var string
     */
    protected $formBlockType = 'SM\Payment\Block\Form\RetailMultiple';

    /**
     * RetailMultiple constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                 $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                 $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                         $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_infoBlockType = 'SM\Payment\Block\Info\RetailMultiple';
        $this->_isOffline = true;
    }

    /**
     * @return string
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(DataObject $data)
    {
        $this->getInfoInstance()
             ->setAdditionalInformation('split_data', json_encode($data->getData('additional_data')));

        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }
}
