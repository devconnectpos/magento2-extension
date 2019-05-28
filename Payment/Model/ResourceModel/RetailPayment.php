<?php
namespace SM\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RetailPayment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sm_payment', 'id');
    }
}
