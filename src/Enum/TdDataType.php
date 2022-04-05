<?php 
namespace WztzTech\Iot\PhpTd\Enum;

use WztzTech\Iot\PhpTd\Enum\BaseEnum;

/**
 * tdengine 支持的 数据类型 枚举类。
 * 类型说明来自 tdengine 官方文档：
 * 原文链接： https://www.taosdata.com/docs/cn/v2.0/taos-sql#
 */
abstract class TdDataType extends BaseEnum
{
    /**
     * 布尔型，{true, false}
     * 1 字节
     */
    const TD_BOOL = 1;

    /**
     * 单字节整型，
     * 范围 [-127, 127], 
     * -128 用于 NULL
     */
    const TD_TINYINT = 2;

    /**
     * 短整型， 
     * 范围 [-32767, 32767], 
     * -32768 用于 NULL
     */
    const TD_SMALLINT = 3;

    /**
     * 整型，
     * 范围 [-2^31+1, 2^31-1], 
     * -2^31 用作 NULL
     */
    const TD_INT = 4;

    /**
     * 长整型，
     * 范围 [-2^63+1, 2^63-1], 
     * -2^63 用于 NULL
     */
    const TD_BIGINT = 5;

    /**
     * 浮点型，4 字节
     * 有效位数 6-7，
     * 范围 [-3.4E38, 3.4E38]
     */
    const TD_FLOAT = 6;

    /**
     * 双精度浮点型， 8 字节
     * 有效位数 15-16，
     * 范围 [-1.7E308, 1.7E308]
     */
    const TD_DOUBLE = 7;

    /**
     * 记录单字节字符串，
     * 建议只用于处理 ASCII 可见字符，
     * 中文等多字节字符需使用 nchar。
     * 理论上，最长可以有 16374 字节。
     * binary 仅支持字符串输入，字符串两端需使用单引号引用。
     * 使用时须指定大小，如 binary(20) 定义了最长为 20 个单字节字符的字符串，每个字符占 1 byte 的存储空间，
     * 总共固定占用 20 bytes 的空间，此时如果用户字符串超出 20 字节将会报错。
     * 对于字符串内的单引号，可以用转义字符反斜线加单引号来表示，即 \’。
     */
    const TD_BINARY = 8;

    /**
     * 时间戳
     * 8 字节
     * 缺省精度毫秒，可支持微秒和纳秒。
     * 从格林威治时间 1970-01-01 00:00:00.000 (UTC/GMT) 开始，
     * 计时不能早于该时间。（从 TdEngine 2.0.18.0 版本开始，已经去除了这一时间范围限制）
     * （从 TdEngine 2.1.5.0 版本开始支持纳秒精度）
     */
    const TD_TIMESTAMP = 9;

    /**
     * 记录包含多字节字符在内的字符串，如中文字符。
     * 每个 nchar 字符占用 4 bytes 的存储空间。
     * 字符串两端使用单引号引用，字符串内的单引号需用转义字符 \’。
     * nchar 使用时须指定字符串大小，类型为 nchar(10) 的列表示此列的字符串最多存储 10 个 nchar 字符，
     * 会固定占用 40 bytes 的空间。如果用户字符串长度超出声明长度，将会报错。
     */
    const TD_NCHAR = 10;

    /**
     * json数据类型， 只有tag类型可以是json格式
     */
    const TD_JSON = 11;
}