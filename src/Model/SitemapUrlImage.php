<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class SitemapUrlImage implements ISitemapUrlImage
{
    /** @var string */
    private $url;

    /** @var string */
    private $title;

    /** @var string */
    private $caption;

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
