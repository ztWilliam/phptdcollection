<?php 
namespace WztzTech\Iot\PhpTd\Connector\Restful;

use WztzTech\Iot\PhpTd\Connector\{ITdResult, ITdQueryResult};

use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage, PhpTdException};

class RestfulTDQueryResult extends RestfulTdResult implements ITdQueryResult {

    /**
     * 结果集中，所有列名的数组，顺序与原结果一致
     */
    private $fieldNames = [];

    /**
     * 结果集中，各列的属性
     * 以 FieldName => [FieldMeta] 为键值对 的数组
     * 其中，FieldMeta 至少包含 type 和 length 两个键 
     */
    private $fieldInfos = [];

    /**
     * 结果集中的所有数据行，只包含数据，不包含列名
     * 每一行的数据顺序，与 $fieldNames 中的列名顺序一致。
     */
    private $allData = [];

    private function fillFieldInfo($column_meta) {
        for ($i = 0; $i < count($column_meta); $i ++) {
            $column_name = $column_meta[$i][0];
            $this->fieldNames[$i] = $column_name;

            $this->fieldInfos[$column_name] = [
                'name' => $column_name,
                'type' => $column_meta[$i][1],
                'length' => $column_meta[$i][2],
            ];
        }
    }

    private function fillData($data) {
        $this->allData = $data;
    }

    protected function __construct(object $jsonResult) 
    {
        parent::__construct($jsonResult);

        if (\property_exists($jsonResult, 'column_meta')) {
            $this->fillFieldInfo($jsonResult->column_meta);
        }

        if (\property_exists($jsonResult, 'data')) {
            $this->fillData($jsonResult->data);
        }
    }

    /**
     * 解析 tdengine 服务端执行命令后返回的结果，并转化为 ITdResult 类型的对象。
     * 
     * @param String $result 
     * 
     * @return ITdResult 任何实现 ITdResult 或其派生接口的对象实例，不允许返回null
     */
    public static function parseResult(String $result) : ITdResult {
        if (is_null($result)) {
            throw new PhpTdException(
                ErrorMessage::TD_TAOS_SQL_RESULT_NULL_ERR_MESSAGE,
                ErrorCode::TD_TAOS_SQL_RESULT_NULL_ERR
            );
        }

        return new static(json_decode($result, false));
    }

    /**
     * 获取所有字段名的集合。
     * 字段排列顺序与data数据行中的顺序一致。
     * 
     * @return array
     */
    public function allFields() : array {
        return clone $this->fieldNames;
    }

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
    public function fieldType(String $fieldName) : int {
        return $this->fieldInfos[$fieldName]['type'];
    }

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
    public function fieldLength(String $fieldName) : int {
        return $this->fieldInfos[$fieldName]['length'];
    }

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
    public function fetchDataRows(int $fromIndex = 0, int $count = 0) : array {
        return array_slice($this->allData, $fromIndex, $count, false);
    }

    /**
     * 获取结果集中的某行数据，
     * 是 FieldName => FieldValue 键值对的集合。
     * 
     * @param int $rowIndex 从 0 开始的数据行号，最大不得超过 rowsAffected-1 
     * 
     * @return array 结果形如： [FieldName1 => FieldValue1, FieldName2 => FieldValue2, ...]
     */
    public function fetchDataRow(int $rowIndex) : array {
        $dataRow = array_slice($this->allData, $rowIndex, 1);

        $result = [];
        for ($i = 0; $i < count($this->fieldNames); $i ++) {
            $result[$this->fieldNames[$i]] = $dataRow[0][$i];
        }

        return $result;
    }

    /**
     * 直接获取结果集中 某行（以行号定位）某列（以列名定位）的数据值。
     * 
     * @param int $rowIndex 从 0 开始的数据行号，最大不得超过 rowsAffected-1 
     * @param String $fieldName 字段名，应在 allFields 中存在
     * 
     * @return 指定行列上的值
     */
    public function getFieldValue(int $rowIndex, String $fieldName) {
        $theRow = $this->fetchDataRow($rowIndex);

        return $theRow[$fieldName];
    }


}