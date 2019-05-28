<?php
namespace SM\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

class SaveOutletInformationToOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(ObjectManagerInterface $objectmanager)
    {
        $this->objectManager = $objectmanager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $order           = $observer->getOrder();
        $quoteRepository = $this->objectManager->create('Magento\Quote\Model\QuoteRepository');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteRepository->get($order->getQuoteId());
        if ($quote->getOutletId() != null) {
            $order->setOutletId($quote->getOutletId());
        }

        return $this;
    }
}
