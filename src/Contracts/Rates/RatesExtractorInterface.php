<?php
namespace Calcurates\Contracts\Rates;

interface RatesExtractorInterface
{
    /**
     * Extract rates
     */
    public function extract(array $rates): array;
}
