<?php

declare(strict_types=1);

namespace Calcurates;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(Logger::class)) {
    /**
     * WC_Logger wrapper.
     *
     * PSR-3
     * But the WooCommerce infrastructure found the malware in the https://github.com/php-fig/log/blob/1.1.4/Psr/Log/Test/LoggerInterfaceTest.php file
     * Currently we can't use the PSR interface correctly
     *
     * @see SAAS-2486
     */
    class Logger
    {
        public const EMERGENCY = 'emergency';
        public const ALERT = 'alert';
        public const CRITICAL = 'critical';
        public const ERROR = 'error';
        public const WARNING = 'warning';
        public const NOTICE = 'notice';
        public const INFO = 'info';
        public const DEBUG = 'debug';

        /**
         * Default WooCommerce logger.
         */
        private \WC_Logger_Interface $logger;

        /**
         * Log source file.
         */
        private string $source;

        private static ?self $instance = null;

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
         * Logs with an arbitrary level.
         *
         * @param mixed[] $context
         */
        public function log($level, string $message, array $context = []): void
        {
            if ($context) {
                $message = "\n".'==== '.$message.' ===='."\n".\print_r($context, true).'====END LOG===='."\n\n";
            }

            $this->logger->log($level, $message, ['source' => $this->source]);
        }

        /**
         * System is unusable.
         *
         * @param mixed[] $context
         */
        public function emergency(string $message, array $context = []): void
        {
            $this->log(self::EMERGENCY, $message, $context);
        }

        /**
         * Action must be taken immediately.
         *
         * Example: Entire website down, database unavailable, etc. This should
         * trigger the SMS alerts and wake you up.
         *
         * @param mixed[] $context
         */
        public function alert(string $message, array $context = []): void
        {
            $this->log(self::ALERT, $message, $context);
        }

        /**
         * Critical conditions.
         *
         * Example: Application component unavailable, unexpected exception.
         *
         * @param mixed[] $context
         */
        public function critical(string $message, array $context = []): void
        {
            $this->log(self::CRITICAL, $message, $context);
        }

        /**
         * Runtime errors that do not require immediate action but should typically
         * be logged and monitored.
         *
         * @param mixed[] $context
         */
        public function error(string $message, array $context = []): void
        {
            $this->log(self::ERROR, $message, $context);
        }

        /**
         * Exceptional occurrences that are not errors.
         *
         * Example: Use of deprecated APIs, poor use of an API, undesirable things
         * that are not necessarily wrong.
         *
         * @param mixed[] $context
         */
        public function warning(string $message, array $context = []): void
        {
            $this->log(self::WARNING, $message, $context);
        }

        /**
         * Normal but significant events.
         *
         * @param mixed[] $context
         */
        public function notice(string $message, array $context = []): void
        {
            $this->log(self::NOTICE, $message, $context);
        }

        /**
         * Interesting events.
         *
         * Example: User logs in, SQL logs.
         *
         * @param mixed[] $context
         */
        public function info(string $message, array $context = []): void
        {
            $this->log(self::INFO, $message, $context);
        }

        /**
         * Detailed debug information.
         *
         * @param mixed[] $context
         */
        public function debug(string $message, array $context = []): void
        {
            $this->log(self::DEBUG, $message, $context);
        }
    }
}
