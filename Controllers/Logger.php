<?php
namespace Calcurates\Controllers;

use Katzgrau\KLogger\Logger as KLogger;

class Logger
{
    public static function log($title = '', $data = [])
    {
        $logger = new KLogger(plugin_dir_path(__DIR__) . 'logs');
        $logger->info($title, $data);
    }

}
