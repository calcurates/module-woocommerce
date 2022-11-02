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
     *     tax: float|int,
     *     message: string|null,
     *     delivery_date_from: string|null,
     *     delivery_date_to: string|null,
     *     priority: int|null,
     *     priority_item: int|null,
     *     rate_image: string|null,
     * }[]
     */
    abstract public function extract(array $data): array;

    /**
     * @param array{name: string, displayName?: string|null, additionalText?: string[]|null} $rate
     */
    public function resolveLabel(array $rate): string
    {
        $label = isset($rate['displayName']) && $rate['displayName'] ? $rate['displayName'] : $rate['name'];
        if (isset($rate['additionalText']) && $rate['additionalText']) {
            $label .= '('.\implode("\n", $rate['additionalText']).')';
        }

        return $label;
    }
}