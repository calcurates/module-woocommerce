<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class FlatRatesRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $rates): array
    {
        $ready_rates = [];

        foreach ($rates as $rate) {
            if ($rate['success'] || (!$rate['success'] && $rate['message'])) {
                $ready_rates[] = [
                    'has_error' => !$rate['success'],
                    'id' => $rate['id'],
                    'label' => $rate['name'],
                    'cost' => $rate['rate']['cost'] ?? 0,
                    'tax' => $rate['rate']['tax'] ?? 0,
                    'message' => $rate['message'],
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => $rate['priority'],
                    'rate_image' => $rate['imageUri'],
                ];
            }
        }

        return $ready_rates;
    }
}
