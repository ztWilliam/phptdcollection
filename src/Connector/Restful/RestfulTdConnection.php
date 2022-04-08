<?php
namespace WztzTech\Iot\PhpTd\Connector\Restful;

use PhpParser\Node\Expr\Cast\String_;
use WztzTech\Iot\PhpTd\Util\HttpClient;

use WztzTech\Iot\PhpTd\Enum\TdTimeFormat;

use WztzTech\Iot\PhpTd\Connector\{ITdConnection, ITdResult, ITdQueryResult};
use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage};
use WztzTech\Iot\PhpTd\Exception\PhpTdException;

use function PHPUnit\Framework\isNull;

class RestfulTdConnection implements ITdConnection {

    /**
     * 登录tdengine时的curl命令。
     * 参数依次为：host, port, user, password
     */
    const CONNECTION_STRING_TEMPLATE = "%s:%s/rest/login/%s/%s";

    /**
     * 执行命令（无论查询类的还是写入类的）时用到的url
     * 时间返回格式为 TdTimeFormat::LOCAL_TIME 类型的
     * 参数依次为：host, port 
     */
    const EXEC_SQL_URL_TEMPLATE = "%s:%s/rest/sql";

    /**
     * 时间返回格式为 TdTimeFormat::TIME_STAMP 类型的，
     * 其余同 EXEC_SQL_URL_TEMPLATE
     */
    const EXEC_SQLT_URL_TEMPLATE = "%s:%s/rest/sqlt";

    /**
     * 时间返回格式为 TdTimeFormat::UTC_TIME 类型的，
     * 其余同 EXEC_SQL_URL_TEMPLATE
     */
    const EXEC_SQLUTC_URL_TEMPLATE = "%s:%s/rest/sqlutc";

    /**
     * 执行命令时，需在header中添加的身份认证信息
     * 参数为 authCode
     */
    const EXEC_HEADER_TEMPLATE = "Taosd %s";

    private $authCode = '';
    private $host = '';
    private $port = '';
    private $defaultDb = '';

    private $connected = False;

    private HttpClient $client;

    private $options = [
        'RESULT_TIME_FORMAT' => TdTimeFormat::TIME_STAMP,   //时间字段的返回形式，可选项为：TdTimeFormat的枚举项
    ];

    /**
     * 建立一个TdEngine 连接。
     * 
     * @param String $host TDengine 服务端所在IP
     * @param String $port TDengine 开放的连接端口
     * @param String $user 用户名
     * @param String $pass 密码
     * @param String $defaultDb 默认Db Name，可选
     * @param array $options 可通过 Key-Value 数组，对连接选项进行初始化
     * @param object $tdClient tdengine 的客户端连接器，默认值为null，仅当外部想传入一个已有的client时（例如“连接池资源共享”）才需要赋值，否则不需要传参
     * 
     * @return ITdConnection | null 若连接成功，返回一个 ITdConnection对象，若连接失败，则返回null
     */
    public static function connect(String $host, String $port, 
                                    String $user, String $pass, String $defaultDb = '', 
                                    array $options = [], object $tdClient = null) : ITdConnection {

        $newConn = new static();
        $newConn->host = $host;
        $newConn->port = $port;
        $newConn->defaultDb = $defaultDb;

        //将传入的options合并到默认选项组中，键名相同的值会被传入的options中的覆盖：
        $newConn->options = array_merge($newConn->options, $options);

        //测试登录连接
        //如果有外部传入的
        $newConn->client = is_null($tdClient) ? new HttpClient() : $tdClient;
        $url = sprintf(self::CONNECTION_STRING_TEMPLATE, $host, $port, $user, $pass);

        try {
            $response = $newConn->client->send(
                $url, HttpClient::METHOD_GET, [], '', [], null, null, null, 
                [HttpClient::RESULT_OPTION_JSON] );
            
        } catch(\Throwable $ex) {
            throw new PhpTdException(
                sprintf(ErrorMessage::NETWORK_CONNECT_ERR_MESSAGE, $ex->getMessage()),
                ErrorCode::NETWORK_CONNECT_ERR
            );
        }

        if (is_null($response)) {
            throw new PhpTdException(
                sprintf(ErrorMessage::NETWORK_CONNECT_ERR_MESSAGE, '结果为空'),
                ErrorCode::NETWORK_CONNECT_ERR
            );
        }

        //登录成功后，将获取的 authCode 存入新的对象
        if ($response->code == 0) {
            $newConn->authCode = $response->desc;
            $newConn->connected = True;
        } else {
            //登录失败，将错误信息一并抛出
            throw new PhpTdException(
                sprintf(ErrorMessage::TD_ENGINE_LOGIN_ERR_MESSAGE, $response->code),
                ErrorCode::TD_ENGINE_LOGIN_ERR
            );
        }

        //返回新对象。
        return $newConn;
    }

