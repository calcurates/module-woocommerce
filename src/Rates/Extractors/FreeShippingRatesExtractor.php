<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FreeShippingRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $rate) {
            if ($rate['success'] || $rate['message']) {
                $ready_rates[] = \array_merge([
                    'has_error' => !$rate['success'],
                    'id' => $rate['id'],
                    'label' => $this->resolve_label($rate),
                    'message' => $rate['message'],
                    'priority' => $rate['priority'],
                    'priority_item' => null,
                    'rate_image' => $rate['imageUri'],
                    'packages' => $this->make_packages($rate['rates']),
                    'custom_number' => null,
                ], $this->extract_rate_fields($rate['rate'] ?? null));
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
