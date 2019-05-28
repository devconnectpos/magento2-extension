<?php
/**
 * Created by PhpStorm.
 * User: xuantung
 * Date: 9/14/18
 * Time: 2:11 PM
 */

namespace SM\Shipping\Repositories;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Shipping;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\ShippingMethod;
use SM\Sales\Repositories\OrderManagement;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

class ShippingManagement extends ServiceAbstract
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;
    /** @var \Magento\Shipping\Model\Config $shippingConfig */
    protected $shippingConfig;

    protected $objectManager;

    /**
     * ShippingManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ScopeConfigInterface                       $scopeConfig
     * @param \Magento\Shipping\Model\Config             $shippingConfig
     * @param ObjectManagerInterface                     $objectManager
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Config $shippingConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
        $this->objectManager = $objectManager;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getShippingMethods()
    {
        $methods = array();
        $activeCarriers = $this->shippingConfig->getAllCarriers();
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            if (in_array($carrierCode, OrderManagement::getAllowedShippingMethods())) {
                $options = array();
                if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code= $carrierCode.'_'.$methodCode;
                        $options[]=array('value'=>$code,'label'=>$method);

                    }
                    $carrierTitle = $this->scopeConfig->getValue(
                        'carriers/' . $carrierCode . '/title',
                        ScopeInterface::SCOPE_STORE
                    );

                    $shipping_method = new ShippingMethod();
                    $shipping_method->setData('code', $carrierCode);
                    $shipping_method->setData('label', $carrierTitle);
                    $shipping_method->setData('is_active', $carrierModel->getConfigData('active'));
                    $shipping_method->setData('magento_active', $carrierModel->getConfigData('active'));
                    $shipping_method->setData('showmethod', $carrierModel->getConfigData('showmethod'));
                    $methods[] = $shipping_method;
                }
            }
        }
        if ($this->getSearchCriteria()->getData('currentPage') > 1) {
            return $this->getSearchResult()->setItems([])->getOutput();
        }

        return $this->getSearchResult()
            ->setSearchCriteria($this->getSearchCriteria())
            ->setItems($methods)
            ->setTotalCount(count($methods))
            ->setMessageError(OrderManagement::$MESSAGE_ERROR)
            ->getOutput();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function updateShippingMethods()
    {
        $data = $this->getRequest()->getParams();
        $items = array();
        foreach ($data['methods'] as $method) {
            $item = new ShippingMethod();
            $item->setData('code', $method['code']);
            $item->setData('label', $method['label']);
            $item->setData('is_active', $method['is_active']);
            $item->setData('magento_active', $method['magento_active']);
            $item->setData('showmethod', $method['showmethod']);
            $items[] = $item;
        }
        return $this->getSearchResult()
            ->setItems($items)
            ->setTotalCount(1)
            ->setLastPageNumber(1)
            ->getOutput();
    }
}
