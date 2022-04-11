<?php
namespace WztzTech\Iot\PhpTd\Collection;

/**
 * 采集数据的存储库，由具有相近主题、相互有关联的数据来源的数据组成。
 * 通常对应 tdengine 中的一个 db 。
 * 
 */
interface ICollectionStore {

    public static function register(String $name, int $keepDays, int $updateMode, array $options, String $desc = '') : ICollectionStore;

    public static function newInstance($name) : ICollectionStore;

    public function allCollectors() : array;

    public function allPointsOfCollector(ICollector $collector) : array;
    
}