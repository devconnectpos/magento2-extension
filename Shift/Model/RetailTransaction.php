<?php
namespace SM\Shift\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class RetailTransaction extends AbstractModel implements RetailTransactionInterface, IdentityInterface
{

    const CACHE_TAG = 'sm_retail_transaction';

    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ResourceModel\RetailTransaction');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
