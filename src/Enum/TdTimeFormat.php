<?php
namespace WztzTech\Iot\PhpTd\Enum;

/**
 * tdengine 支持的时间返回格式
 * 
 */
class TdTimeFormat extends BaseEnum {
    /**
     * 当前时区时间字符串
     * 例如：
     * "2018-10-03 14:38:05.000"
     */
    const LOCAL_TIME = 'local';

    /**
     * UTC时间字符串
     * 例如：
     * "2018-10-03T14:38:05.000+0800"
     */
    const UTC_TIME = 'utc';

    /**
     * Unix时间戳
     * 例如：
     * 1538548685000
     */
    const TIME_STAMP = 'ts';
}