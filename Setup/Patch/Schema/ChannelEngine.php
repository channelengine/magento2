<?php namespace ChannelEngine\Magento2\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

class ChannelEngine implements SchemaPatchInterface
{
    private const QUOTE_ENTITY = 'quote';

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var ConfigBasedIntegrationManager
     */
    private $integrationManager;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @var array|array[]
     */
    private $orderAttributes;
    /**
     * @var array|array[]
     */
    private $orderLineAttributes;
    /**
     * @var array|array[]
     */
    private $productAttributes;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ConfigBasedIntegrationManager $integrationManager
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ConfigBasedIntegrationManager $integrationManager,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->integrationManager = $integrationManager;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;

        $this->orderAttributes = [
            'ce_id' => [
                'type' => Table::TYPE_INTEGER,
                'visible' => true,
                'required' => false,
                'label' => 'CE Order ID'
            ],
            'ce_channel_order_no' => [
                'type' => Table::TYPE_TEXT,
                'visible' => true,
                'required' => false,
                'label' => 'CE Channel Order No'
            ],
            'ce_channel_name' => [
                'type' => Table::TYPE_TEXT,
                'visible' => true,
                'required' => false,
                'label' => 'CE Channel Name'
            ]
        ];
        $this->orderLineAttributes = [
            'ce_id' => [
                'type' => Table::TYPE_INTEGER,
                'visible' => true,
                'required' => false,
                'label' => 'CE Order Line ID'
            ]
        ];
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

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->setup->startSetup();

        $conn = $this->setup->getConnection();
        $orderGridTable = $this->setup->getTable('sales_order_grid');

        // Install attributes
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $this->setup]);
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        foreach ($this->orderAttributes as $attr => $config) {
            $conn->addColumn($orderGridTable, $attr, [
                'type' => $config['type'],
                'length' => 255,
                'nullable' => true,
                'comment' => $config['label']
            ]);

            $salesSetup->addAttribute(Order::ENTITY, $attr, $config);
            $quoteSetup->addAttribute("quote", $attr, $config);
        }

        foreach ($this->orderLineAttributes as $attr => $config) {
            $salesSetup->addAttribute('order_item', $attr, $config);
        }

        foreach ($this->productAttributes as $attr => $config) {
            $eavSetup->addAttribute('catalog_product', $attr, $config);
        }

        // Install integrations
        $this->integrationManager->processIntegrationConfig(['ChannelEngine']);

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
