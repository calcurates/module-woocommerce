<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

class RatesExtractorFactory
{
    public function create(string $rate_name): RatesExtractorAbstract
    {
        $extractor = __NAMESPACE__.'\\'.\ucfirst($rate_name).'RatesExtractor';
        if (\class_exists($extractor)) {
            $extractor_instance = new $extractor();
            if ($extractor_instance instanceof RatesExtractorAbstract) {
                return $extractor_instance;
            }
        }

        throw new RatesExtractorException("Class $extractor doesn't exists or it's not extends the RatesExtractorAbstract class");
    }
}
