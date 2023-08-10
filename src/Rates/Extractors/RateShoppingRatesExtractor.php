<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class RateShoppingRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $rate_shopping) {
            foreach ($rate_shopping['carriers'] as $carrier) {
                foreach ($carrier['rates'] as $rate) {
                    $services_names = [];
                    $services_messages = [];
                    $services_ids = [];
                    $services_priority = null;

                    if ($rate['success']) {
                        foreach ($rate['services'] as $service) {
                            if ($service['message']) {
                                $services_messages[] = $service['message'];
                            }

                            $services_ids[] = $service['id'];
                            $services_names[] = $this->resolveLabel($service);
                            if (null !== $service['priority']) {
                                $services_priority += $service['priority'];
                            }
                        }
                    }

                    $services_ids = \implode('_', $services_ids);
                    $services_messages = \implode('. ', \array_unique($services_messages));
                    $services_names = \implode(', ', \array_unique($services_names));

                    if ($rate['success'] || $rate['message']) {
                        $cost = $rate['rate']['cost'] ?? 0;
                        $tax = $rate['rate']['tax'] ?? 0;

                        $ready_rates[] = [
                            'has_error' => !$rate['success'],
                            'id' => $rate_shopping['id'].'_'.$carrier['id'].'_'.$services_ids,
                            'label' => $this->resolveLabel($carrier).'. '.$services_names,
                            'cost' => $rate_shopping['splitTaxAndCost'] ? $cost : $cost + $tax,
                            'tax' => $rate_shopping['splitTaxAndCost'] ? $tax : 0,
                            'message' => $rate['success'] ? $rate_shopping['message'].' '.$services_messages : $rate['message'],
                            'delivery_date_from' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['from'] : null,
                            'delivery_date_to' => isset($rate['rate']['estimatedDeliveryDate']) ? $rate['rate']['estimatedDeliveryDate']['to'] : null,
                            'priority' => $rate_shopping['priority'],
                            'priority_item' => $services_priority,
                            'rate_image' => $rate_shopping['imageUri'],
                            'time_slots' => $rate['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                        ];
                    }
                }
            }
        }

        return $ready_rates;
    }
}
