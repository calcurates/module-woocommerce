<?php

declare(strict_types=1);

namespace Calcurates\Utils;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * WC_Logger wrapper.
 */
class Logger
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

    public function __construct()
    {
        $this->logger = \wc_get_logger();
        $this->source = Basic::get_plugin_text_domain();
    }

    /**
     * Add to logs.
     *
     * @param string $level One of the following:
     *                      'emergency': System is unusable.
     *                      'alert': Action must be taken immediately.
     *                      'critical': Critical conditions.
     *                      'error': Error conditions.
     *                      'warning': Warning conditions.
     *                      'notice': Normal but significant condition.
     *                      'info': Informational messages.
     *                      'debug': Debug-level messages.
     */
    private function log(string $level, string $message = '', array $data = []): void
    {
        if ($data) {
            $message = "\n".'==== '.$message.' ===='."\n".\print_r($data, true).'====END LOG===='."\n\n";
        }

        $this->logger->log($level, $message, ['source' => $this->source]);
    }

    /**
     * Add to logs as debug info.
     */
    public function debug(string $message, array $data = []): void
    {
        $this->log('debug', $message, $data);
    }

    /**
     * Add to logs as critical info.
     */
    public function critical(string $message, array $data = []): void
    {
        $this->log('critical', $message, $data);
    }
}
