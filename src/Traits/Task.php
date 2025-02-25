<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Traits;

use Infrangible\Sitemap\Task\Sitemap;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait Task
{
    protected function getTaskName(): string
    {
        return 'sitemap';
    }

    protected function getClassName(): string
    {
        return Sitemap::class;
    }
}