    /**
     * 销毁连接相关信息，
     * 断开连接。
     * 
     */
    public function close() : void {
        $this->host = '';
        $this->port = '';
        $this->authCode = '';
        $this->defaultDb = '';

        $this->connected = False;
    }

    /**
     * 切换默认的Db。
     * 在执行命令时，若未明确指定db，则连接器使用默认的db。
     * 若未设置默认db，则在执行必须要指明db的操作或查询命令时，如不提供db，将可能会执行失败。
     * 
     * @param String $dbName 要切换的默认dbName
     * 
     * @return ITdConnection 将自身对象返回，以便支持 -> 链式调用
     */
    public function withDefaultDb(String $dbName) : ITdConnection {

        $this->defaultDb = $dbName;

        return $this;
    }

    /**
     * 在执行命令时，动态指定连接的选项。
     * 被指定的选项，将会在连接对象存续期间持续生效。
     * 
     * @param array $options 需要修改的选项的 key-value 数组
     * 
     * @return ITdConnection 将自身对象返回，以便支持 -> 链式调用
     */
    public function withOptions(array $options) : ITdConnection {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 执行非查询类的操作命令。
     * 虽然有些连接器，也可以通过exec来执行查询类的指令，但仍然建议调用时做好区分，以增强调用代码的可读性。
     * 
     * @param String $taosql 要执行的 taos sql 命令
     * @param String $dbName 可选的，当前这条命令作用于哪个db。若不指定，则使用默认db。 
     * （注意：这里指定的dbName并不会修改连接器的默认db，只影响本次执行所影响的db）
     * 
     * @return ITdResult
     */
    public function exec(String $taosql, String $dbName = '') : ITdResult {

        $response = $this->execTaosSql($taosql, $dbName);

        return RestfulTdResult::parseResult($response);
    }

    /**
     * 执行查询类的命令
     * 
     * @param String $taosql 要执行的 taos sql 命令
     * @param String $dbName 可选的，当前这条命令作用于哪个db。若不指定，则使用默认db。 
     * （注意：这里指定的dbName并不会修改连接器的默认db，只影响本次执行所影响的db）
     * 
     * @return ITdQueryResult
     */
    public function query(String $taosql, String $dbName = '') : ITdQueryResult {
        $response = $this->execTaosSql($taosql, $dbName);
        
        return RestfulTDQueryResult::parseResult($response);
    }

    private function execTaosSql(String $taosql, String $dbName = '') : String {
        //当前的连接状态是否是“已连接”？
        if(!$this->connected) {
            throw new PhpTdException(
                ErrorMessage::TD_ENGINE_CONNECTION_CLOSED_ERR_MESSAGE,
                ErrorCode::TD_ENGINE_CONNECTION_CLOSED_ERR
            );
        }

        if (empty($taosql)) {
            throw new PhpTdException(
                ErrorMessage::TD_TAOS_SQL_EMPTY_ERR_MESSAGE,
                ErrorCode::TD_TAOS_SQL_EMPTY_ERR
            );
        }

        //构造url（考虑options 和 defaultDb）
        switch ($this->options['RESULT_TIME_FORMAT']) {
            case TdTimeFormat::LOCAL_TIME :
                $url = sprintf(self::EXEC_SQL_URL_TEMPLATE, $this->host, $this->port);
                break;
            case TdTimeFormat::TIME_STAMP :
                $url = sprintf(self::EXEC_SQLT_URL_TEMPLATE, $this->host, $this->port);
                break;
            case TdTimeFormat::UTC_TIME :
                $url = sprintf(self::EXEC_SQLUTC_URL_TEMPLATE, $this->host, $this->port);
                break;
            default :
                $url = sprintf(self::EXEC_SQL_URL_TEMPLATE, $this->host, $this->port);
        }

        //检查是否需要添加db的选项
        $db = empty($dbName) ? $this->defaultDb : $dbName;
        if(!empty($db)) {
            $url = $url . '/' . $db;
        }

        //通过 tdClient 发送命令到 tdengine 服务端
        try {
            $response = $this->client->send(
                $url,
                HttpClient::METHOD_POST,
                [
                    'Authorization' => sprintf(self::EXEC_HEADER_TEMPLATE, $this->authCode),
                ],
                $taosql, 
                [], null, null, null, 
                [HttpClient::RESULT_OPTION_JSON]
            );
    
        } catch (\Throwable $ex) {
            throw new PhpTdException(
                sprintf(ErrorMessage::NETWORK_CONNECT_ERR_MESSAGE, $ex->getMessage()),
                ErrorCode::NETWORK_CONNECT_ERR
            );
        }

        if (is_null($response)) {
            throw new PhpTdException(
                sprintf(ErrorMessage::NETWORK_CONNECT_ERR_MESSAGE, '结果为空'),
                ErrorCode::NETWORK_CONNECT_ERR
            );
        }

        return json_encode($response);
    }

}