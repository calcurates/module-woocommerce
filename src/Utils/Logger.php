<?php
namespace Calcurates\Utils;

use Calcurates\Basic;
use Katzgrau\KLogger\Logger as KLogger;

class Logger
{
    private $logger;

    public function __construct()
    {
        $this->logger = new KLogger(Basic::get_plugin_dir_path() . 'logs');
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
