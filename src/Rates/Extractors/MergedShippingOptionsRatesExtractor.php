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
            if ($rate['success']) {
                $ready_rates[] = [
                    'has_error' => false,
                    'id' => $rate['id'],
                    'label' => $this->resolveLabel($rate),
                    'cost' => $rate['rate']['cost'] ?? 0,
                    'tax' => $rate['rate']['tax'] ?? 0,
                    'message' => null,
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => null,
                    'priority_item' => null,
                    'rate_image' => null,
                    'time_slots' => isset($rate['rate']['estimatedDeliveryDate']) && isset($rate['rate']['estimatedDeliveryDate']['timeSlots']) ? $rate['rate']['estimatedDeliveryDate']['timeSlots'] : null,
                ];
            }
        }

        return $ready_rates;
    }
}
