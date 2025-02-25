<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (! $connection->tableColumnExists(
            $setup->getTable('cms_page'),
            'exclude_from_sitemap'
        )) {
            $connection->addColumn(
                $setup->getTable('cms_page'),
                'exclude_from_sitemap',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 3,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Exclude from Sitemap'
                ]
            );
        }

        $setup->endSetup();
    }
}
