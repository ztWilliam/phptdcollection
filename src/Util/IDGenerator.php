<?php
namespace WztzTech\Iot\PhpTd\Util;

class IDGenerator {

    public static function uniqID() {
        //todo 从配置文件中取“数据中心”序号
        $dataCenter = 'dc001_';

        return uniqid($dataCenter, true);
    }

    public static function uniqID32() {
        return md5(self::uniqID());
    }
}