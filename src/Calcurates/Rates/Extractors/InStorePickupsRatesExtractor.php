<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

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
            if (true !== $in_store_rate['success']) {
                continue;
            }

            foreach ($in_store_rate['stores'] as $store) {
                if (true !== $store['success']) {
                    continue;
                }

                $ready_rates[] = [
                    'id' => $in_store_rate['id'].'_'.$store['id'],
                    'label' => $store['name'],
                    'cost' => $store['rate']['cost'],
                    'tax' => $store['rate']['tax'] ?: 0,
                    'message' => $in_store_rate['message'],
                    'delivery_date_from' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($store['rate']['estimatedDeliveryDate']) ? $store['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $in_store_rate['priority'],
                ];
            }
        }

        return $ready_rates;
    }
}
