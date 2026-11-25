<?php

declare(strict_types=1);

namespace ChannelEngine\Magento2\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Quote\Setup\QuoteSetupFactory;

class AddTaxColumns implements SchemaPatchInterface
{
    private const QUOTE_ENTITY = 'quote';

    private const QUOTE_ITEM_ENTITY = 'quote_item';

    private const QUOTE_COLUMNS = [
        'ce_shipping_tax_rate' => [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'required' => false,
            'label' => 'CE Shipping Tax Rate'
        ],
        'ce_price_includes_tax' => [
            'type' => Table::TYPE_SMALLINT,
            'unsigned' => true,
            'visible' => false,
            'required' => false,
            'label' => 'CE Price Includes Tax'
        ]
    ];

    private const QUOTE_ITEM_COLUMNS = [
        'ce_tax_rate' => [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'required' => false,
            'label' => 'CE Tax Rate'
        ]
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->setup = $setup;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->setup->startSetup();

        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->setup]);

        foreach (self::QUOTE_COLUMNS as $column => $config) {
            $quoteSetup->addAttribute(self::QUOTE_ENTITY, $column, $config);
        }

        foreach (self::QUOTE_ITEM_COLUMNS as $column => $config) {
            $quoteSetup->addAttribute(self::QUOTE_ITEM_ENTITY, $column, $config);
        }

        $this->setup->endSetup();

        return $this;
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
        return [ChannelEngine::class];
    }
}
