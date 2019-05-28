<?php
namespace SM\Payment\Model\ResourceModel\RetailPayment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'SM\Payment\Model\RetailPayment',
            'SM\Payment\Model\ResourceModel\RetailPayment'
        );
    }
}
