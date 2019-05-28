<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 18/03/2017
 * Time: 11:50
 */

namespace SM\Shift\Helper;

use Exception;
use SM\Shift\Model\ResourceModel\Shift\CollectionFactory;

/**
 * Class Data
 *
 * @package SM\Shift\Helper
 */
class Data
{
    /**
     * @var \SM\Shift\Model\ResourceModel\Shift\CollectionFactory
     */
    private $shiftCollectionFactory;

    private $shift = [];

    /**
     * Data constructor.
     *
     * @param \SM\Shift\Model\ResourceModel\Shift\CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->shiftCollectionFactory = $collectionFactory;
    }

    /**
     * @return \SM\Shift\Model\ResourceModel\Shift\Collection
     */
    protected function getShiftCollection()
    {
        return $this->shiftCollectionFactory->create();
    }

    /**
     * @param $outletId
     * @param $registerId
     *
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    public function getShiftOpening($outletId, $registerId)
    {
        if (is_null($outletId) || is_null($registerId)) {
            throw new Exception("Must define required data");
        }

        if (!isset($this->shift[$outletId . "|" . $registerId])) {
            /** @var \SM\Shift\Model\ResourceModel\Shift\Collection $collection */
            $collection = $this->shiftCollectionFactory->create();
            $collection->addFieldToFilter('outlet_id', $outletId)
                       ->addFieldToFilter('register_id', $registerId)
                       ->addFieldToFilter('is_open', 1);
            $this->shift[$outletId . "|" . $registerId] = $collection->getFirstItem();
        }

        return $this->shift[$outletId . "|" . $registerId];
    }
}
