<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Traits;

use Infrangible\Sitemap\Model\Collection;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
trait Controller
{
    protected function getResourceKey(): string
    {
        return 'Infrangible_Sitemap::infrangible_sitemap';
    }

    protected function getModuleKey(): string
    {
        return 'Infrangible_Sitemap';
    }

    protected function getObjectName(): string
    {
        return 'Sitemap';
    }

    protected function getObjectField(): ?string
    {
        return 'filename';
    }

    protected function getCollectionClass(): ?string
    {
        return Collection::class;
    }

    protected function getMenuKey(): string
    {
        return 'Infrangible_Sitemap::infrangible_sitemap_manage';
    }

    protected function getTitle(): string
    {
        return __('Sitemap')->render();
    }

    protected function allowAdd(): bool
    {
        return false;
    }

    protected function allowEdit(): bool
    {
        return false;
    }

    protected function allowView(): bool
    {
        return false;
    }

    protected function allowDelete(): bool
    {
        return false;
    }

    protected function showFiltersButton(): bool
    {
        return false;
    }

    protected function showColumnsButton(): bool
    {
        return false;
    }
}