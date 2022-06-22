<?php
namespace WztzTech\Iot\PhpTd\Exception;

class ErrorCode {

    const REFLECTION_ERR_INVALID_CLASS_NAME = -1;

    const REFLECTION_ERR_INVALID_INTERFACE = -2;

    const NETWORK_CONNECT_ERR = -10;

    const NAME_EXISTS_ERR = -20;

    const PARAM_OR_FIELD_EMPTY_ERR = -21;

    const TD_ENGINE_LOGIN_ERR = -300;

    const TD_ENGINE_CONNECTION_CLOSED_ERR = -301;

    const TD_TAOS_SQL_EMPTY_ERR = -401;

    const TD_TAOS_SQL_RESULT_NULL_ERR = -404;

    const TD_TAOS_SQL_EXECUTE_FAILED_ERR = -405;

    const RESULT_ROW_INDEX_OUT_OF_RANGE_ERR = -501;

    const META_REGISTER_FAILED_ERR = -601;


}