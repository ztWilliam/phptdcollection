<?php
namespace WztzTech\Iot\PhpTd\Collection;

use WztzTech\Iot\PhpTd\Collection\Meta\{CollectionMeta, ColumnMeta};
use WztzTech\Iot\PhpTd\Exception\ErrorCode;
use WztzTech\Iot\PhpTd\Exception\ErrorMessage;
use WztzTech\Iot\PhpTd\Exception\PhpTdException;

class BaseCollectionPoint implements ICollectionPoint {

    protected String $_name;
    protected String $_desc;

    protected String $_key = '';

    protected ICollector $_collector;

    protected ICollectionStore $_store;

    protected array $_tags = [];

    protected array $_privateOptions = [];

    protected array $_data = [];
    protected array $_unsavedData = [];

    protected function __construct(
        String $name,
        String $desc = '', 
        ICollector $bindCollector, 
        ICollectionStore $store = null,
        array $tags = [] 
    )
    {
        $this->_name = $name;
        $this->_desc = $desc;

        $this->_collector = $bindCollector;
        $this->_store = $store;

        $this->_tags = array_merge($tags);
        
    }

    /**
     * 创建一个全新的采集点，
     * 该采集点应为“未注册”状态。
     * 
     * @param String $name 采集点的名称
     * @param String $desc 该采集点的描述信息
     * @param ICollector $bindCollector 要绑定的采集器
     * @param ICollectionStore $store 该采集点的数据应存储于哪个库中
     * @param array $tags 该采集点的 tag 值，以 tagFieldName 为键，以 tagFieldValue 为值，tagFields 应属于要绑定的采集器的tags。
     * 
     * @return ICollectionPoint
     */
    public static function createPoint(
        String $name,
        String $desc = '', 
        ICollector $bindCollector, 
        ICollectionStore $store = null,
        array $tags = [] 
        ) : ICollectionPoint {
        
        return new static(
            $name, $desc, $bindCollector, $store, $tags
        );
        
    }

    /**
     * 注册本采集点。
     * 新注册的采集点将获取一个全局唯一的key值；已注册的采集点将根据当前实例信息对已注册信息进行变更。
     * 
     * 采集点对应的表，也将同时被创建/或更新
     * 
     * @param ICollectionStore $store 采集点归属的数据库，若这里不传，则用实例自身创建时指定的库，若创建时也未指定，则无法注册。
     * 
     * @return ICollectionPoint 返回注册后的实例
     */
    public function register(ICollectionStore $store = null) : ICollectionPoint {

        return $this;
    }

    /**
     * 根据采集点的 key ，创建一个采集点实例。
     * 只有注册过的采集点，才有 key 值。
     * 
     * @param String $key 采集点注册成功后，获取的 key 值
     * 
     * @return ICollectionPoint|null 当key存在时，返回该key对应的采集点实例；当key不存在时，返回null。
     */
    public static function createByKey(String $key) : ICollectionPoint {
        $metaAgent = CollectionMeta::getMetaAgent();

        $pointObj = $metaAgent->pointInfo($key);

        if (is_null($pointObj)) {
            throw new PhpTdException(
                sprintf(ErrorMessage::BASE_OBJECT_NOT_FOUND_ERR_MESSAGE, $key, 'Point'),
                ErrorCode::BASE_OBJECT_NOT_FOUND_ERR
            );
        }

        return $pointObj;
    }

    /**
     * 获得该采集点的名称
     */
    public function getName() : String {
        return $this->_name;
    }

    /**
     * 获得该采集点的描述信息
     */
    public function getDesc() : String {
        return $this->_desc;
    }

    /**
     * 获取采集点的 key
     */
    public function getKey() : String {
        return $this->_key;
    }

    /**
     * 设置采集点的 key
     */
    public function setKey(String $key) {
        $this->_key = $key;
    }

    /**
     * 获取采集点绑定的采集器
     */
    public function getCollector() : ICollector {
        return clone $this->_collector;
    }

    /**
     * 获取采集点归属的数据库
     */
    public function getStore() : ICollectionStore {
        return clone $this->_store;
    }

    /**
     * 获取采集点的隐私选项
     * 
     * @param String $optionKey 隐私选项的 key
     * @return String 隐私选项的值
     */
    public function getPrivateOption(String $optionKey) : String {
        if (array_key_exists($optionKey, $this->_privateOptions)) {
            return $this->_privateOptions[$optionKey];
        } 

        throw new PhpTdException(
            sprintf(ErrorMessage::BASE_OBJECT_NOT_FOUND_ERR_MESSAGE, $optionKey, 'Private Option'),
            ErrorCode::BASE_OBJECT_NOT_FOUND_ERR
        );
    }

    /**
     * 给tags赋值，并返回更新了tags的当前对象实例，以支持链式调用
     * 
     * @param array $tags 以 tagFieldName 为键，以 tagFieldValue 为值，tagFields 应属于要绑定的采集器的tags
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withTags(array $tags) : ICollectionPoint {
        $this->_tags = array_merge($this->_tags, $tags);

        return $this;
    }

    /**
     * 为数据字段赋值，并返回更新了数据的当前对象实例，以支持链式调用
     * 
     * @param array $values 可以放多行数据，以 [[],[],...]的形式，每行数据是以 dataFieldName 为键，以 dataFieldValue 为值的数组。
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withData(array $values) : ICollectionPoint {
        $this->_data = array_merge($this->_data, $values);

        return $this;
    }

    /**
     * 为隐私选项赋值，并返回更新了隐私选项的当前对象实例，以支持链式调用
     * 
     * 所谓“隐私选项”，是指与采集点的安全连接属性相关的设置项，如 ip地址/端口、协议序列号、用户登录安全选项 等，
     * 这些涉及安全的选项通常由应用程序自行管理，不能写死在代码中，也不应存储在数据库中（至少不能明码存储），
     * 所以通常只能在运行时，由更高层的应用程序传入这些选项。
     * 
     * 具体每个采集点的安全选项有哪些，是由采集点所绑定的采集器（即 ICollector 的具体实现类）的采集逻辑决定的。
     * 
     * @param array $options 以隐私选项的key为键，以隐私选项的取值（只能是字符串类型）为值的数组
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withPrivateOptions(array $options) : ICollectionPoint {
        $this->_privateOptions = array_merge($this->_privateOptions, $options);

        return $this;
    }

    // public function gather();

    // public function gatherHistory();

    /**
     * 保存当前尚未保存的数据，一次调用只保存一条（按时间顺序最早的一条）。
     * 
     * @return int 0:无待保存数据；1:保存成功；小于0:保存时发生错误
     */
    public function save() : int {
        //todo 从data数组中，pop出最早一条，保存：


        return 0;
    }

    /**
     * 将当前尚未保存的数据批量保存，按时间顺序（从先到后）。
     * 
     * @return int 0:无待保存数据；>0:保存成功的数据行数；<0:保存时发生错误，没有任何数据被保存。
     */
    public function batchSave() : int {
        //todo 把data数组中的数据批量保存，（需要注意小于tdengine最大语句的size）

        return 0;

    }

    /**
     * 返回当前未保存的数据。
     * 尚未保存的数据。
     * 
     * @return array 多行数据[[],[]...,[]]，其中每行数据是以 dataFieldName 为键，以 dataFieldValue 为值的数组
     */
    public function unsavedData() : array {

        return clone $this->_unsavedData;

    }

}