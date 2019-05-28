<?php
namespace SM\Shift\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ShiftInOut extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_shift_shiftinout', 'id');
    }
}
