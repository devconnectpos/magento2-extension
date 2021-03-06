<?php
namespace SM\Setting\Repositories\SettingManagement;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Tax
 *
 * @package SM\Setting\Repositories\SettingManagement
 */
class Store extends AbstractSetting implements SettingInterface
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Tax constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $date
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $date
    ) {
        $this->date = $date;
        parent::__construct($scopeConfig);
    }

    /**
     * @var string
     */
    protected $CODE = "store";

    /**
     * Retrieves global timezone
     *
     * @param bool $isMysql
     *
     * @return string
     */
    public function getTimezone($isMysql = false)
    {

        $foo = $this->date;

        $gmtOffset = $foo->calculateOffset(
            $this->getScopeConfig()->getValue(
                'general/locale/timezone',
                ScopeInterface::SCOPE_STORE,
                $this->getStore()
            )
        );
        if ($isMysql) {
            $offsetInt = -$foo->getGmtOffset();
            $offset    = ($offsetInt >= 0 ? '+' : '-') . sprintf('%02.0f', round(abs($offsetInt / 3600))) . ':'
                         . (sprintf('%02.0f', abs(round((abs($offsetInt) - round(abs($offsetInt / 3600)) * 3600) / 60))));
            return $offset;
        } else {
            // M2 luu timezone khac voi M1
            return -$gmtOffset;
        }
    }

    /**
     * @return array
     */
    public function build()
    {
        // TODO: Implement build() method.
        return [
           "time_zone" => $this->getTimezone()
        ];
    }
}
