<?php
namespace SM\Shift\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Shift extends AbstractModel implements ShiftInterface, IdentityInterface
{
    const CACHE_TAG = 'sm_shift_shift';

    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ResourceModel\Shift');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
