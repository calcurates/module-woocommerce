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
                $cost = $rate['rate']['cost'] ?? 0;
                $tax = $rate['rate']['tax'] ?? 0;

                $ready_rates[] = [
                    'has_error' => !$rate['success'],
                    'id' => $rate['id'],
                    'label' => $this->resolveLabel($rate),
                    'cost' => $rate['splitTaxAndCost'] ? $cost : $cost + $tax,
                    'tax' => $rate['splitTaxAndCost'] ? $tax : 0,
                    'message' => $rate['message'],
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $rate['priority'],
                    'priority_item' => null,
                    'rate_image' => $rate['imageUri'],
                    'time_slots' => $rate['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                ];
            }
        }

        return $ready_rates;
    }
}
