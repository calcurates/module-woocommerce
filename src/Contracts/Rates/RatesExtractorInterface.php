<?php
namespace Calcurates\Contracts\Rates;

interface RatesExtractorInterface
{
    public function extract(array $rates);
}
