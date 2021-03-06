<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/01/2017
 * Time: 10:56
 */

namespace SM\Sales\Repositories;

use Exception;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use SM\Core\Api\Data\CustomerAddress;
use SM\Core\Api\Data\XOrder;
use SM\Customer\Helper\Data as CustomerHelper;
use SM\Integrate\Helper\Data as IntegrateHelper;
use SM\Payment\Model\RetailMultiple;
use SM\Sales\Model\ResourceModel\OrderSyncError\CollectionFactory as OrderSyncErrorCollectionFactory;
use SM\XRetail\Helper\Data;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class OrderHistoryManagement
 *
 * @package SM\Sales\Repositories
 */
class OrderHistoryManagement extends ServiceAbstract
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \SM\Customer\Helper\Data
     */
    protected $customerHelper;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \SM\Sales\Model\ResourceModel\OrderSyncError\CollectionFactory
     */
    private $orderErrorCollectionFactory;

    /**
     * @var \SM\XRetail\Helper\Data
     */
    private $retailHelper;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * OrderHistoryManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                        $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                  $dataConfig
     * @param \SM\XRetail\Helper\Data                                        $retailHelper
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory     $collectionFactory
     * @param \SM\Customer\Helper\Data                                       $customerHelper
     * @param \SM\Integrate\Helper\Data                                      $integrateHelperData
     * @param \Magento\Catalog\Model\Product\Media\Config                    $productMediaConfig
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory
     * @param \SM\Sales\Model\ResourceModel\OrderSyncError\CollectionFactory $orderErrorCollectionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                     $quoteRepository
     * @param \Magento\Sales\Model\OrderFactory                              $orderFactory
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        Data $retailHelper,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        CustomerHelper $customerHelper,
        IntegrateHelper $integrateHelperData,
        Config $productMediaConfig,
        CustomerFactory $customerFactory,
        OrderSyncErrorCollectionFactory $orderErrorCollectionFactory,
        CartRepositoryInterface $quoteRepository,
        OrderFactory $orderFactory
    ) {
        $this->productMediaConfig          = $productMediaConfig;
        $this->customerHelper              = $customerHelper;
        $this->orderCollectionFactory      = $collectionFactory;
        $this->integrateHelperData         = $integrateHelperData;
        $this->customerFactory             = $customerFactory;
        $this->retailHelper                = $retailHelper;
        $this->orderErrorCollectionFactory = $orderErrorCollectionFactory;
        $this->quoteRepository             = $quoteRepository;
        $this->orderFactory                = $orderFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    public function getOrders()
    {
        $searchCriteria = $this->getSearchCriteria();

        if ($searchCriteria->getData('getErrorOrder') && intval($searchCriteria->getData('getErrorOrder')) === 1) {
            return $this->loadOrderError($searchCriteria);
        } else {
            return $this->loadOrders($searchCriteria);
        }
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \ReflectionException
     */
    public function loadOrders(DataObject $searchCriteria)
    {
        $collection = $this->getOrderCollection($searchCriteria);

        $orders = [];
        if (1 < $searchCriteria->getData('currentPage')) {
        } else {
            $storeId = $searchCriteria->getData('storeId');

            /** @var \Magento\Sales\Model\Order $order */
            foreach ($collection as $order) {
                $order         = $this->orderFactory->create()->loadByIncrementId($order->getIncrementId());
                if ($order->getShippingMethod() === 'smstorepickup_smstorepickup') {
                    $quoteId = $order->getQuoteId();
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $this->quoteRepository->get($quoteId);
                    if (!!$quote->getData('pickup_outlet_id')
                        && $quote->getData('pickup_outlet_id') != $quote->getData('outlet_id')) {
                        if ($quote->getData('pickup_outlet_id') != $searchCriteria->getData('outletId')) {
                            continue;
                        }

                    } elseif ($quote->getData('outlet_id') != $searchCriteria->getData('outletId')) {
                        continue;
                    }
                }
                $customerPhone = "";
                $xOrder        = new XOrder($order->getData());
                //$date_start             = $this->timezoneInterface->date($array_date_start[1], null, false);
                $xOrder->setData('created_at', $this->retailHelper->convertTimeDBUsingTimeZone($order->getCreatedAt(), $storeId));
                $xOrder->setData('status', $order->getStatusLabel());
                if ($order->getCustomerId()) {
                    $customer = $this->customerFactory->create()->load($order->getCustomerId());
                    if ($customer->getData('retail_telephone')) {
                        $customerPhone = $customer->getData('retail_telephone');
                    } else {
                        $customerPhone = "";
                    }
                }
                $xOrder->setData(
                    'customer',
                    [
                        'id'    => $order->getCustomerId(),
                        'name'  => $order->getCustomerName(),
                        'email' => $order->getCustomerEmail(),
                        'phone' => $customerPhone,
                    ]
                );

                $xOrder->setData('items', $this->getOrderItemData($order->getItemsCollection()->getItems()));

                if ($billingAdd = $order->getBillingAddress()) {
                    $customerBillingAdd = new CustomerAddress($billingAdd->getData());
                    $xOrder->setData('billing_address', $customerBillingAdd);
                }
                if ($shippingAdd = $order->getShippingAddress()) {
                    $customerShippingAdd = new CustomerAddress($shippingAdd->getData());
                    $xOrder->setData('shipping_address', $customerShippingAdd);
                }
                if ($order->getShippingMethod() === 'smstorepickup_smstorepickup'
                    && is_null($order->getData('retail_status'))) {
                    if (!$order->hasCreditmemos()) {
                        if ($order->canInvoice()) {
                            $xOrder->setData(
                                'retail_status',
                                OrderManagement::RETAIL_ORDER_PARTIALLY_PAID_AWAIT_PICKING
                            );
                        } elseif ($order->canShip()) {
                            $xOrder->setData('retail_status', OrderManagement::RETAIL_ORDER_COMPLETE_AWAIT_PICKING);
                        }
                    } else {
                        if ($order->getState() == Order::STATE_CLOSED) {
                            $xOrder->setData('retail_status', OrderManagement::RETAIL_ORDER_FULLY_REFUND);
                        } else {
                            if ($order->canShip()) {
                                $xOrder->setData(
                                    'retail_status',
                                    OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND_AWAIT_PICKING
                                );
                            }
                        }
                    }
                }
                if ($order->getPayment()->getMethod() == RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE || $order->getShippingMethod() === 'smstorepickup_smstorepickup') {
                    $paymentData = json_decode($order->getPayment()->getAdditionalInformation('split_data'), true);
                    $paymentData = is_array($paymentData) ? $paymentData : [];
                    if (is_array($paymentData)) {
                        $paymentData = array_filter(
                            $paymentData,
                            function ($val) {
                                return is_array($val);
                            }
                        );
                        if ($order->getShippingMethod() === 'smstorepickup_smstorepickup' && !$order->canInvoice() && $order->getData('is_exchange') != 1) {
                            array_push($paymentData, [
                                'title'      => $order->getPayment()->getMethodInstance()->getTitle(),
                                'amount'     => $order->getTotalPaid(),
                                'created_at' => $order->getCreatedAt(),
                                'type'       => $order->getPayment()->getMethodInstance()->getCode()
                            ]);
                        }
                        $xOrder->setData('payment', $paymentData);
                    }
                } else {
                    $xOrder->setData(
                        'payment',
                        [
                            [
                                'title'      => $order->getPayment()->getMethodInstance()->getTitle(),
                                'amount'     => $order->getTotalPaid(),
                                'created_at' => $order->getCreatedAt(),
                                'type'       => $order->getPayment()->getMethodInstance()->getCode()
                            ]
                        ]
                    );
                }
                $xOrder->setData('outlet_id', $order->getData('outlet_id'));
                $xOrder->setData('can_creditmemo', $order->canCreditmemo());
                $xOrder->setData('can_ship', $order->canShip());
                $xOrder->setData('can_invoice', $order->canInvoice());
                $xOrder->setData('is_order_virtual', $order->getIsVirtual());

                $totals = [
                    'shipping_incl_tax'            => floatval($order->getShippingInclTax()),
                    'shipping'                     => floatval($order->getShippingAmount()),
                    'shipping_method'              => floatval($order->getShippingMethod()),
                    'shipping_discount'            => floatval($order->getShippingDiscountAmount()),
                    'shipping_tax_amount'          => floatval($order->getShippingTaxAmount()),
                    'subtotal'                     => floatval($order->getSubtotal()),
                    'subtotal_incl_tax'            => floatval($order->getSubtotalInclTax()),
                    'tax'                          => floatval($order->getTaxAmount()),
                    'discount'                     => floatval($order->getDiscountAmount()),
                    'retail_discount_pert_item'    => floatval($order->getData('discount_per_item')),
                    'grand_total'                  => floatval($order->getGrandTotal()),
                    'total_paid'                   => floatval($order->getTotalPaid()),
                    'total_refunded'               => floatval($order->getTotalRefunded()),
                    'reward_point_discount_amount' => null,
                    'store_credit_discount_amount' => null,
                    'gift_card_discount_amount'    => null,
                    'store_credit_balance'         => floatval($order->getData('store_credit_balance')),
                    'previous_reward_points_balance'    => floatval($order->getData('previous_reward_points_balance')),
                    'reward_points_redeemed'        => floatval($order->getData('reward_points_redeemed')),
                    'reward_points_earned'          => floatval($order->getData('reward_points_earned')),
                    'reward_points_earned_amount'   => floatval($order->getData('reward_points_earned_amount')),
                    'reward_points_refunded'        => floatval($order->getData('reward_points_refunded')),
                ];

                if ($this->integrateHelperData->isIntegrateRP()
                    && $this->integrateHelperData->isAHWRewardPoints()) {
                    $totals['reward_point_discount_amount'] = $order->getData('aw_reward_points_amount');
                }

                if ($this->integrateHelperData->isIntegrateRP()
                    && $this->integrateHelperData->isRewardPointMagento2EE()) {
                    $totals['reward_point_discount_amount'] = -$order->getData('reward_currency_amount');
                }

                if ($this->integrateHelperData->isIntegrateStoreCredit()
                    && $this->integrateHelperData->isExistStoreCreditMagento2EE()) {
                    $totals['store_credit_discount_amount'] = -$order->getData('customer_balance_amount');
                }

                if ($this->integrateHelperData->isIntegrateGC()
                    && $this->integrateHelperData->isAHWGiftCardxist()) {
                    $orderGiftCards = [];
                    if ($order->getExtensionAttributes()) {
                        $orderGiftCards = $order->getExtensionAttributes()
                                                ->getAwGiftcardCodes();
                    }
                    if (count($orderGiftCards) > 0) {
                        $totals['gift_card'] = [];
                        foreach ($orderGiftCards as $giftcard) {
                            array_push(
                                $totals['gift_card'],
                                [
                                    'gift_code'   => $giftcard->getGiftcardCode(),
                                    'giftcard_amount' => floatval(abs($giftcard->getGiftcardAmount())),
                                    'base_giftcard_amount' => floatval(abs($giftcard->getBaseGiftcardAmount()))
                                ]
                            );
                        }
                    }
                }
                if ($this->integrateHelperData->isIntegrateGC()
                    && $this->integrateHelperData->isGiftCardMagento2EE()) {
                    $orderGiftCards = [];
                    if ($order->getData('gift_cards')) {
                        $orderGiftCards = unserialize($order->getData('gift_cards'));
                    }
                    if (count($orderGiftCards) > 0) {
                        $totals['gift_card'] = [];
                        foreach ($orderGiftCards as $giftCard) {
                            array_push(
                                $totals['gift_card'],
                                [
                                    'gift_code'   => $giftCard['c'],
                                    'giftcard_amount' => floatval(abs($giftCard['a'])),
                                    'base_giftcard_amount' => floatval(abs($giftCard['ba']))
                                ]
                            );
                        }
                    }
                }

                $xOrder->setData('totals', $totals);
                $orders[] = $xOrder;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($orders)
                    ->setTotalCount($collection->getTotalCount())
                    ->setMessageError(OrderManagement::$MESSAGE_ERROR)
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->getOutput();
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Exception
     */
    protected function getOrderCollection(DataObject $searchCriteria)
    {
        /** @var  \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->orderCollectionFactory->create();
        $storeId = $searchCriteria->getData('storeId');
        if (!$searchCriteria->getData('isSearchOnline')) {
            $outletId = $searchCriteria->getData('outletId');
            if (!!$outletId && !$searchCriteria->getData('searchString')) {
               // $collection->getSelect()->where(new Zend_Db_Expr("1"));
                $collection->addFieldToFilter(
                    array('outlet_id', 'shipping_method', 'pickup_outlet_id'),
                    array(
                        array('eq' => $outletId),
                        array('eq' => 'smstorepickup_smstorepickup'),
                        array('eq' => $outletId),
                    )
                );
            }

            if (is_null($storeId)) {
                throw new Exception("Please define storeId when pull order");
            } else {
                $collection->addFieldToFilter(
                    array('store_id', 'shipping_method'),
                    array(
                        array('eq' => $storeId),
                        array('eq' => 'smstorepickup_smstorepickup')
                    )
                );
            }
        }
        if ($entityId = $searchCriteria->getData('entity_id')) {
            $arrEntityId = explode(",", $entityId);
            $collection->addFieldToFilter('entity_id', ["in" => $arrEntityId]);
        }

        $collection
            ->setOrder('entity_id')
            ->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'))
            ->setPageSize(
                is_nan($searchCriteria->getData('pageSize')) ?
                    DataConfig::PAGE_SIZE_LOAD_DATA :
                    $searchCriteria->getData('pageSize')
            );
        if ($dateFrom = $searchCriteria->getData('dateFrom')) {
            $collection->getSelect()
                       ->where('created_at >= ?', $dateFrom);
        }
        if ($dateTo = $searchCriteria->getData('dateTo')) {
            $collection->getSelect()
                       ->where('created_at <= ?', $dateTo . ' 23:59:59');
        }
        if ($searchString = $searchCriteria->getData('searchString')) {
            $fieldSearch      = ['retail_id', 'customer_email', 'increment_id'];
            $fieldSearchValue = [
                ['like' => '%' . $searchString . '%'],
                ['like' => '%' . $searchString . '%'],
                ['like' => '%' . $searchString . '%']
            ];
            $arrString        = explode(' ', $searchString);
            foreach ($arrString as $customerNameSearchValue) {
                $fieldSearch[]      = 'customer_firstname';
                $fieldSearchValue[] = ['like' => '%' . $customerNameSearchValue . '%'];
                $fieldSearch[]      = 'customer_lastname';
                $fieldSearchValue[] = ['like' => '%' . $customerNameSearchValue . '%'];
            }
            $collection->addFieldToFilter($fieldSearch, $fieldSearchValue);
        }

        return $collection;
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return array
     * @throws \ReflectionException
     */
    public function loadOrderError(DataObject $searchCriteria)
    {
        $collection = $this->getOrderErrorCollection($searchCriteria);
        $orders = [];
        if (1 < $searchCriteria->getData('currentPage')) {
        } else {
            foreach ($collection as $order) {
                $orderData = json_decode($order['order_offline'], true);
                if (is_array($orderData)) {
                    if (isset($orderData['id'])) {
                        unset($orderData['id']);
                    }
                    $orders[] = $orderData;
                }
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($orders)
                    ->getOutput();
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getOrderErrorCollection(DataObject $searchCriteria)
    {
        $collection = $this->orderErrorCollectionFactory->create();
        $storeId    = $searchCriteria->getData('storeId');
        if (is_null($storeId)) {
            throw new Exception("Please define storeId when pull order");
        }

        $collection->addFieldToFilter('store_id', $storeId);

        if ($dateFrom = $searchCriteria->getData('dateFrom')) {
            $collection->getSelect()
                       ->where('created_at >= ?', $dateFrom);
        }
        if ($dateTo = $searchCriteria->getData('dateTo')) {
            $collection->getSelect()
                       ->where('created_at <= ?', $dateTo . ' 23:59:59');
        }

        return $collection;
    }

    /**
     * @param $items
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getOrderItemData($items)
    {
        $itemData = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $_item = new XOrder\XOrderItem($item->getData());
            $_item->setData('isChildrenCalculated', $item->isChildrenCalculated());
            if (!$item->getProduct()
                || is_null($item->getProduct()->getImage())
                || $item->getProduct()->getImage() == 'no_selection'
                || !$item->getProduct()->getImage()
            ) {
                $_item->setData('origin_image', null);
            } else {
                $_item->setData(
                    'origin_image',
                    $this->productMediaConfig->getMediaUrl($item->getProduct()->getImage())
                );
                $_item->setData(
                    'origin_image',
                    $this->productMediaConfig->getMediaUrl($item->getProduct()->getImage())
                );
            }

            $children = [];
            if ($item->getChildrenItems() && $item->getProductType() == 'bundle') {
                foreach ($item->getChildrenItems() as $childrenItem) {
                    $_child = new XOrder\XOrderItem($childrenItem->getData());
                    if (is_null($childrenItem->getProduct()->getImage())
                        || $childrenItem->getProduct()->getImage() == 'no_selection'
                        || !$childrenItem->getProduct()->getImage()
                    ) {
                        $_child->setData('origin_image', null);
                    } else {
                        $_child->setData(
                            'origin_image',
                            $this->productMediaConfig->getMediaUrl($childrenItem->getProduct()->getImage())
                        );
                    }

                    $children[] = $_child->getOutput();
                }
            }

            $_item->setData('children', $children);
            $_item->setData('buy_request', $item->getBuyRequest()->getData());
            $itemData[] = $_item->getOutput();
        }

        return $itemData;
    }
}
