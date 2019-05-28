<?php
namespace SM\Shift\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RetailTransaction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_retail_transaction', 'id');
    }
}
