<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Controller\Adminhtml\Sitemap;

use Infrangible\Sitemap\Block\Adminhtml\Sitemap\Grid\Container;
use Infrangible\Sitemap\Traits\Controller;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Index extends \Infrangible\BackendWidget\Controller\Backend\Object\Index
{
    use Controller;

    protected function getGridBlockType(): string
    {
        return Container::class;
    }
}
