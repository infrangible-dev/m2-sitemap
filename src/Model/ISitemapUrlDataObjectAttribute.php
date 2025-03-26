<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ISitemapUrlDataObjectAttribute
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getValue(): string;

    public function setValue(string $value): void;
}
