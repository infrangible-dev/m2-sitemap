<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Block\Adminhtml\Sitemap;

use Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid\Column\Renderer\Link;
use Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid\Column\Renderer\Stores;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Grid extends \Infrangible\BackendWidget\Block\Grid
{
    protected function prepareCollection(AbstractDb $collection): void
    {
    }

    /**
     * @throws \Exception
     */
    protected function prepareFields(): void
    {
        $this->addTextColumn(
            'filename',
            __('File Name')->render()
        );
        $this->addTextColumnWithRenderer(
            'store_ids',
            __('Stores')->render(),
            Stores::class
        );
        $this->addDatetimeColumn(
            'created_at',
            __('Created At')->render()
        );
        $this->addTextColumnWithRenderer(
            'link',
            __('Link')->render(),
            Link::class
        );
    }

    protected function getHiddenFieldNames(): array
    {
        return [];
    }

    protected function addActionColumn(): void
    {
        $this->addAction(
            'generate',
            __('Generate')->render(),
            'infrangible_sitemap/sitemap/generate',
            true
        );

        parent::addActionColumn();
    }
}
