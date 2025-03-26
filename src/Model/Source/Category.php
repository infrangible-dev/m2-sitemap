<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model\Source;

use FeWeDev\Base\Arrays;
use Infrangible\Core\Helper\Database;
use Infrangible\Core\Helper\Export;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Infrangible\Sitemap\Model\ISitemapUrl;
use Infrangible\Sitemap\Model\SitemapUrlFactory;
use Magento\Framework\Event\ManagerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Category implements ISource
{
    /** @var Arrays */
    protected $arrays;

    /** @var Database */
    protected $databaseHelper;

    /** @var \Infrangible\Core\Helper\Category */
    protected $categoryHelper;

    /** @var Export */
    protected $exportHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var Url */
    protected $urlHelper;

    /** @var SitemapUrlFactory */
    protected $sitemapUrlFactory;

    /** @var ManagerInterface */
    protected $eventManager;

    public function __construct(
        Arrays $arrays,
        Database $databaseHelper,
        \Infrangible\Core\Helper\Category $categoryHelper,
        Export $exportHelper,
        Stores $storeHelper,
        Url $urlHelper,
        SitemapUrlFactory $sitemapUrlFactory,
        ManagerInterface $eventManager
    ) {
        $this->arrays = $arrays;
        $this->databaseHelper = $databaseHelper;
        $this->categoryHelper = $categoryHelper;
        $this->exportHelper = $exportHelper;
        $this->storeHelper = $storeHelper;
        $this->urlHelper = $urlHelper;
        $this->sitemapUrlFactory = $sitemapUrlFactory;
        $this->eventManager = $eventManager;
    }

    public function getStoreData(int $storeId): array
    {
        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        $activeCategoryIds = $this->categoryHelper->getActiveCategoryIds(
            $dbAdapter,
            $storeId
        );

        return $this->exportHelper->getCategoriesData(
            $dbAdapter,
            $activeCategoryIds,
            $storeId,
            ['url_path', 'updated_at', 'exclude_from_sitemap']
        );
    }

    /**
     * @return ISitemapUrl[]
     */
    public function transformStoreData(int $storeId, array $storeData): array
    {
        $urls = [];

        foreach ($storeData as $categoryData) {
            $excludeFromSitemap = $this->arrays->getValue(
                $categoryData,
                'exclude_from_sitemap',
                0
            );

            $excludeFromSitemapId = (int)(is_array($excludeFromSitemap) ? $this->arrays->getValue(
                $excludeFromSitemap,
                'id',
                0
            ) : 0);

            if ($excludeFromSitemapId != 1) {
                $sitemapUrl = $this->createSitemapUrlModel();

                $this->populateSitemapUrl(
                    $sitemapUrl,
                    $storeId,
                    $categoryData
                );

                $this->eventManager->dispatch(
                    'infrangible_sitemap_transform_category',
                    [
                        'sitemap_url'   => $sitemapUrl,
                        'category_data' => $categoryData
                    ]
                );

                $urls[] = $sitemapUrl;
            }
        }

        return $urls;
    }

    protected function getCategoryUrl(int $storeId, array $categoryData): string
    {
        $urlRewrites = $this->arrays->getValue(
            $categoryData,
            'url_rewrites',
            []
        );

        if (count($urlRewrites) > 0) {
            return $this->urlHelper->getUrl(
                '',
                null,
                ['_direct' => reset($urlRewrites)],
                $storeId
            );
        }

        return $this->urlHelper->getUrl(
            '',
            null,
            [
                '_direct' => $this->arrays->getValue(
                    $categoryData,
                    'url_path',
                    $this->urlHelper->getUrl()
                )
            ],
            $storeId
        );
    }

    public function createSitemapUrlModel(): ISitemapUrl
    {
        return $this->sitemapUrlFactory->create();
    }

    public function populateSitemapUrl(ISitemapUrl $sitemapUrl, int $storeId, array $categoryData): void
    {
        $url = $this->getCategoryUrl(
            $storeId,
            $categoryData
        );

        $lastModified = date(
            'Y-m-d',
            strtotime(
                $this->arrays->getValue(
                    $categoryData,
                    'updated_at'
                )
            )
        );

        $changeFrequency = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/category/changeFrequency',
            'daily',
            false,
            $storeId
        );

        $priority = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/category/priority',
            '1.0',
            false,
            $storeId
        );

        $sitemapUrl->setUrl($url);
        $sitemapUrl->setLastModified($lastModified);
        $sitemapUrl->setChangeFrequency($changeFrequency);
        $sitemapUrl->setPriority($priority);
    }
}
