<?php
namespace SM\Shift\Model\ResourceModel\Shift;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Shift\Model\Shift', 'SM\Shift\Model\ResourceModel\Shift');
    }
}
