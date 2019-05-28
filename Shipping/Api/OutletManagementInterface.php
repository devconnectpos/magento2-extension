<?php
namespace SM\Shipping\Api;

interface OutletManagementInterface
{

    /**
     * Find offices for the customer
     *
     * @return \Vendor\Module\Api\Data\OfficeInterface[]
     */
    public function fetchOutlets();
}
