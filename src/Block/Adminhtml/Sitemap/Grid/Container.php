<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Container extends \Infrangible\BackendWidget\Block\Grid\Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->addButton(
            'generate',
            [
                'label'   => __('Generate All'),
                'onclick' => sprintf(
                    "confirmSetLocation('Are you sure?', '%s')",
                    $this->_urlBuilder->getUrl('infrangible_sitemap/sitemap/all')
                ),
                'class'   => 'add primary'
            ]
        );
    }
}
