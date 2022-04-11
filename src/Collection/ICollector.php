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
    
    public function gatherFromPoint(ICollectionPoint $point);

    public function receiveData(object $data);

    public function allTags() : array;

    public function allDataFields() : array;

}