<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model\Source;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Export;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Infrangible\Sitemap\Model\ISitemapUrl;
use Infrangible\Sitemap\Model\SitemapUrlFactory;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product implements ISource
{
    /** @var Variables */
    protected $variables;

    /** @var Arrays */
    protected $arrays;

    /** @var LoggerInterface */
    protected $logging;

    /** @var Stores */
    protected $storeHelper;

    /** @var Database */
    protected $databaseHelper;

    /** @var Export */
    protected $exportHelper;

    /** @var Url */
    protected $urlHelper;

    /** @var SitemapUrlFactory */
    protected $sitemapUrlFactory;

    public function __construct(
        Variables $variables,
        Arrays $arrays,
        LoggerInterface $logging,
        Stores $storeHelper,
        Database $databaseHelper,
        Export $exportHelper,
        Url $urlHelper,
        SitemapUrlFactory $sitemapUrlFactory
    ) {
        $this->variables = $variables;
        $this->arrays = $arrays;
        $this->logging = $logging;
        $this->storeHelper = $storeHelper;
        $this->databaseHelper = $databaseHelper;
        $this->exportHelper = $exportHelper;
        $this->urlHelper = $urlHelper;
        $this->sitemapUrlFactory = $sitemapUrlFactory;
    }

    /**
     * @throws \Exception
     */
    public function getStoreData(int $storeId): array
    {
        $productExport = $this->storeHelper->getStoreConfigFlag(
            'infrangible_sitemap/product/export',
            true,
            $storeId
        );

        if (! $productExport) {
            return [];
        }

        $showOutOfStock = $this->storeHelper->getStoreConfig(
            'cataloginventory/options/show_out_of_stock',
            false,
            true,
            $storeId
        );

        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        $lastProductId = 0;

        $productsData = [];

        while (true) {
            $this->logging->debug(
                sprintf(
                    'Fetched from database with product id > %d',
                    $lastProductId
                )
            );

            $chunkProductIds = $this->exportHelper->getExportableProductIds(
                $storeId,
                ! $showOutOfStock,
                [],
                $lastProductId
            );

            if ($this->variables->isEmpty($chunkProductIds)) {
                break;
            }

            $this->logging->debug(
                sprintf(
                    'Fetched %d product(s) from database',
                    count($chunkProductIds)
                )
            );

            $chunkedProductsData = $this->exportHelper->getProductsData(
                $dbAdapter,
                $chunkProductIds,
                $storeId,
                [],
                ['url_path', 'updated_at', 'exclude_from_sitemap']
            );

            $productsData = array_merge(
                $productsData,
                $chunkedProductsData
            );

            $lastProductId = end($chunkProductIds);
        }

        return $productsData;
    }

    /**
     * @return ISitemapUrl[]
     */
    public function transformStoreData(int $storeId, array $storeData): array
    {
        $changeFrequency = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/product/changeFrequency',
            'daily',
            false,
            $storeId
        );

        $priority = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/product/priority',
            '1.0',
            false,
            $storeId
        );

        $urls = [];

        foreach ($storeData as $productData) {
            $excludeFromSitemap = $this->arrays->getValue(
                $productData,
                'exclude_from_sitemap',
                0
            );

            $excludeFromSitemapId = (int)(is_array($excludeFromSitemap) ? $this->arrays->getValue(
                $excludeFromSitemap,
                'id',
                0
            ) : 0);

            if ($excludeFromSitemapId != 1) {
                $url = $this->getProductUrl(
                    $storeId,
                    $productData
                );

                $lastModified = date(
                    'Y-m-d',
                    strtotime(
                        $this->arrays->getValue(
                            $productData,
                            'updated_at'
                        )
                    )
                );

                $sitemapUrl = $this->sitemapUrlFactory->create();

                $sitemapUrl->setUrl($url);
                $sitemapUrl->setLastModified($lastModified);
                $sitemapUrl->setChangeFrequency($changeFrequency);
                $sitemapUrl->setPriority($priority);

                $urls[] = $sitemapUrl;
            }
        }

        return $urls;
    }

    protected function getProductUrl(int $storeId, array $productData): string
    {
        $urlRewrites = $this->arrays->getValue(
            $productData,
            'url_rewrites',
            []
        );

        foreach ($urlRewrites as $categoryId => $url) {
            if ($categoryId === 0) {
                return $this->urlHelper->getUrl(
                    '',
                    null,
                    ['_direct' => $url],
                    $storeId
                );
            }
        }

        return $this->urlHelper->getUrl(
            '',
            null,
            [
                '_direct' => $this->arrays->getValue(
                    $productData,
                    'url_path',
                    $this->urlHelper->getUrl()
                )
            ],
            $storeId
        );
    }
}
