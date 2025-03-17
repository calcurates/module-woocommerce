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
            if (!$rate_shopping['carriers']) {
                continue;
            }
            foreach ($rate_shopping['carriers'] as $carrier) {
                if (!$carrier['rates']) {
                    continue;
                }
                foreach ($carrier['rates'] as $rate) {
                    if (!$rate['success'] && !$rate['message']) {
                        continue;
                    }

                    $services_names = [];
                    $services_messages = [];
                    $services_ids = [];
                    $services_priority = null;
                    $packages_names = [];

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

                            \array_push($packages_names, ...\array_map(static function (array $package): string {
                                return $package['name'];
                            }, $service['packages'] ?? []));
                        }
                    }

                    $services_ids = \implode('_', $services_ids);
                    $services_messages = \implode('. ', \array_unique($services_messages));
                    $services_names = \implode(', ', \array_unique($services_names));

                    $ready_rates[] = [
                        'has_error' => !$rate['success'],
                        'id' => $rate_shopping['id'].'_'.$carrier['id'].'_'.$services_ids,
                        'label' => $this->resolveLabel($carrier).'. '.$services_names,
                        'cost' => $rate['rate']['cost'] ?? 0,
                        'tax' => $rate['rate']['tax'] ?? 0,
                        'currency' => $rate['rate']['currency'] ?? '',
                        'message' => $rate['success'] ? $rate_shopping['message'].' '.$services_messages : $rate['message'],
                        'delivery_date_from' => $rate['rate']['estimatedDeliveryDate']['from'] ?? null,
                        'delivery_date_to' => $rate['rate']['estimatedDeliveryDate']['to'] ?? null,
                        'priority' => $rate_shopping['priority'],
                        'priority_item' => $services_priority,
                        'rate_image' => $rate_shopping['imageUri'],
                        'time_slots' => $rate['rate']['estimatedDeliveryDate']['timeSlots'] ?? null,
                        'days_in_transit_from' => $rate['rate']['estimatedDeliveryDate']['daysInTransitFrom'] ?? null,
                        'days_in_transit_to' => $rate['rate']['estimatedDeliveryDate']['daysInTransitTo'] ?? null,
                        'packages' => $packages_names,
                        'custom_number' => null,
                    ];
                }
            }
        }

        return $ready_rates;
    }
}
