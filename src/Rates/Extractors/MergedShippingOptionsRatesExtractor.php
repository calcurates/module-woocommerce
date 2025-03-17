<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class MergedShippingOptionsRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $rate) {
            if ($rate['success'] || $rate['message']) {
                $ready_rates[] = [
                    'has_error' => false,
                    'id' => $rate['id'],
                    'label' => $this->resolveLabel($rate),
                    'cost' => $rate['rate']['cost'] ?? 0,
                    'tax' => $rate['rate']['tax'] ?? 0,
                    'currency' => $rate['rate']['currency'] ?? '',
                    'message' => $rate['message'],
                    'delivery_date_from' => $rate['rate']['estimatedDeliveryDate']['from'] ?? null,
                    'delivery_date_to' => $rate['rate']['estimatedDeliveryDate']['to'] ?? null,
                    'priority' => null,
                    'priority_item' => null,
                    'rate_image' => null,
                    'time_slots' => $rate['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                    'days_in_transit_from' => $rate['rate']['estimatedDeliveryDate']['daysInTransitFrom'] ?? null,
                    'days_in_transit_to' => $rate['rate']['estimatedDeliveryDate']['daysInTransitTo'] ?? null,
                    'packages' => $this->make_packages($rate),
                    'custom_number' => null,
                ];
            }
        }

        return $ready_rates;
    }

    private function make_packages(array $rate): array
    {
        $packages = [];
        foreach ($rate['flatRates'] as $item) {
            foreach ($item['rates'] ?? [] as $v) {
                foreach ($v['packages'] ?? [] as $p) {
                    $packages[] = $p['name'];
                }
            }
        }
        foreach ($rate['freeShipping'] as $item) {
            foreach ($item['rates'] ?? [] as $v) {
                foreach ($v['packages'] ?? [] as $p) {
                    $packages[] = $p['name'];
                }
            }
        }
        foreach ($rate['tableRates'] as $item) {
            foreach ($item['methods'] as $method) {
                foreach ($method['rates'] ?? [] as $v) {
                    foreach ($v['packages'] ?? [] as $p) {
                        $packages[] = $p['name'];
                    }
                }
            }
        }
        foreach ($rate['inStorePickups'] as $item) {
            foreach ($item['stores'] as $store) {
                foreach ($store['rates'] ?? [] as $v) {
                    foreach ($v['packages'] ?? [] as $p) {
                        $packages[] = $p['name'];
                    }
                }
            }
        }
        foreach ($rate['carriers'] as $item) {
            foreach ($item['rates'] ?? [] as $v) {
                foreach ($v['services'] as $s) {
                    foreach ($s['packages'] ?? [] as $p) {
                        $packages[] = $p['name'];
                    }
                }
            }
        }
        foreach ($rate['rateShopping'] as $item) {
            foreach ($item['carriers'] ?? [] as $c) {
                foreach ($c['rates'] ?? [] as $v) {
                    foreach ($v['services'] as $s) {
                        foreach ($s['packages'] ?? [] as $p) {
                            $packages[] = $p['name'];
                        }
                    }
                }
            }
        }

        return $packages;
    }
}
