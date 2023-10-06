<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FlatRatesRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $rate) {
            if ($rate['success'] || $rate['message']) {
                $ready_rates[] = [
                    'has_error' => !$rate['success'],
                    'id' => $rate['id'],
                    'label' => $this->resolveLabel($rate),
                    'cost' => $rate['rate']['cost'] ?? 0,
                    'tax' => $rate['rate']['tax'] ?? 0,
                    'currency' => $rate['rate']['currency'] ?? '',
                    'message' => $rate['message'],
                    'delivery_date_from' => $rate['rate']['estimatedDeliveryDate']['from'] ?? null,
                    'delivery_date_to' => $rate['rate']['estimatedDeliveryDate']['to'] ?? null,
                    'priority' => $rate['priority'],
                    'priority_item' => null,
                    'rate_image' => $rate['imageUri'],
                    'time_slots' => $rate['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                    'days_in_transit_from' => $rate['rate']['estimatedDeliveryDate']['daysInTransitFrom'] ?? null,
                    'days_in_transit_to' => $rate['rate']['estimatedDeliveryDate']['daysInTransitTo'] ?? null,
                ];
            }
        }

        return $ready_rates;
    }
}
