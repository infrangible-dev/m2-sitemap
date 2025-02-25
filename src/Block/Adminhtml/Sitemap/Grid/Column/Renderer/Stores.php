<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Stores extends AbstractRenderer
{
    /** @var \Infrangible\Core\Helper\Stores */
    protected $storeHelper;

    public function __construct(Context $context, \Infrangible\Core\Helper\Stores $storeHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->storeHelper = $storeHelper;
    }

    /**
     * @throws \Exception
     */
    public function render(DataObject $row): string
    {
        $column = $this->getColumn();

        $value = $row->getData($column->getData('index'));

        $storeIds = explode(
            ',',
            $value
        );

        $data = [];

        foreach ($this->storeHelper->getStores() as $store) {
            $storeId = $store->getId();

            if (in_array(
                $storeId,
                $storeIds
            )) {
                $storeGroup = $store->getGroup();
                $website = $storeGroup->getWebsite();

                $data[ $website->getName() ][ $storeGroup->getName() ][] = $store->getName();
            }
        }

        $result = '<div>';

        foreach ($data as $websiteName => $websiteStoreGroups) {
            $result .= sprintf(
                '%s<br/>',
                $websiteName
            );

            foreach ($websiteStoreGroups as $storeGroupName => $storeNames) {
                $result .= sprintf(
                    '%s%s<br/>',
                    str_repeat(
                        '&nbsp;',
                        3
                    ),
                    $storeGroupName
                );

                foreach ($storeNames as $storeName) {
                    $result .= sprintf(
                        '%s%s<br/>',
                        str_repeat(
                            '&nbsp;',
                            6
                        ),
                        $storeName
                    );
                }
            }
        }

        $result .= '</div>';

        return $result;
    }
}
