<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/3/17
 * Time: 10:41 AM
 */

namespace SM\Email\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class EmailSender
 *
 * @package SM\Email
 */
class EmailSender extends AbstractHelper
{

    /**
     *
     */
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'xpos/email/pos_receipt_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var string
     */
    protected $tempId;

    /**
     * EmailSender constructor.
     *
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $context;
        parent::__construct($context);
        $this->storeManager     = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @param $xmlPath
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * @param $emailTemplateVariables
     * @param $senderInfo
     * @param $receiverInfo
     *
     * @return $this
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->transportBuilder->setTemplateIdentifier($this->tempId)
                                ->setTemplateOptions(
                                    [
                                        /* here you can defile area and store of template for which you prepare it */
                                        'area'  => Area::AREA_ADMINHTML,
                                        'store' => $this->storeManager->getStore()->getId(),
                                    ]
                                )
                                ->setTemplateVars($emailTemplateVariables)
                                ->setFrom($senderInfo)
                                ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    /**
     * @param $emailTemplateVariables
     * @param $senderInfo
     * @param $receiverInfo
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendEmailOrder($emailTemplateVariables, $receiverInfo, $senderInfo = null, $tempId)
    {
        if (is_null($senderInfo)) {
            $transEmail = $this->getConfigValue('trans_email/ident_sales/email', $this->getStore()->getId());
            $senderInfo = array(
                'email' => $transEmail,
                'name' => $this->getConfigValue('trans_email/ident_sales/name', $this->getStore()->getId())
            );
        }
        $this->tempId = $tempId;
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
