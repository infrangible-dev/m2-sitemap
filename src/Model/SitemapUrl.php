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

    /** @var ISitemapUrlImage[] */
    private $images = [];

    /** @var ISitemapUrlDataObject[] */
    private $dataObjects = [];

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

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function addImage(ISitemapUrlImage $image): void
    {
        $this->images[] = $image;
    }

    public function getDataObjects(): array
    {
        return $this->dataObjects;
    }

    public function setDataObjects(array $dataObjects): void
    {
        $this->dataObjects = $dataObjects;
    }

    public function addDataObject(ISitemapUrlDataObject $dataObject): void
    {
        $this->dataObjects[] = $dataObject;
    }

    public function isValid(): bool
    {
        return $this->url !== null;
    }
}
