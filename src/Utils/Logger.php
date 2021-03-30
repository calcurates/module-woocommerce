<?php
namespace Calcurates\Utils;

use Calcurates\Basic;

// Stop direct HTTP access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Logger Wrapper
 */
class Logger
{
    private $logger;
    private $source;

    public function __construct()
    {
        $this->logger = \wc_get_logger();
        $this->source = Basic::get_plugin_text_domain();
    }

    /**
     * log
     *
     * @param  text $title
     * @param  array $data
     * @return void
     */
    private function log(string $type, string $title = '', array $data = [])
    {

        if (is_array($data) && !empty($data)) {
            $log = "\n" . "==== " . $title . " ====" . "\n" . print_r($data, true) . "====END LOG====" . "\n\n";
            $this->logger->$type($log, ['source' => $this->source]);
        } else {
            $this->logger->$type($title, ['source' => $this->source]);
        }

    }

    public function debug(string $title = '', array $data = [])
    {
        $this->log('debug', $title, $data);
    }

    public function critical(string $title = '', array $data = [])
    {
        $this->log('critical', $title, $data);
    }

}
