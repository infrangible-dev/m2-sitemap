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

    public function getLastModified(): string;

    public function getChangeFrequency(): string;

    public function getPriority(): string;
}
