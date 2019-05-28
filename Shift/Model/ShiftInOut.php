<?php
namespace SM\Shift\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ShiftInOut extends AbstractModel implements ShiftInOutInterface, IdentityInterface
{

    const CACHE_TAG = 'sm_shift_shiftinout';

    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ResourceModel\ShiftInOut');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $shiftId
     *
     * @return array
     * @throws \Exception
     */
    public function getInOutData($shiftId)
    {
        if (!$shiftId) {
            throw new \Exception("Please define shift id");
        }
        $collection = $this->getCollection();
        $collection->addFieldToFilter('shift_id', $shiftId);

        $items = [];
        foreach ($collection as $inOut) {
            $items[] = $inOut->getData();
        }

        return $items;
    }
}
