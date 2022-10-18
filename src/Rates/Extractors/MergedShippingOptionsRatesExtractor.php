<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class MergedShippingOptionsRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $rates): array
    {
        $ready_rates = [];

        foreach ($rates as $rate) {
            if ($rate['success']) {
                $ready_rates[] = [
                    'has_error' => false,
                    'id' => $rate['id'],
                    'label' => $rate['name'],
                    'cost' => $rate['rate']['cost'] ?? 0,
                    'tax' => $rate['rate']['tax'] ?? 0,
                    'message' => null,
                    'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                    'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                    'priority' => null,
                    'priority_item' => null,
                    'rate_image' => null,
                ];
            }
        }

        return $ready_rates;
    }
}
