<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\Extractors\RatesExtractorFactory;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class Rates
{
    /**
     * @var RatesExtractorFactory
     */
    private $rates_extractor_factory;
    /**
     * @var array
     */
    private $rates;
    /**
     * @var string
     */
    private $tax_mode;
    /**
     * @var array
     */
    private $package;

    public function __construct(string $tax_mode, array $package)
    {
        $this->tax_mode = $tax_mode;
        $this->package = $package;
        $this->rates = [];
        $this->rates_extractor_factory = new RatesExtractorFactory();
    }

    /**
     * Extract rates from Calcurates response.
     */
    public function extract(array $response): array
    {
        foreach ($response['shippingOptions'] as $shipping_option_name => $shipping_option_data) {
            try {
                $rate = $this->rates_extractor_factory->create($shipping_option_name);
            } catch (\Exception $e) {
                continue;
            }

            $extracted_rates = $rate->extract($shipping_option_data);
            if ($extracted_rates) {
                $this->rates = \array_merge($this->rates, $extracted_rates);
            }
        }

        $this->rates_sort();

        return $this->rates;
    }

    /**
     * Sort rates by priority or cost.
     */
    private function rates_sort(): void
    {
        $rates = [
            'has_priority' => [],
            'no_priority' => [],
        ];

        foreach ($this->rates as $rate) {
            $rates[$rate['priority'] ? 'has_priority' : 'no_priority'][] = $rate;
        }

        \usort($rates['has_priority'], static function ($a, $b): int {
            if ($a['priority'] === $b['priority']) {
                return 0;
            }

            return ($a['priority'] < $b['priority']) ? -1 : 1;
        });

        \usort($rates['no_priority'], static function ($a, $b): int {
            if ($a['cost'] === $b['cost']) {
                return 0;
            }

            return ($a['cost'] < $b['cost']) ? -1 : 1;
        });

        $this->rates = \array_merge($rates['has_priority'], $rates['no_priority']);
    }

    /**
     * Convert rates to WooCommerce compatible data structure.
     */
    public function convert_rates_to_wc_rates(): array
    {
        $rates = [];

        foreach ($this->rates as $rate) {
            $rates[] = [
                'id' => 'calcurates:'.$rate['id'],
                'label' => $rate['label'],
                'cost' => $rate['cost'],
                'package' => $this->package,
                'meta_data' => [
                    'message' => $rate['message'],
                    'delivery_date_from' => $rate['delivery_date_from'],
                    'delivery_date_to' => $rate['delivery_date_to'],
                    'tax' => $rate['tax'],
                ],
                'priority' => $rate['priority'],
            ];
        }

        return $rates;
    }

    /**
     * Apply tax mode.
     */
    public function apply_tax_mode(): void
    {
        $rates = [];

        foreach ($this->rates as $rate) {
            if ($rate['tax']) {
                if ('tax_included' === $this->tax_mode) {
                    $rate['label'] .= ' - duties & tax included';
                    $rate['cost'] += $rate['tax'];
                    $rates[] = $rate;
                } elseif ('without_tax' === $this->tax_mode) {
                    $rates[] = $rate;
                } elseif ('both' === $this->tax_mode) {
                    $label = $rate['label'];
                    $id = $rate['id'];
                    $cost = $rate['cost'];

                    $rate['label'] = $label.' - duties & tax included';
                    $rate['id'] = $id.'tax_included';
                    $rate['cost'] += $rate['tax'];
                    $rates[] = $rate;

                    $rate['label'] = $label.' - without duties & tax';
                    $rate['id'] = $id.'without_tax';
                    $rate['cost'] = $cost;
                    $rates[] = $rate;
                }
            } else {
                $rates[] = $rate;
            }
        }

        $this->rates = $rates;
    }
}
