<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/11/2016
 * Time: 15:54
 */

namespace SM\Setting\Repositories;

use Exception;
use Magento\Config\Model\Config\Loader;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\RetailConfig;
use SM\Core\Api\Data\XSetting;
use SM\Core\Model\DataObject;
use SM\CustomSale\Helper\Data;
use SM\Integrate\Helper\Data as IntegrateHelper;
use SM\Integrate\Model\GCIntegrateManagement;
use SM\Product\Helper\ProductHelper;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class SettingManagement
 *
 * @package SM\Setting\Repositories
 */
class SettingManagement extends ServiceAbstract
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $configLoader;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResource;

    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;
    /**
     * @var \SM\Product\Helper\ProductHelper
     */
    private $productHelper;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;

    /**
     * @var \SM\Integrate\Model\GCIntegrateManagement
     */
    private $gcIntegrateManagement;

    /**
     * SettingManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Config\Model\Config\Loader        $loader
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \SM\CustomSale\Helper\Data                 $customSaleHelper
     * @param \SM\Integrate\Helper\Data                  $integrateHelperData
     * @param \SM\Integrate\Model\GCIntegrateManagement  $GCIntegrateManagement
     * @param \SM\Product\Helper\ProductHelper           $productHelper
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        Loader $loader,
        Config $config,
        Data $customSaleHelper,
        IntegrateHelper $integrateHelperData,
        GCIntegrateManagement $GCIntegrateManagement,
        ProductHelper $productHelper

    ) {
        $this->configLoader     = $loader;
        $this->configResource   = $config;
        $this->objectManager    = $objectManager;
        $this->customSaleHelper = $customSaleHelper;
        $this->integrateHelperData = $integrateHelperData;
        $this->gcIntegrateManagement = $GCIntegrateManagement;
        $this->productHelper    = $productHelper;
        $this->integrateHelperData = $integrateHelperData;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     *
     * @throws \Exception
     */
    public function getSettingData()
    {
        $settings = [];
        if ($this->getSearchCriteria()->getData('currentPage') != 1) {
        } else {
            // Các function get data liên quan đến store sẽ lấy theo store này.
            $store = $this->getSearchCriteria()->getData('storeId');
            if (is_null($store)) {
                throw  new Exception("Must have param storeId");
            }
            $this->storeManager->setCurrentStore($store);

            foreach ($this->getSettingEntityCollection() as $item) {
                /** @var \SM\Setting\Repositories\SettingManagement\AbstractSetting $instance */
                $instance = $this->objectManager->create($item);
                $instance->setStore($store);
                $setting = new XSetting();
                $setting->setData('key', $instance->getCODE());
                $setting->setData('value', $instance->build());
                $settings[] = $setting;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($this->getSearchCriteria())
                    ->setItems($settings)
                    ->setLastPageNumber(1)
                    ->getOutput();
    }

    /**
     * @return array
     */
    protected function getSettingEntityCollection()
    {
        return [
            '\SM\Setting\Repositories\SettingManagement\Tax',
            '\SM\Setting\Repositories\SettingManagement\Shipping',
            '\SM\Setting\Repositories\SettingManagement\Customer',
            '\SM\Setting\Repositories\SettingManagement\Product',
            '\SM\Setting\Repositories\SettingManagement\Store'
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getRetailSettingData()
    {
        $searchCriteria = $this->getSearchCriteria();
        if (!$searchCriteria->getData('group')) {
            throw new Exception(__("Please define setting group"));
        } else {
            $group = $searchCriteria->getData('group');
        }

        $_gs     = explode(",", $group);
        $configs = [];
        if ($searchCriteria->getData('currentPage') > 1) {
        } else {
            foreach ($_gs as $g) {
                $config     = [];
                $configData = $this->configLoader->getConfigByPath('xretail/' . $g, 'default', 0);
                foreach ($configData as $configDatum) {
                    $config[$configDatum['path']] = $this->convertValue($configDatum['value']);
                }
                if ($g === 'pos') {
                    $config["productAttributes"] = $this->productHelper->getProductAttributes();
                    if ($this->integrateHelperData->isAHWGiftCardxist()
                        && $this->integrateHelperData->isIntegrateGC()) {
                        $config['list_code_pools'] = $this->gcIntegrateManagement->getGCCodePool();
                    }
                }

                $config["xretail/pos/integrate_wh"] = "none";

                if (!!$this->integrateHelperData->isMagentoInventory()) {
                    $config["xretail/pos/integrate_wh"] = "magento_inventory";
                }

                if (!!$this->integrateHelperData->isIntegrateWH()) {
                    $config["xretail/pos/integrate_wh"] = "bms";
                }

                $retailConfig = new RetailConfig();
                $retailConfig->setData('key', $g)->setData('value', $config);
                $configs[] = $retailConfig;
            }
        }

        return $this->getSearchResult()->setItems($configs)->getOutput();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function saveRetailSettingData()
    {
        $configData = $this->getRequest()->getParam('data');
        foreach ($configData as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->configResource->saveConfig($key, $value, 'default', 0);
        }
        //FIX XRT :549 update custom sales tax class
        if (isset($configData['xretail/pos/custom_sale_tax_class'])) {
            $customerSales = $this->customSaleHelper->getCustomSaleProduct();
            $customerSales->setStoreId(0);
            $customerSales->setData('tax_class_id', $configData['xretail/pos/custom_sale_tax_class']);
            $customerSales->save();
        }
        if (isset($configData['xretail/pos/integrate_gc'])
            && $this->integrateHelperData->isAHWGiftCardxist()) {
            if ($configData['xretail/pos/integrate_gc'] == "aheadWorks") {
                $data                = [
                    'is_default_codepool_pattern' => $configData['xretail/pos/is_use_default_codepool_pattern'],
                    'code_pool'                   => $configData['xretail/pos/refund_gc_codepool'],
                ];
                $this->integrateHelperData->getGcIntegrateManagement()->updateRefundToGCProduct($data);
            }
        }
        // check saving reward point integration info
        if (isset($configData['xretail/pos/integrate_rp'])) {
            if ($configData['xretail/pos/integrate_rp'] === 'aheadWorks'
                && !$this->integrateHelperData->isAHWRewardPoints()) {
                throw new LocalizedException(
                    __('Module Aheadworks_RewardPoints is not found!')
                );
            } elseif ($configData['xretail/pos/integrate_rp'] === 'mage2_ee'
                     && !$this->integrateHelperData->isRewardPointMagento2EE()) {
                throw new LocalizedException(
                    __('Module Magento_Reward is not found!')
                );
            }
        }
        //check saving gift card integration info
        if (isset($configData['xretail/pos/integrate_gc'])) {
            if ($configData['xretail/pos/integrate_gc'] === 'aheadWorks'
               && !$this->integrateHelperData->isAHWGiftCardxist()) {
                throw new LocalizedException(
                    __('Module Aheadworks_Giftcard is not found!')
                );
            } elseif ($configData['xretail/pos/integrate_gc'] === 'mage2_ee'
                      && !$this->integrateHelperData->isGiftCardMagento2EE()) {
                throw new LocalizedException(
                    __('Module Magento_GiftCardAccount is not found!')
                );
            }
        }
        $this->searchCriteria = new DataObject(
            [
                'group'       => $this->getRequest()->getParam('group'),
                'currentPage' => 1
            ]
        );

        return $this->getRetailSettingData();
    }

    protected function convertValue($value)
    {
        $result = json_decode($value);
        if (json_last_error()) {
            $result = $value;
        }

        return $result;
    }
}
