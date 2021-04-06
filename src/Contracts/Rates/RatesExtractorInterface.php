<?php

declare(strict_types=1);

namespace Calcurates\Contracts\Rates;

interface RatesExtractorInterface
{
    /**
     * Extract rates.
     */
    public function extract(array $rates): array;
}
