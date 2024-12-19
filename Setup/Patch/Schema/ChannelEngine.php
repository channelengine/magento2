<?php namespace ChannelEngine\Magento2\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Eav\Setup\EavSetupFactory;

class ChannelEngine implements SchemaPatchInterface
{
    private const QUOTE_ENTITY = 'quote';
    private const ORDER_ITEM_ENTITY = 'order_item';
    private const ORDER_GRID_TABLE = 'sales_order_grid';

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
        QuoteSetupFactory $quoteSetupFactory        
    ) {
        $this->setup = $setup;
        $this->integrationManager = $integrationManager;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        
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
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->setup->startSetup();

        $conn = $this->setup->getConnection();
        $orderGridTable = $this->setup->getTable(self::ORDER_GRID_TABLE);

        // Install attributes
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $this->setup]);
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->setup]);

        foreach ($this->orderAttributes as $attr => $config) {
            $conn->addColumn($orderGridTable, $attr, [
                'type' => $config['type'],
                'length' => 255,
                'nullable' => true,
                'comment' => $config['label']
            ]);

            $salesSetup->addAttribute(Order::ENTITY, $attr, $config);
            $quoteSetup->addAttribute(self::QUOTE_ENTITY, $attr, $config);
        }

        foreach ($this->orderLineAttributes as $attr => $config) {
            $salesSetup->addAttribute(self::ORDER_ITEM_ENTITY, $attr, $config);
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
