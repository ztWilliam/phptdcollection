<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta;

use WztzTech\Iot\PhpTd\Enum\TdDataType;

/**
 * 
 */
class ColumnMeta {
    public String $name;

    public int $type;

    public int $length;

    /**
     * 构造
     */
    public function __construct(String $name, int $type, int $length) 
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    /**
     * 检查列类型是否合法可用
     * 只检查本实例的属性是否符合 tdengine 的规范，对与其他列相关的检查（如“是否重名”等）不做检查。
     * 
     */
    public function isValid() : bool {
        //名称不能为空

        //列名长度是否超限

        //列名长度 和 类型 是否匹配

        
        return false;
    }

    public static function parseFromJson( String $json ) : ColumnMeta {

        return new ColumnMeta('', 0, 0);
    }

    public function toJson() : String {

        return '';
    }

}