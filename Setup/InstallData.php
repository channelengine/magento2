<?php namespace ChannelEngine\Magento2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;

class InstallData implements InstallDataInterface
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
    * @param ConfigBasedIntegrationManager $integrationManager
    */
    public function __construct(ConfigBasedIntegrationManager $integrationManager, SalesSetupFactory $salesSetupFactory)
    {
        $this->integrationManager = $integrationManager;
        $this->salesSetupFactory = $salesSetupFactory;

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
    * {@inheritdoc}
    */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $conn = $setup->getConnection();
        $orderGridTable = $setup->getTable('sales_order_grid');

        // Install attributes
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        foreach ($this->orderAttributes as $attr => $config)
        {
            $conn->addColumn($orderGridTable, $attr, [
                'type' => $config['type'],
                'length' => 255,
                'nullable' => true,
                'comment' => $config['label']
            ]);
            $salesSetup->addAttribute(Order::ENTITY, $attr, $config);
        }

        foreach ($this->orderLineAttributes as $attr => $config)
        {
            $salesSetup->addAttribute('order_item', $attr, $config);
        }

        // Install integrations
        $this->integrationManager->processIntegrationConfig(['ChannelEngine']);

        $setup->endSetup();
    }
}