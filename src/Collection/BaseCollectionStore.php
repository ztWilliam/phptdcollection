<?php
namespace WztzTech\Iot\PhpTd\Collection;

use WztzTech\Iot\PhpTd\Util\HttpClient;
use WztzTech\Iot\PhpTd\Connector\TdConnectionManager;

use WztzTech\Iot\PhpTd\Enum\TdUpdateMode;
use WztzTech\Iot\PhpTd\Exception\{ErrorMessage, ErrorCode};
use WztzTech\Iot\PhpTd\Exception\TdException;

class BaseCollectionStore implements ICollectionStore {
    const DEFAULT_DAYS = 30;

    private String $name;
    private String $desc;

    private int $keepDays;
    private int $updateMode;
    private array $options = [];

    protected HttpClient $_client;

    protected TdConnectionManager $tdManager;

    protected function __construct(String $name, String $desc = '', int $keepDays, int $updateMode, array $options) {
        $this->name = $name ;
        $this->desc = $desc;

        $this->keepDays = $keepDays;
        $this->updateMode = $updateMode;
        $this->options = $options;

        $this->_client = new HttpClient();

        $this->tdManager = new TdConnectionManager();

    }

    protected function dbExists(): bool {
        $tdSql = sprintf("use %s", $this->getName());

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            return false;
        } else {
            return true;
        }

    }

    protected function clearDB() {
        $tdSql = sprintf("DROP DATABASE IF EXISTS %s ", $this->getName());

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            throw new TdException(
                sprintf(ErrorMessage::TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE, $result->getDesc()),
                ErrorCode::TD_TAOS_SQL_EXECUTE_FAILED_ERR
            );
        }
    }

    protected function createDB() {
        $days = array_key_exists('days', $this->options) ? $this->options['days'] : self::DEFAULT_DAYS;

        $tdSql = sprintf("CREATE DATABASE %s KEEP %d DAYS %d UPDATE %d", 
            $this->getName(), 
            $this->keepDays, 
            $days, 
            TdUpdateMode::UPDATE_PART );

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            throw new TdException(
                sprintf(ErrorMessage::TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE, $result->getDesc()),
                ErrorCode::TD_TAOS_SQL_EXECUTE_FAILED_ERR
            );
        }
    }

    /**
     * 运行时改变后续操作所用的client对象，
     * 支持链式调用。
     * 
     * @param HttpClient $client 执行命令的 HttpClient 对象，若为null，不会影响本实例已有的client对象。
     * 
     * @return BaseCollectionStore
     */
    public function withClient(HttpClient $client) {
        if ( !is_null($client) ) {
            $this->_client = $client;
        }

        return $this;
    }

    /////////////////////////////
    ///
    /// 以下内容是实现接口规定的方法：
    ///
    /////////////////////////////

    /**
     * 根据给定的信息，创建一个时序数据库，并将该信息存在meta信息库中。
     * 
     * @param String $name 要创建的db的名字，若为空，默认以具体实现类的类名作为db名。
     * @param String $desc 该数据库的描述信息
     * @param int $keepDays 数据存储的最长时间，默认1095天（三年）
     * @param int $updateMode 数据修改模式，选项为 TdUpdateMode 枚举值， 默认为“可部分修改数据”。因为该选项在 db 创建之后无法修改，请务必谨慎选择。
     * @param array $options 数据库创建时的其他选项，可参考 tdengine 有关数据存储相关的服务端配置选项，进行设置。
     * 
     * @return ICollectionStore 在数据库创建完毕后，返回相应的实例。
     */
    public static function createStore(
        String $name = '', 
        String $desc = '', 
        int $keepDays = 1095, 
        int $updateMode = TdUpdateMode::UPDATE_PART, 
        array $options = []) : ICollectionStore 
    {
        return new static($name, $desc, $keepDays, $updateMode, $options);
    }

    /**
     * 初始化自身所对应的DB
     * 
     * @param bool $reset 是否需要重置，默认为 false，若为 true，则即使DB已经存在，也会重置为空DB状态，会丢掉所有数据，请谨慎使用。
     * 
     */
    public function initDB(bool $reset = false) : void{
        //检查是否存在 sys_meta 库
        // 用 use sys_meta 判断是否存在该库，有 error 说明该库不存在
        if ($this->dbExists()) {
            // 如果已存在，并且 $reset 是 true ，则 drop 已存在的 sys_meta 库， 否则直接返回
            if ($reset) {
                $this->clearDB();
            } 
        }

        //创建 sys_meta 库
        $this->createDB();
    }

    /**
     * 根据数据库的名字，创建合适类型（与register时的类型相同）的 ICollectionStore 实例。
     * 只有已经注册的库，才能通过 name 来创建实例。
     * 
     * @param String $name 已注册的 db 名字，若为空，则按当前实现类的类名检索。
     * 
     * @return ICollectionStore|null 已注册的db对应的 ICollectionStore 对象，对象的类型与注册时的具体实现类的类型一致。
     */
    public static function newInstance(String $name = '') : ICollectionStore 
    {
        return new static($name, '', 0, 0, []);
    }

    // public function alterDb($options = []);

    public function getName() : String {
        return $this->name;
    }

    public function getDesc() : String {
        return $this->desc;
    }

    /**
     * 向数据库中添加采集点
     * 按照所给 points 的定义，创建对应的数据表
     * 
     * @param array $points 其中每个元素，都为 ICollectionPoint 实例，其类型应当相同。
     * @param ICollector $collector 若不为空，则创建该 collector 对应的超级表，points array 中实例所绑定的 collector 将被忽略； 若为空，则根据 points 中绑定的collector 创建超级表。
     * 
     * @return void
     */
    public function addPoints(array $points, ICollector $collector = null) : void {

    }


}