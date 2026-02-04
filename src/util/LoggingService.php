<?php
namespace Util;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingService
{
    private static $loggers = [];
    
    public static function getProductLogger(): Logger
    {
        return self::getLogger('products');
    }
    
    public static function getCartLogger(): Logger
    {
        return self::getLogger('carts');
    }
    
    private static function getLogger(string $type): Logger
    {
        if (!isset(self::$loggers[$type])) { 
            // Create logger
            self::$loggers[$type] = new Logger('producthub.' . $type);
            
            // Ensure logs directory exists
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            
            // Log to file
            $logFile = $logDir . '/' . $type . '.log';
            $handler = new StreamHandler($logFile, Logger::INFO);
            self::$loggers[$type]->pushHandler($handler);
            
            // Optional: Also log to console in development
            if (php_sapi_name() === 'cli' || isset($_GET['debug'])) {
                $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
                self::$loggers[$type]->pushHandler($consoleHandler);
            }
        }
        
        return self::$loggers[$type];
    }
}