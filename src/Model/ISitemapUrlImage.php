<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ISitemapUrlImage
{
    public function getUrl(): string;

    public function setUrl(string $url): void;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getCaption(): string;

    public function setCaption(string $caption): void;
}
