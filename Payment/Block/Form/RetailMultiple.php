<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 07/12/2016
 * Time: 09:19
 */

namespace SM\Payment\Block\Form;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Form;

class RetailMultiple extends Form
{
    /**
     * RetailMultiple constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        $this->_template = 'SM_Payment::form/retailmultiple.phtml';
        parent::__construct($context, $data);
    }
}
