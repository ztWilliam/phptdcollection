<?php
namespace WztzTech\Iot\PhpTd\Exception;

class ErrorMessage {
    const REFLECTION_ERR_INVALID_CLASS_NAME_MESSAGE = "无效类名 %s";

    const REFLECTION_ERR_INVALID_INTERFACE_MESSAGE = "类：%s 未实现接口 %s";

    const NETWORK_CONNECT_ERR_MESSAGE = "网络请求失败，详情：%s";

    const TD_ENGINE_LOGIN_ERR_MESSAGE = "无法登录，错误号：%s";

    const TD_ENGINE_CONNECTION_CLOSED_ERR_MESSAGE = "tdengine 连接已关闭";

    const TD_TAOS_SQL_EMPTY_ERR_MESSAGE = "没有要执行的 taos sql 命令";

    const TD_TAOS_SQL_RESULT_NULL_ERR_MESSAGE = "taos sql 结果为空。必须是有效的json字符串";

    const TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE = "taos sql 执行失败，错误信息：%s";

    const RESULT_ROW_INDEX_OUT_OF_RANGE_ERR_MESSAGE = "数据行号 %s 超出范围：[%s, %s]";

    const NAME_EXISTS_ERR_MESSAGE = "已存在名为【%s】的记录";

    const META_REGISTER_FAILED_ERR_MESSAGE = "%s 注册失败，详情：%s";

    const PARAM_OR_FIELD_EMPTY_ERR_MESSAGE = "【%s】不能为空";

    const BASE_OBJECT_NOT_FOUND_ERR_MESSAGE = "【%s】的 %s 记录不存在";

}