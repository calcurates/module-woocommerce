<?php
namespace Calcurates\Utils;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

/**
 * WC_Logger wrapper
 */
class Logger
{
    /**
     * Defult WooCommerce logger
     *
     * @var \WC_Logger
     */
    private $logger;

    /**
     * Log source file
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
     * Add to logs
     *
     * @param string $type
     * @param string $title
     * @param array $data
     * @return void
     */
    private function log(string $type, string $title = '', array $data = []): void
    {
        if (\is_array($data) && !empty($data)) {
            $log = "\n" . "==== " . $title . " ====" . "\n" . \print_r($data, true) . "====END LOG====" . "\n\n";
            $this->logger->$type($log, ['source' => $this->source]);
        } else {
            $this->logger->$type($title, ['source' => $this->source]);
        }
    }

    /**
     * Add to logs as debug info
     *
     * @param string $title
     * @param array $data
     * @return void
     */
    public function debug(string $title = '', array $data = []): void
    {
        $this->log('debug', $title, $data);
    }

    /**
     * Add to logs as critical info
     *
     * @param string $title
     * @param array $data
     * @return void
     */
    public function critical(string $title = '', array $data = []): void
    {
        $this->log('critical', $title, $data);
    }
}
