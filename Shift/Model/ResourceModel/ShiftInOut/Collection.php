<?php
namespace SM\Shift\Model\ResourceModel\ShiftInOut;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ShiftInOut', 'SM\Shift\Model\ResourceModel\ShiftInOut');
    }
}
