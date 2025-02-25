<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Controller\Adminhtml\Sitemap;

use Infrangible\Sitemap\Traits\Task;
use Infrangible\Task\Controller\Adminhtml\Run;
use Infrangible\Task\Task\Base;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Generate extends Run
{
    use Task;

    protected function getTaskResourceId(): string
    {
        return 'Infrangible_Sitemap::infrangible_sitemap';
    }

    public function getTask(): Base
    {
        $task = parent::getTask();

        $task->setFileName($this->getRequest()->getParam('filename'));

        return $task;
    }

    protected function getRedirectPath(): ?string
    {
        return 'infrangible_sitemap/sitemap/index';
    }

    protected function isAddResultMessage(): bool
    {
        return true;
    }
}
