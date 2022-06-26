<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta;

use WztzTech\Iot\PhpTd\Connector\{TdConnectionManager, ITdQueryResult, ITdResult};
use WztzTech\Iot\PhpTd\Collection\{ICollectionPoint, ICollectionStore, ICollector};
use WztzTech\Iot\PhpTd\Enum\TdUpdateMode;
use WztzTech\Iot\PhpTd\Exception\ErrorCode;
use WztzTech\Iot\PhpTd\Exception\ErrorMessage;
use WztzTech\Iot\PhpTd\Exception\PhpTdException;
use WztzTech\Iot\PhpTd\Exception\TdException;
use WztzTech\Iot\PhpTd\Util\{HttpClient, TimeUtil};

/**
 * 所有Store、Collector、Points的信息，注册后的元数据均由 CollectionMeta 负责管理
 * 
 * 负责创建 SYS_Meta 数据库，并保存Collection相关的注册信息
 * 
 * 接收所有的采集器、采集点的注册请求
 * 
 * 提供已注册的 Store、采集器、采集点的一览，及其关联信息
 * 
 * 
 */
class CollectionMeta {

    const META_DB_NAME = 'sys_meta';

    const META_SYS_STORE_TABLE_NAME = 'sys_store';

    const META_SYS_COLLECTOR_TABLE_NAME = 'sys_collector';

    const META_SYS_POINTS_TABLE_NAME = 'sys_points_in_store';

    const META_DB_KEEP = 7200;
    
    const META_DB_DAYS = 30;

    const PREFIX_STORE_TABLE = "store_";
    const PREFIX_COLLECTOR_TABLE = "collector_";
    const PREFIX_POINT_TABLE = "point_";

    const CLASS_TYPE_SEPARATOR = "\\";

    const CLASS_TYPE_SEPARATOR_REPLACE = "||";

    private HttpClient $_client;

    private TdConnectionManager $tdManager;

    private function __construct(HttpClient &$client = null) 
    {
        $this->_client = is_null($client) ? new HttpClient() : $client;

        $this->tdManager = new TdConnectionManager();
    }

    /**
     * 获取一个 CollectionMeta 的实例
     * 
     * @param HttpClient $client  
     */
    public static function getMetaAgent(HttpClient &$client = null): CollectionMeta {
        return new static($client);
    }
    
    /**
     * 检查有没有 sys_meta 库，若没有，则执行建库及建表语句，
     * 创建初始的元数据库。
     * 
     * @param bool $reset 是否重置成初始状态
     * 
     * @return int 0:正常初始化成功；1:Meta数据库已存在（无需重置）；-1:初始化Meta数据库失败；-2:初始化系统表失败。
     */
    public function init(bool $reset = false) : int {
        //检查是否存在 sys_meta 库
        // 用 use sys_meta 判断是否存在该库，有 error 说明该库不存在
        if ($this->exists()) {
            // 如果已存在，并且 $reset 是 true ，则 drop 已存在的 sys_meta 库， 否则直接返回
            if ($reset) {
                $this->clear();
            } else {
                return 1;
            }
        }

        //创建 sys_meta 库
        if($this->initMetaDb()) {
            //创建基础超级表
            if(!$this->initSuperTables()) {
                return -2;
            } 
            
        } else {
            return -1;
        }

        return 0;
    }

    /**
     * 用 sys_store 超级表创建 具体 store 实例对应的表。
     * 表名即为 store 的名称
     */
    public function registerStore( ICollectionStore $store ) : int {
        //利用 $store 的 名字 作为表名、classtype、desc 作为tags，创建 sys_store 的子表：
        $tableName = self::PREFIX_STORE_TABLE . $store->getName();
        $classType = str_replace(self::CLASS_TYPE_SEPARATOR, self::CLASS_TYPE_SEPARATOR_REPLACE, get_class($store));
        $desc = $store->getDesc();

        if (empty($tableName)) {
            throw new PhpTdException(
                sprintf(ErrorMessage::PARAM_OR_FIELD_EMPTY_ERR_MESSAGE, 'Store Name'),
                ErrorCode::PARAM_OR_FIELD_EMPTY_ERR
            );
        }

        //检查名字是否用过：
        if ($this->tableExists($tableName)) {
            throw new PhpTdException(
                sprintf(ErrorMessage::NAME_EXISTS_ERR_MESSAGE, $tableName),
                ErrorCode::NAME_EXISTS_ERR
            );
        }

        //创建新 store 表：
        try {
            $this->createStoreTable($tableName, $classType, $desc);

        } catch (\Throwable $ex) {
            throw new PhpTdException(
                sprintf(ErrorMessage::META_REGISTER_FAILED_ERR_MESSAGE, 'Store', $ex->getMessage()),
                ErrorCode::META_REGISTER_FAILED_ERR
            );
        }

        // 创建新采集库对应的 db
        try {
            //调用传入的store自己的初始化方法，创建其对应的DB
            $store->initDB();
        } catch (\Throwable $ex) {
            //因为创建 db 失败，所以要把此前创建成功的注册表也删除：
            $this->deleteTable($tableName);

            throw new PhpTdException(
                sprintf(ErrorMessage::META_REGISTER_FAILED_ERR_MESSAGE, 'Store', $ex->getMessage()),
                ErrorCode::META_REGISTER_FAILED_ERR
            );
        }

        return 0;
    }

    /**
     * 
     */
    public function registerCollector( ICollector $collector ) {

    }

    public function registerPoint( ICollectionPoint $point ) {

    }

    public function allStores() {

    }

    public function collectorInfo(String $collectorName) {

    }

    public function pointInfo(String $pointKey) {

    }


