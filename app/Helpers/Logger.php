<?php
namespace App\Helpers;

class Logger {
    public static function info($msg) {
        file_put_contents(__DIR__.'/../../logs/app.log',
            "[".date('Y-m-d H:i:s')."] ".$msg.PHP_EOL,
            FILE_APPEND
        );
    }
}
