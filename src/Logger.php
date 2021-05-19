<?php

declare(strict_types=1);

namespace Calcurates;

use Psr\Log\AbstractLogger;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Logger::class)) {
    /**
     * WC_Logger wrapper.
     */
    class Logger extends AbstractLogger
    {
        /**
         * Default WooCommerce logger.
         *
         * @var \WC_Logger
         */
        private $logger;

        /**
         * Log source file.
         *
         * @var string
         */
        private $source;

        /**
         * @var self
         */
        private static $instance;

        private function __construct()
        {
            $this->logger = \wc_get_logger();
            $this->source = WCCalcurates::get_plugin_text_domain();
        }

        public static function getInstance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * {@inheritdoc}
         */
        public function log($level, $message, array $context = []): void
        {
            if ($context) {
                $message = "\n".'==== '.$message.' ===='."\n".\print_r($context, true).'====END LOG===='."\n\n";
            }

            $this->logger->log($level, $message, ['source' => $this->source]);
        }
    }
}
