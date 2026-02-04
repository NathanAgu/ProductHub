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
            // Creation logger
            self::$loggers[$type] = new Logger('producthub.' . $type);
            
            // Verif dossier log existe
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            
            // Log dans fichier
            $logFile = $logDir . '/' . $type . '.log';
            $handler = new StreamHandler($logFile, Logger::INFO);
            self::$loggers[$type]->pushHandler($handler);
        }
        
        return self::$loggers[$type];
    }
}