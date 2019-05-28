<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 12/01/2017
 * Time: 11:28
 */

namespace SM\Setting\Repositories\SettingManagement;

use Magento\Framework\App\Config\ScopeConfigInterface;
use SM\CustomSale\Helper\Data;
use SM\Product\Helper\ProductHelper;
use SM\Product\Repositories\ProductManagement;

class Product extends AbstractSetting
{

    /**
     * @var string
     */
    protected $CODE = 'product';
    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;
    /**
     * @var \SM\Product\Repositories\ProductManagement
     */
    private $productManagement;
    /**
     * @var \SM\Product\Helper\ProductHelper
     */
    private $productHelper;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \SM\CustomSale\Helper\Data                         $customSaleHelper
     * @param \SM\Product\Repositories\ProductManagement         $productManagement
     * @param \SM\Product\Helper\ProductHelper                   $productHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $customSaleHelper,
        ProductManagement $productManagement,
        ProductHelper $productHelper
    ) {
        $this->customSaleHelper  = $customSaleHelper;
        $this->productHelper    = $productHelper;
        $this->productManagement = $productManagement;
        parent::__construct($scopeConfig);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function build()
    {
        return [
            'custom_sale_product_id' => $this->customSaleHelper->getCustomSaleId(),
            // FIXME: REMOVE IN NEXT VERSION. NOW WE SUPPORT OLD CONNECT POS VERSION.
            'product_attributes'     => $this->productHelper->getProductAttributes(),
            'custom_sale_product'    => $this->productManagement->getCustomSaleData($this->getStore(), null)
                                                                ->getOutput(),
        ];
    }
}
