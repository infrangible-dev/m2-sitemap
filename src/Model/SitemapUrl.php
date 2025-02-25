<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class SitemapUrl implements ISitemapUrl
{
    /** @var string */
    private $url;

    /** @var string */
    private $lastModified;

    /** @var string */
    private $changeFrequency;

    /** @var string */
    private $priority;

    public function getChangeFrequency(): string
    {
        return $this->changeFrequency;
    }

    public function setChangeFrequency(string $changeFrequency): void
    {
        $this->changeFrequency = $changeFrequency;
    }

    public function getLastModified(): string
    {
        return $this->lastModified;
    }

    public function setLastModified(string $lastModified): void
    {
        $this->lastModified = $lastModified;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
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
