<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Link extends AbstractRenderer
{
    public function render(DataObject $row): string
    {
        $column = $this->getColumn();

        $value = $row->getData($column->getData('index'));

        return $value ? sprintf(
            '<a title="%s" href="%s" target="_blank">%s</a>',
            __('View'),
            $this->_escaper->escapeHtml($value),
            $this->_escaper->escapeHtml($value)
        ) : '';
    }
}
