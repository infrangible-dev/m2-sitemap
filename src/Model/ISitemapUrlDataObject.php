<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ISitemapUrlDataObject
{
    public function getType(): string;

    public function setType(string $type): void;

    /**
     * @return ISitemapUrlDataObjectAttribute[]
     */
    public function getAttributes(): array;

    /**
     * @param ISitemapUrlDataObjectAttribute[] $attributes
     */
    public function setAttributes(array $attributes): void;

    public function addAttribute(ISitemapUrlDataObjectAttribute $attribute): void;
}
