<?php
namespace Calcurates\Utils;

use Katzgrau\KLogger\Logger as KLogger;

class Logger
{
    private $logger;

    public function __construct()
    {
        $this->logger = new KLogger(plugin_dir_path(__DIR__) . 'logs');
    }

    /**
     * log
     *
     * @param  text $title
     * @param  array $data
     * @return void
     */
    public function log(string $title = '', array $data = [])
    {
        $this->logger->info($title, $data);
    }

}
