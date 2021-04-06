<?php

declare(strict_types=1);

namespace Calcurates\Calcurates\Rates\Extractors;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Factory for extractors.
 */
class RatesExtractorFactory
{
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function create(string $rate_name)
    {
        $extractor = __NAMESPACE__.'\\'.\ucfirst($rate_name).'RatesExtractor';
        if (\class_exists($extractor)) {
            $extractor_instance = new $extractor($this->logger);
            if ($extractor_instance instanceof RatesExtractorInterface) {
                return $extractor_instance;
            }
        }

        $error = "Class $extractor doesn't exists or it's not implementing RatesExtractorInterface interface";

        $this->logger->critical($error);

        throw new \RuntimeException($error);
    }
}
