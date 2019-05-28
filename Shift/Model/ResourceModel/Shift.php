<?php
namespace SM\Shift\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Shift extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_shift_shift', 'id');
    }
}
