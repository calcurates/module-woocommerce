<?php

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
        $ready_rates = array();

        foreach ($in_store_rates as $in_store_rate) {
            if ($in_store_rate['success'] !== true) {
                continue;
            }

            foreach ($in_store_rate->stores as $rate) {
                if ($rate['success'] !== true) {
                    continue;
                }

                $ready_rates[] = array(
                    'id' => $in_store_rate['id'] . '_' . $rate['id'],
                    'label' => $rate['name'],
                    'cost' => $rate['rate']['cost'],
                    'tax' => $rate['rate']['tax'] ? $rate['rate']['tax'] : 0,
                    'message' => $in_store_rate['message'],
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? new  \DateTime($rate['rate']['estimatedDeliveryDate']['from']) : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? new  \DateTime($rate['rate']['estimatedDeliveryDate']['to']) : null,
                    'priority' => $in_store_rate['priority'],
                );
            }
        }

        return $ready_rates;
    }
}
