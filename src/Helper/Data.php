<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Helper;

use Exception;
use Infrangible\Core\Helper\Instances;
use Infrangible\Core\Helper\Stores;
use Infrangible\Sitemap\Model\Source\ISource;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var Stores */
    protected $storeHelper;

    /** @var Instances */
    protected $instanceHelper;

    public function __construct(Stores $storeHelper, Instances $instanceHelper)
    {
        $this->storeHelper = $storeHelper;
        $this->instanceHelper = $instanceHelper;
    }

    public function isEnabled(int $storeId): bool
    {
        return $storeId > 0 && $this->storeHelper->getStoreConfig(
                'infrangible_sitemap/general/export',
                true,
                true,
                $storeId
            );
    }

    /**
     * @return ISource[]
     * @throws Exception
     */
    public function getSources(int $storeId): array
    {
        $sourceConfig = $this->storeHelper->getStoreConfig(
            'infrangible_sitemap/source',
            [],
            false,
            $storeId
        );

        $sources = [];

        foreach ($sourceConfig as $sourceName => $sourceClassName) {
            $sourceClass = $this->instanceHelper->getInstance($sourceClassName);

            if ($sourceClass === null) {
                throw new Exception(
                    sprintf(
                        'Could not load source class: %s',
                        $sourceClassName
                    )
                );
            }

            if (! ($sourceClass instanceof ISource)) {
                throw new Exception(
                    sprintf(
                        'Source class: %s does not implement %s',
                        $sourceClassName,
                        ISource::class
                    )
                );
            }

            $sources[ $sourceName ] = $sourceClass;
        }

        return $sources;
    }
}
