<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Files;
use Infrangible\Core\Helper\Stores;
use Infrangible\Sitemap\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /** @var Variables */
    protected $variables;

    /** @var DirectoryList */
    protected $directoryList;

    /** @var Stores */
    protected $storeHelper;

    /** @var Files */
    protected $fileHelper;

    /** @var Data */
    protected $sitemapHelper;

    /**
     * @throws \Exception
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        Variables $variables,
        DirectoryList $directoryList,
        Stores $storeHelper,
        Files $fileHelper,
        Data $sitemapHelper
    ) {
        parent::__construct($entityFactory);

        $this->variables = $variables;
        $this->directoryList = $directoryList;
        $this->storeHelper = $storeHelper;
        $this->fileHelper = $fileHelper;
        $this->sitemapHelper = $sitemapHelper;

        $fileNames = [];

        foreach ($this->storeHelper->getStores() as $store) {
            $storeId = $this->variables->intValue($store->getId());

            if ($this->sitemapHelper->isEnabled($storeId)) {
                $fileName = $this->storeHelper->getStoreConfig(
                    'infrangible_sitemap/general/file_name',
                    'sitemap.xml',
                    false,
                    $storeId
                );

                $fileNames[ $fileName ][] = $storeId;
            }
        }

        $publicUrlPath = $this->directoryList->getUrlPath('pub');

        foreach ($fileNames as $fileName => $storeIds) {
            $filePath = $this->fileHelper->determineFilePath(
                $publicUrlPath === 'pub' ? $fileName : sprintf(
                    'pub/%s',
                    $fileName
                )
            );

            $item = [
                'id'         => $fileName,
                'filename'   => $fileName,
                'store_ids'  => implode(
                    ',',
                    $storeIds
                ),
                'created_at' => file_exists($filePath) ? date(
                    'Y-m-d H:i:s',
                    filemtime($filePath)
                ) : '',
                'link'       => file_exists($filePath) ? sprintf(
                    '%s%s',
                    $this->storeHelper->getWebUrl(),
                    $fileName
                ) : ''
            ];

            $dataObject = new DataObject();
            $dataObject->setData($item);

            $this->_items[] = $dataObject;
        }

        $this->_setIsLoaded();
    }
}
