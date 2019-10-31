<?php namespace ChannelEngine\Magento2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    const QUOTE_ENTITY = 'quote';

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
    * @param ConfigBasedIntegrationManager $integrationManager
    */
    public function __construct(ConfigBasedIntegrationManager $integrationManager, SalesSetupFactory $salesSetupFactory, QuoteSetupFactory $quoteSetupFactory, EavSetupFactory $eavSetupFactory)
    {
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
    * {@inheritdoc}
    */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $conn = $setup->getConnection();
        $orderGridTable = $setup->getTable('sales_order_grid');

        // Install attributes
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach ($this->orderAttributes as $attr => $config)
        {
            $conn->addColumn($orderGridTable, $attr, [
                'type' => $config['type'],
                'length' => 255,
                'nullable' => true,
                'comment' => $config['label']
            ]);

            $salesSetup->addAttribute(Order::ENTITY, $attr, $config);
            $quoteSetup->addAttribute(self::QUOTE_ENTITY, $attr, $config);
        }

        foreach ($this->orderLineAttributes as $attr => $config)
        {
            $salesSetup->addAttribute('order_item', $attr, $config);
        }

        foreach ($this->productAttributes as $attr => $config)
        {
            $eavSetup->addAttribute(Product::ENTITY, $attr, $config);
        }

        // Install integrations
        $this->integrationManager->processIntegrationConfig(['ChannelEngine']);

        $setup->endSetup();
    }
}