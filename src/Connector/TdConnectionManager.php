<?php
namespace WztzTech\Iot\PhpTd\Connector;

use Exception;
use WztzTech\Iot\PhpTd\Exception\{PhpTdException, ErrorCode, ErrorMessage};

/**
 * 
 */
class TdConnectionManager {

    /**
     * 当没有配置文件选项时，
     * 默认的连接器和连接参数，将使用下面的常量定义：
     */
    const DEFAULT_CONNECTOR_CLASS = 'WztzTech\Iot\PhpTd\Connector\Restful\RestfulTdConnection';
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '6041';
    const DEFAULT_USER = 'root';
    const DEFAULT_PASS = 'taosdata';
    const DEFAULT_DB = 'log';

    const CONNECT_METHOD_NAME = 'connect';


    /**
     * 以下属性，可通过配置文件设置
     */
    public String $connector_class = '';
    public String $host = '';
    public String $port = '';
    public String $user = '';
    public String $pass = '';
    public String $default_db = '';

    public function getConnection(array $options = []) {
        $connObj = null;
        $className = empty($this->connector_class) ? self::DEFAULT_CONNECTOR_CLASS : $this->connector_class;
        try {

            $connClass = new \ReflectionClass( $className );

        } catch (Exception $ex) {
            throw new PhpTdException(
                sprintf(ErrorMessage::REFLECTION_ERR_INVALID_CLASS_NAME_MESSAGE, $className),
                ErrorCode::REFLECTION_ERR_INVALID_CLASS_NAME );
        }

        if ($connClass->hasMethod(self::CONNECT_METHOD_NAME)) {

            $connectMethod = $connClass->getMethod(self::CONNECT_METHOD_NAME);

            $connObj = $connectMethod->invoke( null, //因为 connect 是静态方法，所以实例参数传 null 即可
                empty($this->host) ? self::DEFAULT_HOST : $this->host,
                empty($this->port) ? self::DEFAULT_PORT : $this->port,
                empty($this->user) ? self::DEFAULT_USER : $this->user,
                empty($this->pass) ? self::DEFAULT_PASS : $this->pass,
                empty($this->default_db) ? self::DEFAULT_DB : $this->default_db,
                $options
            );

        } else {
            //“没有 connect 方法”，说明 ClassName 所指定的类，并未实现 ITdConnection 接口，需要报错：
            throw new PhpTdException(
                sprintf(ErrorMessage::REFLECTION_ERR_INVALID_INTERFACE_MESSAGE, $className, 'ITdConnection') , 
                ErrorCode::REFLECTION_ERR_INVALID_INTERFACE);
        }

        return $connObj;
    }
}