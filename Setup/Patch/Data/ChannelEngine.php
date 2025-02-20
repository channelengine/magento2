<?php namespace ChannelEngine\Magento2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

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

    /**
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;

        $this->productAttributes = [
            'ce_updated_at' => [
                'type' => 'datetime',
                'label' => 'CE last product update',
                'input' => 'date',
                'required' => false,
                'user_defined' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

//        foreach ($this->productAttributes as $attr => $config) {
//            $eavSetup->addAttribute(Product::ENTITY, $attr, $config);
//        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            'ce_updated_at',
            [
                'type' => 'datetime',
                'backend' => '',
                'frontend' => '',
                'label' => 'ChannelEngine last product update',
                'input' => 'date',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => ''
            ]
        );

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