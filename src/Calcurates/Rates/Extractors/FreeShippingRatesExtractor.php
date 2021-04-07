<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FreeShippingRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $rates): array
    {
        $ready_rates = [];

        foreach ($rates as $rate) {
            if (true !== $rate['success']) {
                continue;
            }

            $ready_rates[] = [
                'id' => $rate['id'],
                'label' => $rate['name'],
                'cost' => $rate['rate']['cost'],
                'tax' => $rate['rate']['tax'] ? $rate['rate']['tax'] : 0,
                'message' => $rate['message'],
                'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                'priority' => $rate['priority'],
            ];
        }

        return $ready_rates;
    }
}
