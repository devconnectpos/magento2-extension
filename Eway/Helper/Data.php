<?php
/**
 * Created by Nomad
 * Date: 6/12/19
 * Time: 4:31 PM
 */

namespace SM\Eway\Helper;


use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;

class Data
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleListInterface $moduleList
    ) {

        $this->objectManager = $objectManager;
        $this->moduleList = $moduleList;
    }

    public function checkEwayRapidSdk()
    {
        return class_exists('Eway\Rapid');
    }
}
