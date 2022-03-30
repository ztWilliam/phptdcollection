<?php
namespace WztzTech\Iot\PhpTd\Connector;

/**
 * 对于查询数据相关的 tdengine 命令，封装其结果的类应该实现的接口。
 * 该接口继承自 ITdResult，除基础接口外，提供了对查询结果数据的访问方法。
 * 
 */
interface ITdQueryResult extends ITdResult {

    /**
     * 获取所有字段名的集合。
     * 字段排列顺序与data数据行中的顺序一致。
     * 
     * @return array
     */
    public function allFields() : array;

    /**
     * 根据 字段名 返回该列的数据类型，
     * 该类型的值必须是 TdDataType 的有效取值。
     * 
     * 若字段名不存在，返回 0 。
     * 
     * @param String $fieldName 字段名
     * 
     * @return int
     */
    public function fieldType(String $fieldName) : int;

    /**
     * 根据 字段名 获取该列的最大长度限定，
     * 列长度 表示该列最多可以保存的内容长度，而不是本次返回值中的数据具体长度。
     * 当列类型是 nchar 时，其长度表示可以保存的 unicode 字符数量（通常一个 unicode 字符占4个字节），而不是字节数。
     * 
     * 若字段名不存在，返回 0 。
     * 
     * @param String $fieldName
     * 
     * @return int
     */
    public function fieldLength(String $fieldName) : int;

    /**
     * 获取结果集中的多行记录，
     * 只包含数据，不包含列名及列头信息。
     * 
     * 可通过指定起始行号以及记录条数，获取结果集中的任意子集。
     * 
     * @param int $fromIndex 默认为0，从第一行开始取。最大不得超过 rowsAffected-1 值。
     * 
     * @param int $count 默认为 0，最大不得超过 rowsAffected - fromIndex
     * 
     * @return array 结果集形如：[[FieldValue1, FieldValue2, ..., FieldValueN],[],...,[]]
     */
    public function fetchDataRows(int $fromIndex = 0, int $count = 0) : array;

    /**
     * 获取结果集中的某行数据，
     * 是 FieldName => FieldValue 键值对的集合。
     * 
     * @param int $rowIndex 从 0 开始的数据行号，最大不得超过 rowsAffected-1 
     * 
     * @return array 结果形如： [FieldName1 => FieldValue1, FieldName2 => FieldValue2, ...]
     */
    public function fetchDataRow(int $rowIndex) : array;

    /**
     * 直接获取结果集中 某行（以行号定位）某列（以列名定位）的数据值。
     * 
     * @param int $rowIndex 从 0 开始的数据行号，最大不得超过 rowsAffected-1 
     * @param String $fieldName 字段名，应在 allFields 中存在
     * 
     * @return 指定行列上的值
     */
    public function getFieldValue(int $rowIndex, String $fieldName);

    /**
     * 获取列的元数据定义
     * 包括 该列的名称、数据类型、最大长度 等
     * 此方法暂不实现，未来如有调用需要，再开放使用
     */
    // public function fieldMetaInfo(String $fieldName) : array;


}