<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ISitemapUrl
{
    public function getUrl(): string;

    public function setUrl(string $url): void;

    public function getLastModified(): string;

    public function setLastModified(string $lastModified): void;

    public function getChangeFrequency(): string;

    public function setChangeFrequency(string $changeFrequency): void;

    public function getPriority(): string;

    public function setPriority(string $priority): void;

    /**
     * @return ISitemapUrlImage[]
     */
    public function getImages(): array;

    /**
     * @param ISitemapUrlImage[] $images
     */
    public function setImages(array $images): void;

    public function addImage(ISitemapUrlImage $image): void;

    /**
     * @return ISitemapUrlDataObject[]
     */
    public function getDataObjects(): array;

    /**
     * @param ISitemapUrlDataObject[] $dataObjects
     */
    public function setDataObjects(array $dataObjects): void;

    public function addDataObject(ISitemapUrlDataObject $dataObject): void;

    public function isValid(): bool;
}
