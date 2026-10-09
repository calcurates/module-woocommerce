<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class InStorePickupsRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $in_store_rate) {
            if (!$in_store_rate['success']) {
                if ($in_store_rate['message']) {
                    $ready_rates[] = \array_merge([
                        'has_error' => true,
                        'id' => $in_store_rate['id'],
                        'label' => $this->resolve_label($in_store_rate),
                        'message' => $in_store_rate['message'],
                        'priority' => $in_store_rate['priority'],
                        'priority_item' => null,
                        'rate_image' => $in_store_rate['imageUri'],
                        'packages' => [],
                        'custom_number' => null,
                    ], $this->extract_rate_fields(null));
                }
                continue;
            }

            foreach ($in_store_rate['stores'] as $store) {
                if ($store['success'] || $store['message']) {
                    $ready_rates[] = \array_merge([
                        'has_error' => !$store['success'],
                        'id' => $in_store_rate['id'].'_'.$store['id'],
                        'label' => $this->resolve_label($store),
                        'message' => $store['message'],
                        'priority' => $in_store_rate['priority'],
                        'priority_item' => $store['priority'],
                        'rate_image' => $store['imageUri'],
                        'packages' => $this->make_packages($store['rates']),
                        'custom_number' => $store['customNumber'],
                    ], $this->extract_rate_fields($store['rate'] ?? null));
                }
            }
        }

        return $ready_rates;
    }

    private function make_packages(?array $rates): array
    {
        $packages = [];
        foreach ($rates ?? [] as $rate) {
            \array_push($packages, ...\array_map(static function (array $package): string {
                return $package['name'];
            }, $rate['packages'] ?? []));
        }

        return $packages;
    }
}
