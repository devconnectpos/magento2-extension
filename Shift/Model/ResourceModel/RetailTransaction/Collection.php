<?php
namespace SM\Shift\Model\ResourceModel\RetailTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'SM\Shift\Model\RetailTransaction',
            'SM\Shift\Model\ResourceModel\RetailTransaction'
        );
    }
}
