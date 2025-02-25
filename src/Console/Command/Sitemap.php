<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Console\Command;

use Infrangible\Task\Console\Command\Task;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Sitemap extends Task
{
    use \Infrangible\Sitemap\Traits\Task;

    protected function getClassName(): string
    {
        return Script\Sitemap::class;
    }

    protected function getCommandDescription(): string
    {
        return 'Generate sitemap(s)';
    }
}
