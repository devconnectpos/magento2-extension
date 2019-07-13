<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Setting\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->dummySettingCategories($setup);
            $this->addUseProductOnlineModeSetting($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummyIntergrateGCExtension($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummySelectSeller($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addUseMagentoRecommendation($setup);
            $this->addFeaturedProductRecommendation($setup);
            $this->addOtherSettingSecondScreen($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addIntegrateStoreCredit($setup);
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $this->addShowSaleTagOnProductSetting($setup);
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $this->dummySettingVeriface($setup);
        }

        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $this->dummySettingEnableDefaultCategory($setup);
        }
    }

    protected function dummySettingCategories(ModuleDataSetupInterface $setup)
    {
        $configData  = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/use_large_categories",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/display_selected_category",
                    'value'    => 'sub_categories_product',
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }


    protected function dummySelectSeller(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');

        $data = [
            'path'     => "xretail/pos/allow_select_seller",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function dummyIntergrateGCExtension(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/integrate_gc",
            'value'    => "none",
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addUseProductOnlineModeSetting(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/use_product_online_mode",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addUseMagentoRecommendation(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/use_magento_recommendation",
            'value'    => 1,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addFeaturedProductRecommendation(ModuleDataSetupInterface $setup) {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/featured_product_recommendation",
            'value'    => json_encode([]),
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addOtherSettingSecondScreen(ModuleDataSetupInterface $setup) {
        $configData  = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/screensaver_mode_after",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/change_screensaver_every",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/picture_video_screensaver",
                    'value'    => json_encode([]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }

    protected function addIntegrateStoreCredit(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/integrate_store_credit",
            'value'    => "none",
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addShowSaleTagOnProductSetting(ModuleDataSetupInterface $setup)
    {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/show_sales_tag",
            'value'    => 1,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function dummySettingVeriface(ModuleDataSetupInterface $setup)
    {
        $configData  = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/veriface",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/veriface_username",
                    'value'    => '',
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/veriface_password",
                    'value'    => '',
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/veriface_token",
                    'value'    => '',
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }

    protected function dummySettingEnableDefaultCategory(ModuleDataSetupInterface $setup)
    {
        $configData  = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/enable_default_category",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }
}
