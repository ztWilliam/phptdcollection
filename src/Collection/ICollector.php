<?php
namespace WztzTech\Iot\PhpTd\Collection;

/**
 * 采集器
 * 用来抓取源头数据，并向“采集库”存储数据的工具
 * 采集器可以视同为一个模板，定义了采集类型、数据格式等
 * “采集器”的采集方式，可以通过传感器、也可以通过软件的数据抽取器、事件监听服务等
 * 一个（或一类）采集器，通常具有相同的采集数据项（即数据结构相同）
 * 
 * 一个“采集器”通常对应 tdengine 中的一个“超级表”
 */
interface ICollector {

    /**
     * 创建一个新的 Collector
     * 其状态为尚未注册的，不能被使用的。
     * 
     * @param String $name 采集器的名称，全系统范围内是不能重名的
     * @param String $desc 该采集器的描述信息
     * @param array $tags 采集器的 tags 定义。以 tagFieldName 为键，以 ColumnMeta 对象为值。
     * @param array $dataFields 采集器的数据字段定义。 以 dataFieldName 为键， 以 ColumnMeta 对象为值
     * 
     * @return ICollector
     */
    public static function createCollector(String $name, String $desc = '', array $tags = null, array $dataFields = null) : ICollector;

    /**
     * 根据采集器的名称，创建一个 ICollector 实例
     * 所提供的名称，必须是一个已经注册的采集器
     * 
     * @param String $name 要创建的采集器，在注册时提供的name
     * @return ICollector | null
     */
    public static function newInstance(String $name) : ICollector;

    /**
     * 将本实例进行注册，或根据当前实例信息更新已注册信息
     * 
     * @return ICollector 变更/注册后的实例
     */
    public function register() : ICollector;
    
    /**
     * 从指定的采集点，抓取最新的数据，并保存到采集点对应的 Store 中。
     * 
     * “最新的数据”，指的是没采集过的数据，具体逻辑由各个具体实现类来定义。
     * 
     * @param ICollectionPoint $point 采集点实例，该采集点必须是与本采集器是绑定的。
     * 
     */
    public function gatherFromPoint(ICollectionPoint $point);

    /**
     * 从指定的采集点，重新采集历史数据，并更新至采集点对应的 Store 中。
     * 
     * @param ICollectionPoint $point 采集点实例，该采集点必须是与本采集器是绑定的。
     * @param String $fromTime 历史数据的起始时间，具体时间格式及精度，由具体实现类来定义
     * @param String $toTime 历史数据的截止时间， 具体时间格式及精度，由具体实现类定义
     * 
     */
    public function gatherHistoryFromPoint(ICollectionPoint $point, String $fromTime, String $toTime);

    /**
     * 接收数据，并将其存入采集点对应的 Store 中。
     * 
     * @param ICollectionPoint $point 采集点实例，该采集点必须是与本采集器是绑定的。
     * @param array $data 接收到的数据，数据格式应符合具体采集器实现类的格式定义
     */
    public function receiveData(ICollectionPoint $point, array $data);

    /**
     * 返回本采集器的所有 Tag 字段定义
     * 
     * @return array 以 tagFieldName 为键，以 ColumnMeta 对象为值
     */
    public function allTags() : array;

    /**
     * 返回本采集器的所有 数据字段 的定义
     * 
     * @return array 以 dataFieldName 为键，以 ColumnMeta 对象为值
     */
    public function allDataFields() : array;


    public function getName() : String;


    public function getDesc() : String;

}