<?php

namespace ChannelEngine\Magento2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class ChannelEngine implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var array|array[]
     */
    private $productAttributes;

    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;

        $this->productAttributes = [
            'ce_updated_at' => [
                'type' => Table::TYPE_DATETIME,
                'visible' => false,
                'input' => 'date',
                'required' => false,
                'user_defined' => false,
                'default' => '2019-01-01 00:00:00',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => 'CE last product update'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        foreach ($this->productAttributes as $attr => $config) {
            $eavSetup->addAttribute(Product::ENTITY, $attr, $config);
        }

        $this->setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
    
    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
