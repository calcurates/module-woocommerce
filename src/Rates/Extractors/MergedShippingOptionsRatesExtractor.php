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
                $ready_rates[] = \array_merge([
                    'has_error' => false,
                    'id' => $rate['id'],
                    'label' => $this->resolve_label($rate),
                    'message' => $rate['message'],
                    'priority' => null,
                    'priority_item' => null,
                    'rate_image' => null,
                    'packages' => $this->make_packages($rate),
                    'custom_number' => null,
                ], $this->extract_rate_fields($rate['rate'] ?? null));
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
