<?php
/**
 * Created by PhpStorm.
 * User: xuantung
 * Date: 9/19/18
 * Time: 5:22 PM
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class ShippingMethod extends ApiDataAbstract
{
    public function getCode() {
        return $this->getData('code');
    }

    public function getLabel() {
        return $this->getData('label');
    }

    public function getIsActive() {
        return $this->getData('is_active') === '1';
    }

    public function getMagentoActive() {
        return $this->getData('magento_active') === '1';
    }

    public function getShowmethod() {
        return $this->getData('showmethod') === '1';
    }

}