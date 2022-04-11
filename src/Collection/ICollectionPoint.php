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

    public static function registerPoint(ICollectionStore $store, ICollector $bindCollector, array $tags) : ICollectionPoint ;

    public static function createByKey(String $key) : ICollectionPoint;

    public function withTags(array $tags) : ICollectionPoint;

    public function withData(array $values) : ICollectionPoint;

    public function withSecurityInfo(array $securities) : ICollectionPoint;

    public function save();

    public function batchSave(array $tags, array $rows);

}