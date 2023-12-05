<?php

declare(strict_types=1);

namespace Calcurates\Rates;

use Calcurates\Logger;
use Calcurates\Rates\Extractors\RatesExtractorFactory;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class Rates
{
    /**
     * @var array{
     *     has_error: bool,
     *     id: string,
     *     label: string,
     *     cost: float|int,
     *     tax: float|int,
     *     message: string|null,
     *     delivery_date_from: string|null,
     *     delivery_date_to: string|null,
     *     priority: int|null,
     *     priority_item: int|null,
     *     rate_image: string|null,
     *     time_slots: array|null,
     *     currency: string,
     *     days_in_transit_from: int|null,
     *     days_in_transit_to: int|null,
     * }[]
     */
    private $rates = [];
    /**
     * @var string
     */
    private $tax_mode;
    /**
     * @var array
     */
    private $package;
    /**
     * @var array
     */
    private $response;

    public function __construct(array $response, string $tax_mode, array $package)
    {
        $this->response = $response;
        $this->tax_mode = $tax_mode;
        $this->package = $package;

        $this->extract_rates();
    }

    /**
     * Extract rates from Calcurates response.
     */
    private function extract_rates(): void
    {
        $rates_extractor_factory = new RatesExtractorFactory();
        foreach ($this->response['shippingOptions'] as $shipping_option_name => $shipping_option_data) {
            try {
                $rate = $rates_extractor_factory->create($shipping_option_name);
            } catch (\Exception $e) {
                Logger::getInstance()->error(
                    "Can't create rate from Shipping Option $shipping_option_name",
                    [
                        'exception' => $e,
                        'shipping_option_name' => $shipping_option_name,
                        'shipping_option_data' => $shipping_option_data,
                    ]
                );
                continue;
            }

            $extracted_rates = $rate->extract($shipping_option_data);
            if ($extracted_rates) {
                \array_push($this->rates, ...$extracted_rates);
            }
        }

        $this->rates_sort();
    }

    /**
     * Sort rates by priority or cost.
     */
    private function rates_sort(): void
    {
        \usort($this->rates, static function (array $a, array $b): int {
            if ($a['priority'] === $b['priority']) {
                if ($a['priority_item'] === $b['priority_item']) {
                    $result = $a['cost'] <=> $b['cost'];
                    if (0 === $result) {
                        $result = $a['label'] <=> $b['label'];
                    }

                    return $result;
                }

                if (null === $a['priority_item']) {
                    return 1;
                }
                if (null === $b['priority_item']) {
                    return -1;
                }

                return $a['priority_item'] <=> $b['priority_item'];
            }
            if (null === $a['priority']) {
                return 1;
            }
            if (null === $b['priority']) {
                return -1;
            }

            return $a['priority'] <=> $b['priority'];
        });
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
                    'message' => $this->prepare_message($rate),
                    'delivery_date_from' => $rate['delivery_date_from'],
                    'delivery_date_to' => $rate['delivery_date_to'],
                    'tax' => $rate['tax'],
                    'currency' => $rate['currency'],
                    'has_error' => $rate['has_error'],
                    'rate_image' => $rate['rate_image'],
                    'time_slot_date_required' => isset($this->response['metadata']['deliveryDates']['timeSlotDateRequired']) && $this->response['metadata']['deliveryDates']['timeSlotDateRequired'] ? '1' : '0',
                    'time_slot_time_required' => isset($this->response['metadata']['deliveryDates']['timeSlotTimeRequired']) && $this->response['metadata']['deliveryDates']['timeSlotTimeRequired'] ? '1' : '0',
                    'time_slots' => $rate['time_slots'],
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

    private function prepare_message(array $rate): string
    {
        $message = $rate['message'] ?: '';

        if ($message) {
            $message = \str_replace(
                ['{tax_amount}', '{min_transit_days}', '{max_transit_days}', '{packages}'],
                [($rate['tax'].' '.$rate['currency']), $rate['days_in_transit_from'], $rate['days_in_transit_to'], $this->get_packages_string()],
                $message
            );
        }

        return $message;
    }

    private function get_packages_string(): string
    {
        $packages = [];
        foreach ($this->package['contents'] as $values) {
            if ($values['quantity'] > 0 && $values['data']->needs_shipping()) {
                $packages[$values['data']->get_name()] = $packages[$values['data']->get_name()] ?? 0;
                $packages[$values['data']->get_name()] += $values['quantity'];
            }
        }
        $out = '';
        foreach ($packages as $name => $count) {
            $out .= $name.' x'.$count.'; ';
        }

        return \rtrim($out, '; ');
    }
}