    private function exists() : bool {
        $tdSql = sprintf("use %s", self::META_DB_NAME);

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            return false;
        } else {
            return true;
        }

    }

    private function clear() : bool {
        $tdSql = sprintf("DROP DATABASE IF EXISTS %s ", self::META_DB_NAME);

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            echo $result->getDesc();
            return false;
        }

        return true;
    }

    private function initMetaDb() {
        $tdSql = sprintf("CREATE DATABASE %s KEEP %d DAYS %d UPDATE %d", 
            self::META_DB_NAME, 
            self::META_DB_KEEP, 
            self::META_DB_DAYS, 
            TdUpdateMode::UPDATE_PART );

        $conn = $this->tdManager->getConnection([], $this->_client);

        $result = $conn->exec($tdSql);

        if ($result->hasError()) {
            echo $result->getDesc();
            return false;
        }

        return true;
    }

    /**
     * sys_store 用于存放所有 store 信息的超级表，每个 store 对应一个子表
     * tags: store_name class_type  desc 
     * dataFields: counting_time   point_count  collector_count  data_count   data_size
     * 
     * sys_collector 用于存放所有 采集器 信息的超级表， 每个 collector 对应一个子表
     * tags: collector_name  class_type  desc
     * dataFields: counting_time   store_count   point_count  running_count   recently_running_time    
     * 
     * sys_points_in_store 用于存放所有 采集点 信息的超级表， 每个 采集点 对应一个子表
     * tags: point_name  store  collector  desc    class_type   point_key
     * dataFields: counting_time   data_count   data_size   recently_data_time
     * 
     */
    private function initSuperTables()
    {
        // 创建表的语句：

        $conn = $this->tdManager->getConnection([], $this->_client);

        $tdSql1 = sprintf("CREATE STABLE %s (`counting_time` TIMESTAMP, `point_count` INT, `collector_count` INT, `data_count` BIGINT, `data_size` BIGINT) TAGS (`store_name` BINARY(128), `class_type` BINARY(200), `desc` NCHAR(200) );", self::META_SYS_STORE_TABLE_NAME);
        $result = $conn->withDefaultDb(self::META_DB_NAME)
                        ->exec($tdSql1);

        if ($result->hasError()) {
            echo $result->getDesc();
            return false;
        }

        $tdSql2 = sprintf("CREATE STABLE %s (counting_time TIMESTAMP, store_count INT, point_count INT, running_count BIGINT, recently_running_time TIMESTAMP) TAGS (`collector_name` BINARY(128),  `class_type` BINARY(200), `desc` NCHAR(200) );" , self::META_SYS_COLLECTOR_TABLE_NAME);
        $result = $conn->exec($tdSql2);

        if ($result->hasError()) {
            echo $result->getDesc();
            return false;
        }

        $tdSql3 = sprintf("CREATE STABLE %s (counting_time TIMESTAMP, data_count BIGINT, data_size BIGINT, recently_data_time TIMESTAMP) TAGS (`point_name` BINARY(128), `store` BINARY(128), `collector` BINARY(128), `point_key` BINARY(128), `class_type` BINARY(200), `desc` NCHAR(200) );", self::META_SYS_POINTS_TABLE_NAME);
        $result = $conn->exec($tdSql3);

        if ($result->hasError()) {
            echo $result->getDesc();
            return false;
        }
        
        return true;
    }

    /**
     * 
     */
    private function tableExists($tableName) : bool {
        $conn = $this->tdManager->getConnection([], $this->_client);

        $tdSql = sprintf(" SHOW TABLES LIKE '%s'; ", $tableName);
        $result = $conn->withDefaultDb(self::META_DB_NAME)
                        ->query($tdSql);

        if ($result->hasError()) {
            throw new TdException(
                sprintf(ErrorMessage::TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE, $result->getDesc()),
                ErrorCode::TD_TAOS_SQL_EXECUTE_FAILED_ERR
            );
        }

        if ($result->rowsAffected() == 0) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * sys_store 用于存放所有 store 信息的超级表，每个 store 对应一个子表
     * tags: store_name class_type  desc 
     * dataFields: counting_time   point_count  collector_count  data_count   data_size
     * 
     */
    private function createStoreTable(String $tableName, String $classType, String $desc) {
        $conn = $this->tdManager->getConnection([], $this->_client);

        $tdSql = sprintf(
            "INSERT INTO `%s` USING `%s` 
            (`store_name`, `class_type`, `desc`) TAGS ('%s', '%s', '%s') 
            (counting_time, point_count, collector_count, data_count) VALUES (%s, %d, %d, %d)", 
            $tableName,
            self::META_SYS_STORE_TABLE_NAME,
            $tableName, $classType, $desc,
            TimeUtil::getMiliSeconds(),
            0, 0, 0
        );

        $result = $conn->withDefaultDb(self::META_DB_NAME)
                        ->exec($tdSql);

        if ($result->hasError()) {
            throw new TdException(
                sprintf(ErrorMessage::TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE, $result->getDesc()),
                ErrorCode::TD_TAOS_SQL_EXECUTE_FAILED_ERR
            );
        }

    }

    private function deleteTable(String $tableName) {
        $conn = $this->tdManager->getConnection([], $this->_client);

        $tdSql = sprintf(" DROP TABLE IF EXISTS `%s`; ", $tableName );
        $result = $conn->withDefaultDb(self::META_DB_NAME)
                        ->exec($tdSql);

        if ($result->hasError()) {
            throw new TdException(
                sprintf(ErrorMessage::TD_TAOS_SQL_EXECUTE_FAILED_ERR_MESSAGE, $result->getDesc()),
                ErrorCode::TD_TAOS_SQL_EXECUTE_FAILED_ERR
            );
        }

    }

}