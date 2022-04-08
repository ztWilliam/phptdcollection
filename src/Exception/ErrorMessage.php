<?php
namespace WztzTech\Iot\PhpTd\Exception;

class ErrorMessage {
    const REFLECTION_ERR_INVALID_CLASS_NAME_MESSAGE = "无效类名 %s";

    const REFLECTION_ERR_INVALID_INTERFACE_MESSAGE = "类：%s 未实现接口 %s";

    const NETWORK_CONNECT_ERR_MESSAGE = "网络请求失败，详情：%s";

    const TD_ENGINE_LOGIN_ERR_MESSAGE = "无法登录，错误号：%s";

    const TD_ENGINE_CONNECTION_CLOSED_ERR_MESSAGE = "tdengine 连接已关闭";

    const TD_TAOS_SQL_EMPTY_ERR_MESSAGE = "没有要执行的 taos sql 命令";
}