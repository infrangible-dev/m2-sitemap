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
use Infrangible\Sitemap\Model\ISitemapUrlDataObject;
use Infrangible\Sitemap\Model\ISitemapUrlDataObjectAttribute;
use Infrangible\Sitemap\Model\ISitemapUrlImage;
use Infrangible\Sitemap\Model\SitemapUrlDataObjectAttributeFactory;
use Infrangible\Sitemap\Model\SitemapUrlDataObjectFactory;
use Infrangible\Sitemap\Model\SitemapUrlFactory;
use Infrangible\Sitemap\Model\SitemapUrlImageFactory;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\App\EmulationFactory;
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

    /** @var SitemapUrlImageFactory */
    protected $sitemapUrlImageFactory;

    /** @var SitemapUrlDataObjectFactory */
    protected $sitemapUrlDataObjectFactory;

    /** @var SitemapUrlDataObjectAttributeFactory */
    protected $sitemapUrlDataObjectAttributeFactory;

    /** @var UrlBuilder */
    protected $urlBuilder;

    /** @var Emulation */
    protected $appEmulation;

    /** @var ManagerInterface */
    protected $eventManager;

    public function __construct(
        Variables $variables,
        Arrays $arrays,
        LoggerInterface $logging,
        Stores $storeHelper,
        Database $databaseHelper,
        Export $exportHelper,
        Url $urlHelper,
        SitemapUrlFactory $sitemapUrlFactory,
        SitemapUrlImageFactory $sitemapUrlImageFactory,
        SitemapUrlDataObjectFactory $sitemapUrlDataObjectFactory,
        SitemapUrlDataObjectAttributeFactory $sitemapUrlDataObjectAttributeFactory,
        UrlBuilder $urlBuilder,
        EmulationFactory $appEmulationFactory,
        ManagerInterface $eventManager
    ) {
        $this->variables = $variables;
        $this->arrays = $arrays;
        $this->logging = $logging;
        $this->storeHelper = $storeHelper;
        $this->databaseHelper = $databaseHelper;
        $this->exportHelper = $exportHelper;
        $this->urlHelper = $urlHelper;
        $this->sitemapUrlFactory = $sitemapUrlFactory;
        $this->sitemapUrlImageFactory = $sitemapUrlImageFactory;
        $this->sitemapUrlDataObjectFactory = $sitemapUrlDataObjectFactory;
        $this->sitemapUrlDataObjectAttributeFactory = $sitemapUrlDataObjectAttributeFactory;
        $this->urlBuilder = $urlBuilder;
        $this->appEmulation = $appEmulationFactory->create();
        $this->eventManager = $eventManager;
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

        $attributes = ['url_path', 'updated_at', 'exclude_from_sitemap'];

        $data = new DataObject(['attributes' => $attributes]);

        $this->eventManager->dispatch(
            'infrangible_sitemap_attributes_product',
            ['attributes' => $data]
        );

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
                $data->getData('attributes')
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
        $this->appEmulation->startEnvironmentEmulation($storeId);

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
                $sitemapUrl = $this->createSitemapUrlModel();

                $this->populateSitemapUrl(
                    $sitemapUrl,
                    $storeId,
                    $productData
                );

                $galleryImages = $this->arrays->getValue(
                    $productData,
                    'gallery_images'
                );

                $addedDataObject = false;

                foreach ($galleryImages as $galleryImageData) {
                    $imagePath = $this->arrays->getValue(
                        $galleryImageData,
                        'value'
                    );

                    if ($imagePath !== \Magento\Sitemap\Model\ResourceModel\Catalog\Product::NOT_SELECTED_IMAGE) {
                        $sitemapUrlImage = $this->createSitemapUrlImageModel();

                        $this->populateSitemapUrlImage(
                            $sitemapUrlImage,
                            $productData,
                            $galleryImageData
                        );

                        $sitemapUrl->addImage($sitemapUrlImage);

                        if (! $addedDataObject) {
                            $sitemapUrlDataObjectThumbnail = $this->createSitemapUrlDataObjectModel();

                            $sitemapUrlDataObjectThumbnail->setType('thumbnail');

                            $sitemapUrlDataObjectAttributeThumbnailName =
                                $this->createSitemapUrlDataObjectAttributeModel();

                            $this->populateDataObjectAttributeThumbnailName(
                                $sitemapUrlDataObjectAttributeThumbnailName,
                                $productData
                            );

                            $sitemapUrlDataObjectThumbnail->addAttribute($sitemapUrlDataObjectAttributeThumbnailName);

                            $sitemapUrlDataObjectAttributeThumbnailSource =
                                $this->createSitemapUrlDataObjectAttributeModel();

                            $this->populateDataObjectAttributeThumbnailSource(
                                $sitemapUrlDataObjectAttributeThumbnailSource,
                                $galleryImageData
                            );

                            $sitemapUrlDataObjectThumbnail->addAttribute($sitemapUrlDataObjectAttributeThumbnailSource);

                            $sitemapUrl->addDataObject($sitemapUrlDataObjectThumbnail);

                            $addedDataObject = true;
                        }
                    }
                }

                $this->eventManager->dispatch(
                    'infrangible_sitemap_transform_product',
                    [
                        'sitemap_url'  => $sitemapUrl,
                        'product_data' => $productData
                    ]
                );

                $urls[] = $sitemapUrl;
            }
        }

        $this->appEmulation->stopEnvironmentEmulation();

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

    public function createSitemapUrlModel(): ISitemapUrl
    {
        return $this->sitemapUrlFactory->create();
    }

    public function populateSitemapUrl(ISitemapUrl $sitemapUrl, int $storeId, array $productData): void
    {
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

        $sitemapUrl->setUrl($url);
        $sitemapUrl->setLastModified($lastModified);
        $sitemapUrl->setChangeFrequency($changeFrequency);
        $sitemapUrl->setPriority($priority);
    }

    public function createSitemapUrlImageModel(): ISitemapUrlImage
    {
        return $this->sitemapUrlImageFactory->create();
    }

    public function populateSitemapUrlImage(
        ISitemapUrlImage $sitemapUrlImage,
        array $productData,
        array $galleryImageData
    ): void {
        $imagePath = $this->arrays->getValue(
            $galleryImageData,
            'value'
        );

        $url = $this->urlBuilder->getUrl(
            $imagePath,
            'product_page_image_large'
        );

        $title = $this->arrays->getValue(
            $productData,
            'name',
            ''
        );

        $caption = $this->arrays->getValue(
            $galleryImageData,
            'label',
            ''
        );

        $sitemapUrlImage->setUrl($url);
        $sitemapUrlImage->setTitle($title);
        $sitemapUrlImage->setCaption($caption);
    }

    public function createSitemapUrlDataObjectModel(): ISitemapUrlDataObject
    {
        return $this->sitemapUrlDataObjectFactory->create();
    }

    public function createSitemapUrlDataObjectAttributeModel(): ISitemapUrlDataObjectAttribute
    {
        return $this->sitemapUrlDataObjectAttributeFactory->create();
    }

    public function populateDataObjectAttributeThumbnailName(
        ISitemapUrlDataObjectAttribute $dataObjectAttributeThumbnailName,
        array $productData
    ): void {
        $name = $this->arrays->getValue(
            $productData,
            'name',
            ''
        );

        $dataObjectAttributeThumbnailName->setName('name');
        $dataObjectAttributeThumbnailName->setValue($name);
    }

    public function populateDataObjectAttributeThumbnailSource(
        ISitemapUrlDataObjectAttribute $dataObjectAttributeThumbnailSource,
        array $galleryImageData
    ): void {
        $imagePath = $this->arrays->getValue(
            $galleryImageData,
            'value'
        );

        $url = $this->urlBuilder->getUrl(
            $imagePath,
            'product_page_image_large'
        );

        $dataObjectAttributeThumbnailSource->setName('src');
        $dataObjectAttributeThumbnailSource->setValue($url);
    }
}
