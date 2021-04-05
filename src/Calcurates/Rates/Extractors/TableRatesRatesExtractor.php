<?php

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class TableRatesRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $table_rates): array
    {
        $ready_rates = array();

        foreach ($table_rates as $table_rate) {
            if ($table_rate['success'] !== true) {
                continue;
            }

            foreach ($table_rate['methods'] as $rate) {
                if ($rate['success'] !== true) {
                    continue;
                }

                $ready_rates[] = array(
                    'id' => $table_rate['id'] . '_' . $rate['id'],
                    'label' => $rate['name'],
                    'cost' => $rate['rate']['cost'],
                    'tax' => $rate['rate']['tax'] ? $rate['rate']['tax'] : 0,
                    'message' => $table_rate['message'],
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $table_rate['priority'],
                );
            }
        }

        return $ready_rates;
    }
}
