<?php
namespace WztzTech\Iot\PhpTd\Collection;

use WztzTech\Iot\PhpTd\Enum\TdUpdateMode;

class BaseCollectionStore implements ICollectionStore {
    private String $name;
    private String $desc;

    protected function __construct(String $name, int $keepDays, int $updateMode, array $options, String $desc = '') {
        $this->name = $name . static::class;
        $this->desc = $desc;
    }

    /**
     * 根据给定的信息，创建一个时序数据库，并将该信息存在meta信息库中。
     * 
     * @param String $name 要创建的db的名字，若为空，默认以具体实现类的类名作为db名。
     * @param int $keepDays 数据存储的最长时间，默认1095天（三年）
     * @param int $updateMode 数据修改模式，选项为 TdUpdateMode 枚举值， 默认为“可部分修改数据”。因为该选项在 db 创建之后无法修改，请务必谨慎选择。
     * @param array $options 数据库创建时的其他选项，可参考 tdengine 有关数据存储相关的服务端配置选项，进行设置。
     * @param String $desc 该数据库的描述信息
     * 
     * @return ICollectionStore 在数据库创建完毕后，返回相应的实例。
     */
    public static function register(
        String $name = '', 
        int $keepDays = 1095, 
        int $updateMode = TdUpdateMode::UPDATE_PART, 
        array $options = [], 
        String $desc = '') : ICollectionStore 
    {

        return new static($name, $keepDays, $updateMode, $options, $desc);
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
        return new static($name, 0, 0, [], '');
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