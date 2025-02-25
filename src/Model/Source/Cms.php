<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model\Source;

use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Url;
use Infrangible\Sitemap\Model\ISitemapUrl;
use Infrangible\Sitemap\Model\SitemapUrlFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cms implements ISource
{
    /** @var Url */
    protected $urlHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var \Infrangible\Core\Helper\Cms */
    protected $cmsHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var SitemapUrlFactory */
    protected $sitemapUrlFactory;

    /** @var string */
    protected $homeIdentifier;

    /** @var string */
    protected $noRouteIdentifier;

    public function __construct(
        Url $urlHelper,
        Stores $storeHelper,
        \Infrangible\Core\Helper\Cms $cmsHelper,
        LoggerInterface $logging,
        SitemapUrlFactory $sitemapUrlFactory
    ) {
        $this->urlHelper = $urlHelper;
        $this->storeHelper = $storeHelper;
        $this->cmsHelper = $cmsHelper;
        $this->logging = $logging;
        $this->sitemapUrlFactory = $sitemapUrlFactory;

        $this->homeIdentifier = $this->storeHelper->getStoreConfig('web/default/cms_home_page');
        $this->noRouteIdentifier = $this->storeHelper->getStoreConfig('web/default/cms_no_route');
    }

    public function getStoreData(int $storeId): array
    {
        $cmsExport = $this->storeHelper->getStoreConfigFlag(
            'infrangible_sitemap/cms/export',
            true,
            $storeId
        );

        if (! $cmsExport) {
            return [];
        }

        $collection = $this->cmsHelper->getCmsPageCollection();

        $collection->addStoreFilter($storeId);
        $collection->addFieldToFilter(
            'exclude_from_sitemap',
            0
        );

        return $collection->getItems();
    }

    /**
     * @param Page[] $storeData
     *
     * @return ISitemapUrl[]
     * @throws NoSuchEntityException
     */
    public function transformStoreData(int $storeId, array $storeData): array
    {
        $defaultChangeFrequency = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/cms/changeFrequency',
            'yearly',
            false,
            $storeId
        );
        $defaultPriority = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/cms/priority',
            '0.5',
            false,
            $storeId
        );

        $urls = [];

        foreach ($storeData as $cmsPage) {
            $cmsPageData = $this->getCmsPageData(
                $cmsPage,
                $storeId,
                $defaultChangeFrequency,
                $defaultPriority
            );

            if (count($cmsPageData) > 0) {
                [$url, $lastModified, $changeFrequency, $priority] = $cmsPageData;

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

    /**
     * @throws NoSuchEntityException
     */
    protected function getCmsPageData(Page $cmsPage, int $storeId, string $changeFrequency, string $priority): array
    {
        if ($cmsPage->getIdentifier() == $this->homeIdentifier) {
            $this->logging->debug(
                sprintf(
                    'Exporting CMS home page with title: %s in store with id: %s',
                    $cmsPage->getTitle(),
                    $storeId
                )
            );

            return [
                $this->storeHelper->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_WEB),
                date(
                    'Y-m-d',
                    strtotime($cmsPage->getUpdateTime())
                ),
                $changeFrequency,
                '1.0'
            ];
        } elseif ($cmsPage->getIdentifier() == $this->noRouteIdentifier) {
            return [];
        } else {
            $this->logging->debug(
                sprintf(
                    'Exporting CMS page with identifier: %s, title: %s in store with id: %s',
                    $cmsPage->getIdentifier(),
                    $cmsPage->getTitle(),
                    $storeId
                )
            );

            return [
                $this->urlHelper->getUrl(
                    '',
                    null,
                    ['_direct' => $cmsPage->getIdentifier()],
                    $storeId
                ),
                date(
                    'Y-m-d',
                    strtotime($cmsPage->getUpdateTime())
                ),
                $changeFrequency,
                $priority
            ];
        }
    }
}
