<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 14/03/2017
 * Time: 18:08
 */

namespace SM\Performance\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use SM\Performance\Helper\RealtimeManager;

class AfterShipment implements ObserverInterface
{

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;

    /**
     * AfterCheckout constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        RealtimeManager $realtimeManager
    ) {
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        try {
            $this->realtimeManager->trigger(
                RealtimeManager::ORDER_ENTITY,
                $order->getId(),
                RealtimeManager::TYPE_CHANGE_NEW
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
