<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class InStorePickupsRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $in_store_rates): array
    {
        $ready_rates = [];

        foreach ($in_store_rates as $in_store_rate) {
            if (!$in_store_rate['success']) {
                if ($in_store_rate['message']) {
                    $ready_rates[] = [
                        'has_error' => true,
                        'id' => $in_store_rate['id'],
                        'label' => $in_store_rate['name'],
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
                    $ready_rates[] = [
                        'has_error' => !$store['success'],
                        'id' => $in_store_rate['id'].'_'.$store['id'],
                        'label' => $store['name'],
                        'cost' => $store['rate']['cost'] ?? 0,
                        'tax' => $store['rate']['tax'] ?? 0,
                        'message' => $store['message'],
                        'delivery_date_from' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $in_store_rate['priority'],
                        'priority_item' => $store['priority'],
                        'rate_image' => $store['imageUri'],
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
