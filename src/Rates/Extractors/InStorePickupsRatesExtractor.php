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
                    $ready_rates[] = [
                        'has_error' => true,
                        'id' => $in_store_rate['id'],
                        'label' => $this->resolveLabel($in_store_rate),
                        'cost' => 0,
                        'tax' => 0,
                        'message' => $in_store_rate['message'],
                        'delivery_date_from' => null,
                        'delivery_date_to' => null,
                        'priority' => $in_store_rate['priority'],
                        'priority_item' => null,
                        'rate_image' => $in_store_rate['imageUri'],
                    ];
                }
                continue;
            }

            foreach ($in_store_rate['stores'] as $store) {
                if ($store['success'] || $store['message']) {
                    $cost = $store['rate']['cost'] ?? 0;
                    $tax = $store['rate']['tax'] ?? 0;

                    $ready_rates[] = [
                        'has_error' => !$store['success'],
                        'id' => $in_store_rate['id'].'_'.$store['id'],
                        'label' => $this->resolveLabel($store),
                        'cost' => $store['splitTaxAndCost'] ? $cost : $cost + $tax,
                        'tax' => $store['splitTaxAndCost'] ? $tax : 0,
                        'message' => $store['message'],
                        'delivery_date_from' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $in_store_rate['priority'],
                        'priority_item' => $store['priority'],
                        'rate_image' => $store['imageUri'],
                        'time_slots' => $store['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
