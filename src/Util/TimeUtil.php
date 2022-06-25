<?php
namespace WztzTech\Iot\PhpTd\Util;

class TimeUtil {

    public static function getMiliSeconds() {
        $time = explode (" ", microtime () );   
        $time = $time [1] . ($time [0] * 1000);   
        $time2 = explode ( ".", $time );   
        $time = $time2 [0];  
        return $time;  
    }

    public static function getMicroSeconds() {
        $time = explode (" ", microtime () );   
        $time = $time [1] . ($time [0] * 1000 * 1000);   
        $time2 = explode ( ".", $time );   
        $time = $time2 [0];  
        return $time;  
    }

}