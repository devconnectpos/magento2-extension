<?php

namespace SM\Payment\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class RetailPayment extends AbstractModel implements RetailPaymentInterface, IdentityInterface
{

    const CACHE_TAG                           = 'sm_payment';
    const GIFT_CARD_PAYMENT_TYPE              = 'gift_card';
    const REFUND_GC_PAYMENT_TYPE              = 'refund_gift_card';
    const REWARD_POINT_PAYMENT_TYPE           = 'reward_point';
    const PAYPAL_PAYMENT_TYPE                 = 'paypal';
    const IZETTLE_PAYMENT_TYPE                = 'izettle';
    const ROUNDING_CASH                       = 'rounding_cash';
    const PAYMENT_EXPRESS                     = 'payment_express';
    const AUTHORIZE_NET                       = 'authorize_net';
    const USAEPAY                             = 'usaepay';
    const MONERIS                             = 'moneris';
    const CARDKNOX                            = 'cardknox';
    const STORE_CREDIT_PAYMENT_TYPE           = 'store_credit';
    const REFUND_TO_STORE_CREDIT_PAYMENT_TYPE = 'refund_to_store_credit';

    protected function _construct()
    {
        $this->_init('SM\Payment\Model\ResourceModel\RetailPayment');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
