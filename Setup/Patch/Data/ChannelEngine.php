<?php

namespace ChannelEngine\Magento2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;

class ChannelEngine implements DataPatchInterface
{
    private $moduleDataSetup;
    protected $eavSetupFactory;
    private $productAttributes;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;

        $this->productAttributes = [
            'ce_updated_at' => [
                'type' => 'datetime',
                'visible' => false,
                'input' => 'date',
                'required' => false,
                'user_defined' => false,
                'default' => '2019-01-01 00:00:00',
                'global' => 1,
                'label' => 'CE last product update'
            ]
        ];
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        foreach ($this->productAttributes as $attr => $config) {
            $eavSetup->addAttribute(Product::ENTITY, $attr, $config);
        }

        $this->setup->endSetup();
    }
}
