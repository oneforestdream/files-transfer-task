<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class CustomLog extends Facade
{
    /**
     * Get Facade Accessor for Current Class
     *
     * @return string Accessor
     */
    protected static function getFacadeAccessor()
    {
        return 'custom.log';
    }

    public static function __callStatic($method, $args)
    {
        if (! isset($args[0]))
            return;

        $message = $args[0];
        $file = isset($args[1]) ? $args[1] : 'laravel';
        $max = 10000;
        $logger = Log::getMonolog();
        $path = storage_path('logs/'.$file.'.log');
        if (! file_exists($path))
            fopen($path, "w");

        $lines = file($path);
        if (count($lines) >= $max)
            file_put_contents($path, implode('', array_slice($lines, -$max, $max)));

        $handler = new StreamHandler($path, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $logger->setHandlers([$handler]);
        $logger->$method($message);
    }
}