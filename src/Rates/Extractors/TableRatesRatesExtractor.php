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
                        'currency' => '',
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
                        'currency' => $method['rate']['currency'] ?? '',
                        'message' => $method['message'],
                        'delivery_date_from' => $method['rate']['estimatedDeliveryDate']['from'] ?? null,
                        'delivery_date_to' => $method['rate']['estimatedDeliveryDate']['to'] ?? null,
                        'priority' => $table_rate['priority'],
                        'priority_item' => $method['priority'],
                        'rate_image' => $method['imageUri'],
                        'time_slots' => $method['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                        'days_in_transit_from' => $method['rate']['estimatedDeliveryDate']['daysInTransitFrom'] ?? null,
                        'days_in_transit_to' => $method['rate']['estimatedDeliveryDate']['daysInTransitTo'] ?? null,
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
