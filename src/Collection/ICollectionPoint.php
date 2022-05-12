<?php
namespace WztzTech\Iot\PhpTd\Collection;

/**
 * 采集点
 * 某个具体的传感器，或某个运行在本地（如：局域网内、边缘网关、服务器端等）的采集程序实例
 * 采集点使用的数据结构，是在其绑定的“采集器”中定义的
 * 采集点可以视为采集器的运行实例
 * 一个“采集点”通常对应 tdengine 中的一个表，该表是基于（其绑定的）采集器所对应的超级表创建的。
 * 
 */
interface ICollectionPoint {

    /**
     * 创建一个全新的采集点，
     * 该采集点应为“未注册”状态。
     * 
     * @param String $name 采集点的名称
     * @param array $tags 该采集点的 tag 值，以 tagFieldName 为键，以 tagFieldValue 为值，tagFields 应属于要绑定的采集器的tags。
     * @param String $desc 该采集点的描述信息
     * @param ICollector $bindCollector 要绑定的采集器
     * @param ICollectionStore $store 该采集点的数据应存储于哪个库中
     * 
     * @return ICollectionPoint
     */
    public static function createPoint(
        String $name,
        array $tags, 
        String $desc = '', 
        ICollector $bindCollector, 
        ICollectionStore $store = null) : ICollectionPoint ;

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
    public function register(ICollectionStore $store = null) : ICollectionPoint;

    /**
     * 根据采集点的 key ，创建一个采集点实例。
     * 只有注册过的采集点，才有 key 值。
     * 
     * @param String $key 采集点注册成功后，获取的 key 值
     * 
     * @return ICollectionPoint|null 当key存在时，返回该key对应的采集点实例；当key不存在时，返回null。
     */
    public static function createByKey(String $key) : ICollectionPoint;

    /**
     * 获取采集点的 key
     */
    public function getKey() : String;

    /**
     * 获取采集点绑定的采集器
     */
    public function getCollector() : ICollector;

    /**
     * 获取采集点归属的数据库
     */
    public function getStore() : ICollectionStore;

    /**
     * 获取采集点的隐私选项
     * 
     * @param String $optionKey 隐私选项的 key
     * @return String 隐私选项的值
     */
    public function getPrivateOption(String $optionKey) : String;

    /**
     * 给tags赋值，并返回更新了tags的当前对象实例，以支持链式调用
     * 
     * @param array $tags 以 tagFieldName 为键，以 tagFieldValue 为值，tagFields 应属于要绑定的采集器的tags
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withTags(array $tags) : ICollectionPoint;

    /**
     * 为数据字段赋值，并返回更新了数据的当前对象实例，以支持链式调用
     * 
     * @param array $values 可以放多行数据，以 [[],[],...]的形式，每行数据是以 dataFieldName 为键，以 dataFieldValue 为值的数组。
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withData(array $values) : ICollectionPoint;

    /**
     * 为隐私选项赋值，并返回更新了隐私选项的当前对象实例，以支持链式调用
     * 
     * 所谓“隐私选项”，是指与采集点的安全连接属性相关的设置项，如 ip地址/端口、协议序列号、用户登录安全选项 等，
     * 这些涉及安全的选项通常由应用程序自行管理，不能写死在代码中，也不应存储在数据库中（至少不能明码存储），
     * 所以通常只能在运行时，由更高层的应用程序传入这些选项。
     * 
     * 具体每个采集点的安全选项有哪些，是由采集点绑定的采集器的采集逻辑决定的。
     * 
     * @param array $options 以隐私选项的key为键，以隐私选项的取值（只能是字符串类型）为值的数组
     * 
     * @return ICollectionPoint 返回当前实例
     */
    public function withPrivateOptions(array $options) : ICollectionPoint;

    // public function gather();

    // public function gatherHistory();

    /**
     * 保存当前尚未保存的数据，一次调用只保存一条（按时间顺序最早的一条）。
     * 
     * @return int 0:无待保存数据；1:保存成功；小于0:保存时发生错误
     */
    public function save() : int;

    /**
     * 将当前尚未保存的数据批量保存，按时间顺序（从先到后）。
     * 
     * @return int 0:无待保存数据；>0:保存成功的数据行数；<0:保存时发生错误，没有任何数据被保存。
     */
    public function batchSave() : int;

    /**
     * 返回当前未保存的数据。
     * 尚未保存的数据。
     * 
     * @return array 多行数据[[],[]...,[]]，其中每行数据是以 dataFieldName 为键，以 dataFieldValue 为值的数组
     */
    public function unsavedData() : array;

}