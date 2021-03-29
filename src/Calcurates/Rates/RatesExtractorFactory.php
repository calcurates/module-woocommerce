<?php
namespace Calcurates\Calcurates\Rates;

use Calcurates\Contracts\Rates\RatesExtractorInterface;
use Calcurates\Utils\Logger;

/**
 * Factory for extractors
 */
class RatesExtractorFactory
{
    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function create(string $rate_name = null)
    {
        $extractor = __NAMESPACE__ . '\\' . ucfirst($rate_name) . 'RatesExtractor';
        if (class_exists($extractor)) {
            $extractor_instance = new $extractor();
            if ($extractor_instance instanceof RatesExtractorInterface) {
                return $extractor_instance;
            }
        }

        $error = "Class $extractor doesn't exists or it's not implementing RatesExtractorInterface interface";

        $this->logger->critical($error);

        throw new \Exception($error);

    }
}
