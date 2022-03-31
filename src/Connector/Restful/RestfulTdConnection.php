<?php
namespace WztzTech\Iot\PhpTd\Connector\Restful;

use WztzTech\Iot\PhpTd\Connector\{ITdConnection, ITdResult, ITdQueryResult};

class RestfulTdConnection implements ITdConnection {

        /**
     * 建立一个TdEngine 连接。
     * 
     * @param String $host TDengine 服务端所在IP
     * @param String $port TDengine 开放的连接端口
     * @param String $user 用户名
     * @param String $pass 密码
     * @param String $defaultDb 默认Db Name，可选
     * @param array $options 可通过 Key-Value 数组，对连接选项进行初始化
     * 
     * @return ITdConnection | null 若连接成功，返回一个 ITdConnection对象，若连接失败，则返回null
     */
    public static function connect(String $host, String $port, 
                                    String $user, String $pass, String $defaultDb = '', 
                                    array $options = []) : ITdConnection {
        return new RestfulTdConnection();
    }

    /**
     * 销毁连接相关信息，
     * 断开连接。
     * 
     */
    public function close() : void {

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
        return RestfulTdResult::parseResult('');
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

    }


}