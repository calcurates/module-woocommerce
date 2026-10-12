<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class TableRatesRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $table_rate) {
            if (!$table_rate['success']) {
                if ($table_rate['message']) {
                    $ready_rates[] = \array_merge([
                        'has_error' => true,
                        'id' => $table_rate['id'],
                        'label' => $this->resolve_label($table_rate),
                        'message' => $table_rate['message'],
                        'priority' => $table_rate['priority'],
                        'priority_item' => null,
                        'rate_image' => $table_rate['imageUri'],
                        'packages' => [],
                        'custom_number' => null,
                    ], $this->extract_rate_fields(null));
                }
                continue;
            }

            foreach ($table_rate['methods'] as $method) {
                if ($method['success'] || $method['message']) {
                    $ready_rates[] = \array_merge([
                        'has_error' => !$method['success'],
                        'id' => $table_rate['id'].'_'.$method['id'],
                        'label' => $this->resolve_label($method),
                        'message' => $method['message'],
                        'priority' => $table_rate['priority'],
                        'priority_item' => $method['priority'],
                        'rate_image' => $method['imageUri'],
                        'packages' => $this->make_packages($method['rates']),
                        'custom_number' => $method['customNumber'],
                    ], $this->extract_rate_fields($method['rate'] ?? null));
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
