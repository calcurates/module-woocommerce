<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

abstract class RatesExtractorAbstract
{
    /**
     * @return array{
     *     has_error: bool,
     *     id: string,
     *     label: string,
     *     cost: float|int,
     *     tax: float|int|null,
     *     insurance_amount: float|int|null,
     *     message: string|null,
     *     delivery_date_from: string|null,
     *     delivery_date_to: string|null,
     *     priority: int|null,
     *     priority_item: int|null,
     *     rate_image: string|null,
     *     time_slots: array|null,
     *     currency: string,
     *     days_in_transit_from: int|null,
     *     days_in_transit_to: int|null,
     *     packages: string[],
     *     custom_number: float|null,
     *     final_rate: float|int|null,
     *     base_rate: float|int|null,
     *     surcharge: float|int|null,
     *     discount: float|int|null,
     *     surcharge_rules: array<int|string, float|int>,
     *     discount_rules: array<int|string, float|int>,
     * }[]
     */
    abstract public function extract(array $data): array;

    /**
     * Map the shared Calcurates rate payload onto the plugin rate fields.
     *
     * @param array<string, mixed>|null $rate
     *
     * @return array{
     *     cost: float|int,
     *     tax: float|int|null,
     *     insurance_amount: float|int|null,
     *     currency: string,
     *     delivery_date_from: string|null,
     *     delivery_date_to: string|null,
     *     time_slots: array|null,
     *     days_in_transit_from: int|null,
     *     days_in_transit_to: int|null,
     *     final_rate: float|int|null,
     *     base_rate: float|int|null,
     *     surcharge: float|int|null,
     *     discount: float|int|null,
     *     surcharge_rules: array<int|string, float|int>,
     *     discount_rules: array<int|string, float|int>,
     * }
     */
    protected function extract_rate_fields(?array $rate): array
    {
        if (!\is_array($rate)) {
            $rate = null;
        }

        $delivery_date = \is_array($rate['estimatedDeliveryDate'] ?? null) ? $rate['estimatedDeliveryDate'] : [];

        return [
            'cost' => $rate['cost'] ?? 0,
            'tax' => $rate['tax'] ?? null,
            'insurance_amount' => $rate['insurance'] ?? null,
            'currency' => $rate['currency'] ?? '',
            'delivery_date_from' => $delivery_date['from'] ?? null,
            'delivery_date_to' => $delivery_date['to'] ?? null,
            'time_slots' => $delivery_date['timeSlots'] ?? null,
            'days_in_transit_from' => $delivery_date['daysInTransitFrom'] ?? null,
            'days_in_transit_to' => $delivery_date['daysInTransitTo'] ?? null,
            'final_rate' => $rate['cost'] ?? null,
            'base_rate' => $rate['baseRate'] ?? null,
            'surcharge' => $rate['surcharge'] ?? null,
            'discount' => $rate['discount'] ?? null,
            'surcharge_rules' => \is_array($rate['surchargeRules'] ?? null) ? $rate['surchargeRules'] : [],
            'discount_rules' => \is_array($rate['discountRules'] ?? null) ? $rate['discountRules'] : [],
        ];
    }

    /**
     * @param array{name: string, displayName?: string|null, additionalText?: string[]|null} $rate
     */
    public function resolve_label(array $rate): string
    {
        $label = isset($rate['displayName']) && $rate['displayName'] ? $rate['displayName'] : $rate['name'];
        if (isset($rate['additionalText']) && $rate['additionalText']) {
            $label .= '('.\implode("\n", $rate['additionalText']).')';
        }

        return $label;
    }
}
