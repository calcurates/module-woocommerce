<?php

declare(strict_types=1);

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
        $ready_rates = [];

        foreach ($table_rates as $table_rate) {
            if (true !== $table_rate['success']) {
                continue;
            }

            foreach ($table_rate['methods'] as $rate) {
                if (true !== $rate['success']) {
                    continue;
                }

                $ready_rates[] = [
                    'id' => $table_rate['id'].'_'.$rate['id'],
                    'label' => $rate['name'],
                    'cost' => $rate['rate']['cost'],
                    'tax' => $rate['rate']['tax'] ?: 0,
                    'message' => $table_rate['message'],
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $table_rate['priority'],
                ];
            }
        }

        return $ready_rates;
    }
}
