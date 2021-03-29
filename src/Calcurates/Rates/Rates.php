<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\RatesExtractorFactory;

class Rates
{
    private $flat_rates_extractor;
    private $rates;
    private $tax_mode;
    private $package;

    public function __construct($tax_mode, $package)
    {
        $this->tax_mode = $tax_mode;
        $this->package = $package;
        $this->rates = [];
    }

    /**
     * Extract rates from Clacurates response
     *
     * @param  mixed $response
     * @return array
     */
    public function extract(object $response): array
    {

        foreach ($response->shippingOptions as $shipping_option_name => $shipping_option_data) {

            $rates_extractor_factory = new RatesExtractorFactory();

            try {
                $rate = $rates_extractor_factory->create($shipping_option_name);
            } catch (\Exception $e) {
                continue;
            }

            $extracted_rates = $rate->extract($shipping_option_data);
            $this->append_rates($extracted_rates);
        }

        $this->rates_sort();

        return $this->rates;
    }

    /**
     * Sort rates by priority or cost
     *
     * @return void
     */
    private function rates_sort()
    {
        $rates = [
            'has_priority' => [],
            'no_priority' => [],
        ];

        foreach ($this->rates as $rate) {
            $rates[$rate['priority'] ? 'has_priority' : 'no_priority'][] = $rate;
        }

        usort($rates['has_priority'], function ($a, $b) {

            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return ($a['priority'] < $b['priority']) ? -1 : 1;

        });

        usort($rates['no_priority'], function ($a, $b) {

            if ($a['cost'] == $b['cost']) {
                return 0;
            }

            return ($a['cost'] < $b['cost']) ? -1 : 1;

        });

        $this->rates = array_merge($rates['has_priority'], $rates['no_priority']);

    }

    /**
     * Convert rates to WooCommerce compatible data structure
     *
     * @return array
     */
    public function convert_rates_to_wc_rates(): array
    {
        $rates = [];

        foreach ($this->rates as $rate) {
            $rates[] = [
                'id' => 'calcurates:' . $rate['id'],
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
     * Check shipping option existance
     *
     * @param  mixed $shipping_option
     * @param  mixed $response
     * @return bool
     */
    private function has_shipping_option(string $shipping_option, object $response): bool
    {
        if (is_object($response) && property_exists($response, 'shippingOptions')) {
            if (property_exists($response->shippingOptions, $shipping_option) && !empty($response->shippingOptions->$shipping_option)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Append rates to rates array
     *
     * @param  mixed $rates
     * @return void
     */
    private function append_rates($rates)
    {
        if (is_array($rates) && !empty($rates)) {
            $this->rates = array_merge($this->rates, $rates);
        }
    }

    /**
     * Apply tax mode
     *
     * @param  mixed $rates
     * @return void
     */
    public function apply_tax_mode()
    {
        $rates = [];

        foreach ($this->rates as $rate) {

            if ($rate['tax']) {

                if ($this->tax_mode === 'tax_included') {

                    $rate['label'] .= ' - duties & tax included';
                    $rate['cost'] += $rate['tax'];
                    $rates[] = $rate;

                } elseif ($this->tax_mode === 'without_tax') {

                    $rates[] = $rate;

                } elseif ($this->tax_mode === 'both') {

                    $label = $rate['label'];
                    $id = $rate['id'];
                    $cost = $rate['cost'];

                    $rate['label'] = $label . ' - duties & tax included';
                    $rate['id'] = $id . 'tax_included';
                    $rate['cost'] += $rate['tax'];
                    $rates[] = $rate;

                    $rate['label'] = $label . ' - without duties & tax';
                    $rate['id'] = $id . 'without_tax';
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
