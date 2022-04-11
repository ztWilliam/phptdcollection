<?php
namespace WztzTech\Iot\PhpTd\Enum;

/**
 * 此枚举项用于设置 tdengine 的数据库是否支持更新相同时间戳数据。
 * 因为此选项在数据库创建之后，不能通过 alter db 命令修改，
 * 所以在创建时应根据采集数据源的特点来谨慎选择。
 * 
 */
class TdUpdateMode extends BaseEnum {

    /**
     * 表示不允许更新数据，后发送的相同时间戳的数据会被直接丢弃
     */
    const DISABLE = 0;

    /**
     * 表示更新全部列数据，
     * 即如果更新一个数据行，其中某些列没有提供取值，那么这些列会被设为 NULL
     */
    const UPDATE_ALL = 1;

    /**
     * 表示支持更新部分列数据，
     * 即如果更新一个数据行，其中某些列没有提供取值，那么这些列会保持原有数据行中的对应值
     */
    const UPDATE_PART = 2;
}