<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Setup;

use Exception;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\EntityType;
use Infrangible\Core\Helper\Setup;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallData implements InstallDataInterface
{
    /** @var CategorySetupFactory */
    protected $categorySetupFactory;

    /** @var Database */
    protected $databaseHelper;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var EntityType */
    protected $entityTypeHelper;

    /** @var Setup */
    protected $setupHelper;

    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        Database $databaseHelper,
        Attribute $attributeHelper,
        EntityType $entityTypeHelper,
        Setup $setupHelper
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->databaseHelper = $databaseHelper;
        $this->attributeHelper = $attributeHelper;
        $this->entityTypeHelper = $entityTypeHelper;
        $this->setupHelper = $setupHelper;
    }

    /**
     * @throws Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $eavSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $attributeId = $eavSetup->getAttributeId(
            Product::ENTITY,
            'exclude_from_sitemap'
        );

        if (! $attributeId) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'exclude_from_sitemap',
                [
                    'label'                         => 'Exclude from Sitemap',
                    'type'                          => 'int',
                    'source'                        => Boolean::class,
                    'input'                         => 'boolean',
                    'default'                       => '0',
                    'global'                        => ScopedAttributeInterface::SCOPE_STORE,
                    'visible'                       => true,
                    'searchable'                    => false,
                    'filterable'                    => false,
                    'comparable'                    => false,
                    'visible_on_front'              => false,
                    'wysiwyg_enabled'               => false,
                    'html_allowed_on_front'         => false,
                    'is_visible_in_advanced_search' => false,
                    'is_filterable_in_search'       => false,
                    'used_in_product_listing'       => false,
                    'used_for_sort_by'              => false,
                    'is_configurable'               => false,
                    'used_for_promo_rules'          => false,
                    'required'                      => false,
                    'user_defined'                  => false
                ]
            );

            $attributeSetCollection = $this->attributeHelper->getAttributeSetCollection();

            $attributeSetCollection->setEntityTypeFilter($this->entityTypeHelper->getProductEntityTypeId());

            /** @var Set $attributeSet */
            foreach ($attributeSetCollection as $attributeSet) {
                $attributeSortOrder = 100;

                $groupId = $attributeSet->getDefaultGroupId();

                if ($groupId) {
                    $this->setupHelper->addProductAttributeToSetAndGroup(
                        $eavSetup,
                        'exclude_from_sitemap',
                        strval($attributeSet->getId()),
                        strval($groupId),
                        $attributeSortOrder
                    );
                }
            }
        }

        $attributeId = $eavSetup->getAttributeId(
            Category::ENTITY,
            'exclude_from_sitemap'
        );

        if (! $attributeId) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'exclude_from_sitemap',
                [
                    'label'                         => 'Exclude from Sitemap',
                    'type'                          => 'int',
                    'source'                        => Boolean::class,
                    'input'                         => 'select',
                    'default'                       => '0',
                    'global'                        => ScopedAttributeInterface::SCOPE_STORE,
                    'visible'                       => true,
                    'searchable'                    => false,
                    'filterable'                    => false,
                    'comparable'                    => false,
                    'visible_on_front'              => false,
                    'wysiwyg_enabled'               => false,
                    'html_allowed_on_front'         => false,
                    'is_visible_in_advanced_search' => false,
                    'is_filterable_in_search'       => false,
                    'used_in_product_listing'       => false,
                    'used_for_sort_by'              => false,
                    'is_configurable'               => false,
                    'used_for_promo_rules'          => false,
                    'required'                      => false,
                    'user_defined'                  => false
                ]
            );

            $attributeSetCollection = $this->attributeHelper->getAttributeSetCollection();

            $attributeSetCollection->setEntityTypeFilter($this->entityTypeHelper->getCategoryEntityTypeId());

            /** @var Set $attributeSet */
            foreach ($attributeSetCollection as $attributeSet) {
                $attributeSortOrder = 100;

                $groupId = $attributeSet->getDefaultGroupId();

                if ($groupId) {
                    $this->setupHelper->addProductAttributeToSetAndGroup(
                        $eavSetup,
                        'exclude_from_sitemap',
                        strval($attributeSet->getId()),
                        strval($groupId),
                        $attributeSortOrder
                    );
                }
            }
        }
    }
}
