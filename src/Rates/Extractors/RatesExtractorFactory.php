<?php

declare(strict_types=1);

namespace Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Factory for extractors.
 */
class RatesExtractorFactory
{
    public function create(string $rate_name): RatesExtractorInterface
    {
        $extractor = __NAMESPACE__.'\\'.\ucfirst($rate_name).'RatesExtractor';
        if (\class_exists($extractor)) {
            $extractor_instance = new $extractor();
            if ($extractor_instance instanceof RatesExtractorInterface) {
                return $extractor_instance;
            }
        }

        $error = "Class $extractor doesn't exists or it's not implementing RatesExtractorInterface interface";

        Logger::getInstance()->critical($error);

        throw new \RuntimeException($error);
    }
}
