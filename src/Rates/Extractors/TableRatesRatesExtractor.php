<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class TableRatesRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $table_rates): array
    {
        $ready_rates = [];

        foreach ($table_rates as $table_rate) {
            if (!$table_rate['success']) {
                if ($table_rate['message']) {
                    $ready_rates[] = [
                        'has_error' => true,
                        'id' => $table_rate['id'],
                        'label' => $table_rate['name'],
                        'cost' => 0,
                        'tax' => 0,
                        'message' => $table_rate['message'],
                        'delivery_date_from' => null,
                        'delivery_date_to' => null,
                        'priority' => $table_rate['priority'],
                        'priority_item' => null,
                        'rate_image' => $table_rate['imageUri'],
                    ];
                }
                continue;
            }

            foreach ($table_rate['methods'] as $method) {
                if ($method['success'] || $method['message']) {
                    $ready_rates[] = [
                        'has_error' => !$method['success'],
                        'id' => $table_rate['id'].'_'.$method['id'],
                        'label' => $method['name'],
                        'cost' => $method['rate']['cost'] ?? 0,
                        'tax' => $method['rate']['tax'] ?? 0,
                        'message' => $method['message'],
                        'delivery_date_from' => isset($method['rate']['estimatedDeliveryDate']) ? $method['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($method['rate']['estimatedDeliveryDate']) ? $method['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $table_rate['priority'],
                        'priority_item' => $method['priority'],
                        'rate_image' => $method['imageUri'],
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
