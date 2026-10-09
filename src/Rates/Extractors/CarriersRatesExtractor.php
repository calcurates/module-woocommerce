<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class CarriersRatesExtractor extends RatesExtractorAbstract
{
    public function extract(array $data): array
    {
        $ready_rates = [];

        foreach ($data as $carrier) {
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
                        $services_names[] = $this->resolve_label($service);
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

                $ready_rates[] = \array_merge([
                    'has_error' => !$rate['success'],
                    'id' => $carrier['id'].'_'.$services_ids,
                    'label' => $this->resolve_label($carrier).'. '.$services_names,
                    'message' => $rate['success'] ? $carrier['message'].' '.$services_messages : $rate['message'],
                    'priority' => $carrier['priority'],
                    'priority_item' => $services_priority,
                    'rate_image' => $carrier['imageUri'],
                    'packages' => $packages_names,
                    'custom_number' => null,
                ], $this->extract_rate_fields($rate['rate'] ?? null));
            }
        }

        return $ready_rates;
    }
}
