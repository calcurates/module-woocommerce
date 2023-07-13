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
                    $ready_rates[] = [
                        'has_error' => true,
                        'id' => $table_rate['id'],
                        'label' => $this->resolveLabel($table_rate),
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
                        'label' => $this->resolveLabel($method),
                        'cost' => $method['rate']['cost'] ?? 0,
                        'tax' => $method['rate']['tax'] ?? 0,
                        'message' => $method['message'],
                        'delivery_date_from' => isset($method['rate']['estimatedDeliveryDate']) ? $method['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($method['rate']['estimatedDeliveryDate']) ? $method['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $table_rate['priority'],
                        'priority_item' => $method['priority'],
                        'rate_image' => $method['imageUri'],
                        'time_slots' => isset($method['rate']['estimatedDeliveryDate']) && isset($method['rate']['estimatedDeliveryDate']['timeSlots']) ? $method['rate']['estimatedDeliveryDate']['timeSlots'] : null,
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
