<?php
namespace WztzTech\Iot\PhpTd\Exception;

class ErrorMessage {
    const REFLECTION_ERR_INVALID_CLASS_NAME_MESSAGE = "无效类名 %s";

    const REFLECTION_ERR_INVALID_INTERFACE_MESSAGE = "类：%s 未实现接口 %s";

    const NETWORK_CONNECT_ERR_MESSAGE = "网络请求失败，详情：%s";

    const TD_ENGINE_LOGIN_ERR_MESSAGE = "无法登录，错误号：%s";
}