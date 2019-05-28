<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 2/21/17
 * Time: 2:38 PM
 */

namespace SM\CustomSale\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use SM\CustomSale\Helper\Data as CustomSaleHelper;
use SM\Integrate\Helper\Data as IntegrateHelper;

/**
 * Class FilterCustomSaleProduct
 *
 * @package SM\CustomSale\Plugin
 */
class FilterCustomSaleProduct
{

    /**
     * @var \SM\CustomSale\Helper\Data
     */
    protected $customSaleHelper;

    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $intergrateHelper;

    /**
     * FilterCustomSaleProduct constructor.
     *
     * @param \SM\CustomSale\Helper\Data $customSaleHelper
     * @param \SM\Integrate\Helper\Data  $intergrateHelper
     */
    public function __construct(
        CustomSaleHelper $customSaleHelper,
        IntegrateHelper $intergrateHelper
    ) {
        $this->customSaleHelper = $customSaleHelper;
        $this->intergrateHelper = $intergrateHelper;
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    public function afterGetCollection(
        ProductDataProvider $subject,
        $result
    ) {
        if ($this->intergrateHelper->isAHWGiftCardxist()
           && $this->intergrateHelper->isIntegrateGC()) {
            $arr = $this->intergrateHelper->getGcIntegrateManagement()->getRefundToGCProductId();
            $result->addFieldToFilter('entity_id', ['neq' => $arr]);
        }
        return $result->addFieldToFilter('entity_id', ['neq' => $this->customSaleHelper->getCustomSaleId()]);
    }
}