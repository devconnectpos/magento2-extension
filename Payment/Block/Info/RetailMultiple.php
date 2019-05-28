<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/12/2016
 * Time: 09:19
 */

namespace SM\Payment\Block\Info;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info;

/**
 * Class RetailMultiple
 *
 * @package SM\Payment\Block\Info
 */
class RetailMultiple extends Info
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var
     */
    protected $multiplePayment;

    /**
     * RetailMultiple constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_template = 'SM_Payment::info/retailmultiple.phtml';
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @param $code
     * @param $field
     *
     * @return mixed
     */
    public function getPaymentMethodConfigData($code, $field)
    {
        $path = 'payment/' . $code . '/' . $field;

        return $this->_scopeConfig->getValue($path, 'default', $this->multiplePayment['store_id']);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMultiplePaymentData()
    {
        if (is_null($this->multiplePayment)) {
            $this->convertAdditionalData();
        }

        return $this->multiplePayment;
    }

    /**
     * @param $price
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function formatPrice($price)
    {
        $order = $this->getInfo()->getOrder();
        return $this->priceCurrency->format(
            $price,
            true,
            2,
            null,
            $this->_storeManager->getStore($order->getStoreId())->getCurrentCurrencyCode()
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function convertAdditionalData()
    {
        $this->multiplePayment = json_decode($this->getInfo()->getAdditionalInformation('split_data'), true);
        $this->multiplePayment = array_filter(
            $this->multiplePayment,
            function ($val) {
                return is_array($val);
            }
        );

        return $this;
    }

    /**
     * @return array
     */
    public function filterFields()
    {
        return ['store_id', 'method_title'];
    }

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('SM_Payment::info/pdf/retailmultiple.phtml');

        return $this->toHtml();
    }
}
