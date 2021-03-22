<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Calcurates\Rates\CarrierRatesExtractor;
use Calcurates\Calcurates\Rates\FlatRatesExtractor;
use Calcurates\Calcurates\Rates\FreeShippingRatesExtractor;
use Calcurates\Calcurates\Rates\RateShoppingRatesExtractor;
use Calcurates\Calcurates\Rates\TableRatesExtractor;

class Rates
{
    private $flat_rates_extractor;
    private $rates;

    public function __construct()
    {
        $this->flat_rates_extractor = new FlatRatesExtractor();
        $this->free_shipping_rates_extractor = new FreeShippingRatesExtractor();
        $this->table_rates_extractor = new TableRatesExtractor();
        $this->in_store_pickups_rates_extractor = new InStorePickupsRatesExtractor();
        $this->rate_shopping_rates_extractor = new RateShoppingRatesExtractor();
        $this->carriers_rates_extractor = new CarrierRatesExtractor();
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

        if ($this->has_shipping_option('flatRates', $response)) {
            $this->append_rates($this->flat_rates_extractor->extract($response->shippingOptions->flatRates));
        }
        if ($this->has_shipping_option('freeShipping', $response)) {
            $this->append_rates($this->free_shipping_rates_extractor->extract($response->shippingOptions->freeShipping));
        }
        if ($this->has_shipping_option('tableRates', $response)) {
            $this->append_rates($this->table_rates_extractor->extract($response->shippingOptions->tableRates));
        }
        if ($this->has_shipping_option('inStorePickups', $response)) {
            $this->append_rates($this->in_store_pickups_rates_extractor->extract($response->shippingOptions->inStorePickups));
        }
        if ($this->has_shipping_option('rateShopping', $response)) {
            $this->append_rates($this->rate_shopping_rates_extractor->extract($response->shippingOptions->rateShopping));
        }
        if ($this->has_shipping_option('carriers', $response)) {
            $this->append_rates($this->carriers_rates_extractor->extract($response->shippingOptions->carriers));
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
     * @param  mixed $package
     * @return array
     */
    public function convert_rates_to_wc_rates($package): array
    {
        $rates = [];

        foreach ($this->rates as $rate) {
            $rates[] = [
                'id' => 'calcurates:' . $rate['id'],
                'label' => $rate['label'],
                'cost' => $rate['cost'],
                'package' => $package,
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
    public function apply_tax_mode($tax_mode)
    {
        $rates = [];

        foreach ($this->rates as $rate) {

            if ($rate['tax']) {

                if ($tax_mode === 'tax_included') {

                    $rate['label'] .= ' - duties & tax included';
                    $rate['cost'] += $rate['tax'];
                    $rates[] = $rate;

                } elseif ($tax_mode === 'without_tax') {

                    $rates[] = $rate;

                } elseif ($tax_mode === 'both') {

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
