<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Task;

use FeWeDev\Base\Files;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Registry;
use Infrangible\Core\Helper\Stores;
use Infrangible\Core\Helper\Xml;
use Infrangible\SimpleMail\Model\MailFactory;
use Infrangible\Sitemap\Model\ISitemapUrl;
use Infrangible\Task\Helper\Data;
use Infrangible\Task\Model\RunFactory;
use Infrangible\Task\Task\Base;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Sitemap extends Base
{
    /** @var Variables */
    protected $variables;

    /** @var Stores */
    protected $storeHelper;

    /** @var \Infrangible\Sitemap\Helper\Data */
    protected $sitemapHelper;

    /** @var \Infrangible\Core\Helper\Files */
    protected $fileHelper;

    /** @var Xml */
    protected $xmlHelper;

    /** @var string|null */
    private $fileName;

    /** @var bool */
    private $emptyRun = true;

    /** @var array<string, ISitemapUrl[]> */
    private $urls = [];

    /** @var int */
    private $chunkSize = 50000;

    public function __construct(
        Files $files,
        Registry $registryHelper,
        Data $helper,
        LoggerInterface $logging,
        DirectoryList $directoryList,
        RunFactory $runFactory,
        \Infrangible\Task\Model\ResourceModel\RunFactory $runResourceFactory,
        MailFactory $mailFactory,
        Variables $variables,
        Stores $storeHelper,
        \Infrangible\Sitemap\Helper\Data $sitemapHelper,
        \Infrangible\Core\Helper\Files $fileHelper,
        Xml $xmlHelper
    ) {
        parent::__construct(
            $files,
            $registryHelper,
            $helper,
            $logging,
            $directoryList,
            $runFactory,
            $runResourceFactory,
            $mailFactory
        );

        $this->variables = $variables;
        $this->storeHelper = $storeHelper;
        $this->sitemapHelper = $sitemapHelper;
        $this->fileHelper = $fileHelper;
        $this->xmlHelper = $xmlHelper;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    protected function prepare(): void
    {
    }

    /**
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    protected function runTask(): bool
    {
        $result = true;

        $fileName = $this->getFileName();

        if ($this->variables->isEmpty($fileName)) {
            $storeCode = $this->getStoreCode();

            if ($storeCode === 'admin') {
                foreach ($this->storeHelper->getStores() as $store) {
                    $storeId = $this->variables->intValue($store->getStoreId());

                    $result = $this->collectStoreSitemapUrls($storeId) && $result;
                }
            } else {
                $store = $this->storeHelper->getStore($storeCode);

                $storeId = $this->variables->intValue($store->getStoreId());

                $result = $this->collectStoreSitemapUrls($storeId);
            }
        } else {
            foreach ($this->storeHelper->getStores() as $store) {
                $storeId = $this->variables->intValue($store->getId());

                $storeFileName = $this->storeHelper->getStoreConfig(
                    'infrangible_sitemap/general/file_name',
                    'sitemap.xml',
                    false,
                    $storeId
                );

                if ($fileName === $storeFileName) {
                    $result = $this->collectStoreSitemapUrls($storeId) && $result;
                }
            }
        }

        if ($result) {
            $this->writeSitemaps();
        }

        return $result;
    }

    protected function collectStoreSitemapUrls(int $storeId): bool
    {
        if (! $this->sitemapHelper->isEnabled($storeId)) {
            return true;
        }

        $this->logging->info(
            sprintf(
                'Collecting sitemap data for store with id: %d',
                $storeId
            )
        );

        $storeFileName = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/general/file_name',
            'sitemap.xml',
            false,
            $storeId
        );

        try {
            $sources = $this->sitemapHelper->getSources($storeId);
        } catch (\Exception $exception) {
            $this->logging->error(
                sprintf(
                    'Could not initialize sources of store with id: %d because: %s',
                    $storeId,
                    $exception->getMessage()
                )
            );

            return false;
        }

        foreach ($sources as $sourceName => $source) {
            try {
                $this->logging->info(
                    sprintf(
                        'Getting sitemap data for store with id: %d from source with name: %s',
                        $storeId,
                        $sourceName
                    )
                );

                $storeData = $source->getStoreData($storeId);

                $this->logging->info(
                    sprintf(
                        'Found %d entries for store with id: %d from source with name: %s',
                        count($storeData),
                        $storeId,
                        $sourceName
                    )
                );
            } catch (\Exception $exception) {
                $this->logging->error(
                    sprintf(
                        'Could not get source data of source with name: %s in store with id: %d because: %s',
                        $sourceName,
                        $storeId,
                        $exception->getMessage()
                    )
                );

                return false;
            }

            try {
                $this->logging->info(
                    sprintf(
                        'Transforming sitemap data for store with id: %d from source with name: %s',
                        $storeId,
                        $sourceName
                    )
                );

                $sitemapUrls = $source->transformStoreData(
                    $storeId,
                    $storeData
                );

                $this->logging->info(
                    sprintf(
                        'Transformed %d sitemap urls for store with id: %d from source with name: %s',
                        count($sitemapUrls),
                        $storeId,
                        $sourceName
                    )
                );
            } catch (\Exception $exception) {
                $this->logging->error(
                    sprintf(
                        'Could not transform source data of source with name: %s in store with id: %d because: %s',
                        $sourceName,
                        $storeId,
                        $exception->getMessage()
                    )
                );

                return false;
            }

            if (! array_key_exists(
                $storeFileName,
                $this->urls
            )) {
                $this->urls[ $storeFileName ] = [];
            }

            $this->urls[ $storeFileName ] = array_merge(
                $this->urls[ $storeFileName ],
                $sitemapUrls
            );
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    protected function writeSitemaps(): void
    {
        if (count($this->urls) === 0) {
            return;
        }

        if (count($this->urls) > 1) {
            foreach (array_keys($this->urls) as $fileName) {
                if (basename($fileName) === 'sitemap.xml') {
                    throw new \Exception(
                        'If data is split into multiple files, no file must be named sitemap.xml in configuration.'
                    );
                }
            }
        }

        $this->setEmptyRun(false);

        $lastModDate = $this->storeHelper->getDate()->format('Y-m-d');

        $publicUrlPath = $this->directoryList->getUrlPath('pub');

        $allSitemaps = [];

        foreach ($this->urls as $fileName => $fileUrls) {
            $this->logging->info(
                sprintf(
                    'Collected %d sitemap urls into file with name: %s',
                    count($fileUrls),
                    $fileName
                )
            );

            $sitemapEntries = [];

            foreach ($fileUrls as $fileUrl) {
                $sitemapEntry = [
                    'loc'        => $fileUrl->getUrl(),
                    'lastmod'    => $fileUrl->getLastModified(),
                    'changefreq' => $fileUrl->getChangeFrequency(),
                    'priority'   => $fileUrl->getPriority()
                ];

                $images = $fileUrl->getImages();

                if (count($images) > 0) {
                    foreach ($images as $image) {
                        $sitemapEntry[ 'image:image' ][] = [
                            'image:loc'     => $image->getUrl(),
                            'image:title'   => $image->getTitle(),
                            'image:caption' => $image->getCaption()
                        ];
                    }
                }

                $dataObjects = $fileUrl->getDataObjects();

                if (count($dataObjects) > 0) {
                    $pageMapDataObjects = [];

                    foreach ($dataObjects as $dataObject) {
                        $pageMapDataObjectAttributes = [];

                        foreach ($dataObject->getAttributes() as $dataObjectAttribute) {
                            $pageMapDataObjectAttributes[] = [
                                '@name'  => $dataObjectAttribute->getName(),
                                '@value' => $dataObjectAttribute->getValue()
                            ];
                        }

                        $pageMapDataObjects[] = [
                            '@type'     => $dataObject->getType(),
                            'Attribute' => $pageMapDataObjectAttributes
                        ];
                    }

                    $sitemapEntry[ 'PageMap' ] = [
                        '@xmlns'     => 'http://www.google.com/schemas/sitemap-pagemap/1.0',
                        'DataObject' => $pageMapDataObjects
                    ];
                }

                $sitemapEntries[] = $sitemapEntry;
            }

            $sitemapUrls = array_map(
                function (array $sitemapEntry) {
                    return $sitemapEntry[ 'loc' ];
                },
                $sitemapEntries
            );

            $uniqueSitemapUrls = array_unique($sitemapUrls);

            $urls = array_values(
                array_intersect_key(
                    $sitemapEntries,
                    $uniqueSitemapUrls
                )
            );

            $urlChunks = array_chunk(
                $urls,
                $this->chunkSize
            );

            $filePath = $this->fileHelper->determineFilePath(
                $publicUrlPath === 'pub' ? $fileName : sprintf(
                    'pub/%s',
                    $fileName
                )
            );

            if (count($urlChunks) > 1) {
                $this->logging->info(
                    sprintf(
                        'Splitting %d unique sitemap urls into %d files',
                        count($fileUrls),
                        count($urlChunks)
                    )
                );

                $sitemaps = [];

                for ($i = 0; $i < count($urlChunks); $i++) {
                    $chunkUrls = $urlChunks[ $i ];

                    $pathInfo = pathinfo($filePath);

                    $chunkFileName = sprintf(
                        '%s/%s_%03d.%s',
                        $pathInfo[ 'dirname' ],
                        $pathInfo[ 'filename' ],
                        $i + 1,
                        $pathInfo[ 'extension' ]
                    );

                    $chunkFileName = preg_replace(
                        '/^\.\//',
                        '',
                        $chunkFileName
                    );

                    $this->logging->info(
                        sprintf(
                            'Exporting %d unique sitemap urls into file with name: %s',
                            count($chunkUrls),
                            $chunkFileName
                        )
                    );

                    $this->xmlHelper->write(
                        $chunkFileName,
                        'urlset',
                        [
                            'xmlns'       => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                            'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1'
                        ],
                        ['url' => $chunkUrls]
                    );

                    $this->logging->info(
                        sprintf(
                            'Finished writing chunk data to: %s',
                            $chunkFileName
                        )
                    );

                    $sitemaps[] = [
                        'loc'     => sprintf(
                            '%s%s',
                            $this->storeHelper->getWebUrl(),
                            $chunkFileName
                        ),
                        'lastmod' => $lastModDate
                    ];
                }

                $this->logging->info(
                    sprintf(
                        'Exporting sitemap index into file with path: %s',
                        $filePath
                    )
                );

                $this->xmlHelper->write(
                    $filePath,
                    'sitemapindex',
                    [
                        'xmlns'       => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                        'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1'
                    ],
                    ['sitemap' => $sitemaps]
                );
            } else {
                $this->logging->info(
                    sprintf(
                        'Exporting %d unique sitemap urls into file with path: %s',
                        count($urls),
                        $filePath
                    )
                );

                $this->xmlHelper->write(
                    $filePath,
                    'urlset',
                    [
                        'xmlns'       => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                        'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1'
                    ],
                    ['url' => $urls]
                );
            }

            $allSitemaps[] = [
                'loc'     => sprintf(
                    '%s%s',
                    $this->storeHelper->getWebUrl(),
                    $fileName
                ),
                'lastmod' => $lastModDate
            ];
        }

        if (count($this->urls) > 1) {
            $generateMainSitemap = $this->storeHelper->getDefaultConfigFlag(
                'infrangible_sitemap/general/main',
                true
            );

            if ($generateMainSitemap) {
                $filePath = $this->fileHelper->determineFilePath(
                    $publicUrlPath === 'pub' ? 'sitemap.xml' : 'pub/sitemap.xml'
                );

                $this->logging->info(
                    sprintf(
                        'Exporting sitemap index into file with path: %s',
                        $filePath
                    )
                );

                $this->xmlHelper->write(
                    $filePath,
                    'sitemapindex',
                    [
                        'xmlns'       => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                        'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1'
                    ],
                    ['sitemap' => $allSitemaps]
                );
            }
        }
    }

    protected function dismantle(bool $success): void
    {
    }

    public function isEmptyRun(): bool
    {
        return $this->emptyRun;
    }

    public function setEmptyRun(bool $emptyRun): void
    {
        $this->emptyRun = $emptyRun;
    }
}
