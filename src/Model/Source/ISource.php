<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model\Source;

use Infrangible\Sitemap\Model\ISitemapUrl;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
interface ISource
{
    /**
     * @throws \Exception
     */
    public function getStoreData(int $storeId): array;

    /**
     * @return ISitemapUrl[]
     * @throws \Exception
     */
    public function transformStoreData(int $storeId, array $storeData): array;
}
