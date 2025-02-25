<?php

declare(strict_types=1);

namespace Infrangible\Sitemap\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ChangeFrequency implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'always', 'label' => __('Always')],
            ['value' => 'hourly', 'label' => __('Hourly')],
            ['value' => 'daily', 'label' => __('Daily')],
            ['value' => 'weekly', 'label' => __('Weekly')],
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'yearly', 'label' => __('Yearly')],
            ['value' => 'never', 'label' => __('Never')]
        ];
    }

    public function toArray(): array
    {
        return [
            'always'  => __('Always'),
            'hourly'  => __('Hourly'),
            'daily'   => __('Daily'),
            'weekly'  => __('Weekly'),
            'monthly' => __('Monthly'),
            'yearly'  => __('Yearly'),
            'never'   => __('Never'),
        ];
    }
}
