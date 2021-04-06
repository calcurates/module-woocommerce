<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class RateShoppingRatesExtractor implements RatesExtractorInterface
{
    public function extract(array $rate_shopping_rates): array
    {
        $ready_rates = [];

        foreach ($rate_shopping_rates as $rate_shopping) {
            if (true !== $rate_shopping['success']) {
                continue;
            }

            foreach ($rate_shopping['carriers'] as $carrier) {
                if (true !== $carrier['success']) {
                    continue;
                }
                foreach ($carrier['rates'] as $rate) {
                    if (true !== $rate['success']) {
                        continue;
                    }

                    $services_names = [];
                    $services_messages = [];
                    $services_ids = [];

                    foreach ($rate['services'] as $services) {
                        if ($services['message']) {
                            $services_messages[] = $services['message'];
                        }

                        $services_ids[] = $services['id'];
                        $services_names[] = $services['name'];
                    }

                    $services_messages = \implode('. ', $services_messages);
                    $services_ids = \implode('_', $services_ids);
                    $services_names = \implode(', ', $services_names);

                    $ready_rates[] = [
                        'id' => $rate_shopping['id'].'_'.$carrier['id'].'_'.$services_ids,
                        'label' => $carrier['name'].'. '.$services_names,
                        'cost' => $rate['rate']['cost'],
                        'tax' => $rate['rate']['tax'] ? $rate['rate']['tax'] : 0,
                        'message' => $rate_shopping['message'].' '.$services_messages,
                        'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                        'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                        'priority' => $rate_shopping['priority'],
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
