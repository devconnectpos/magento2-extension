<?php
namespace SM\Shipping\Model;

use SM\Shipping\Api\OutletManagementInterface;
use Magento\Framework\DataObject;
use SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory;

class OutletManagement implements OutletManagementInterface
{
    /**
     * @var \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory
     */
    protected $collectionFactory;

    protected $outletFactory;

    /**
     * OfficeManagement constructor.
     *
     * @param \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function fetchOutlets()
    {
        $result     = [];
        $collection = $this->collectionFactory->create();

        foreach ($collection as $item) {
            if ($item->getData('allow_click_and_collect') === '1') {
                $result[] = [
                    'id'      => $item->getData('id'),
                    'name'     => $item->getData('name'),
                    'address'  => $item->getData('street') . ',' . $item->getData('city') . ',' . $item->getData('country_id'),
                    'location' => $item->getData('lat') . ',' . $item->getData('lng')
                ];
            }
        }
        return $result;
    }
}
