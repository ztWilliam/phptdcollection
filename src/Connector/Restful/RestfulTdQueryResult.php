<?php 
namespace WztzTech\Iot\PhpTd\Connector\Restful;

use WztzTech\Iot\PhpTd\Connector\{ITdResult, ITdQueryResult};

class RestfulTDQueryResult implements ITdQueryResult {
    /**
     * 解析 tdengine 服务端执行命令后返回的结果，并转化为 ITdResult 类型的对象。
     * 
     * @param String $result 
     * 
     * @return ITdResult 任何实现 ITdResult 或其派生接口的对象实例，不允许返回null
     */
    public static function parseResult(String $result) : ITdResult {
        return new RestfulTdQueryResult();
    }

    /**
     * 本结果是否执行失败。
     * 
     * @return bool 若为 True，表示本次的执行结果有错误返回，若为 False，表示本次执行无错误。
     */
    public function hasError() : bool {
        return false;
    }

    /**
     * Status 只有是 'succ' 时，表示执行成功，其他值都表示执行失败。
     * 当 tdengine 正常返回 status 时，该值即为 tdengine 返回的 status；
     * 当 tdengine 无法正常返回 status 时（例如网络错误或其他未知错误等），该值统一为 'failed' 。
     * 
     * 
     * @return String
     */
    public function getStatus() : String {
        return 'succ';
    }

    /**
     * 获取错误码，通过错误码可以获取真实错误信息，便于追踪调试。
     * 
     * 当错误码为 0 时，表示无错误；
     * 当错误码 > 0 时，表示该错误码是 tdengine 返回的错误码
     * 当错误码 < 0 时，表示该错误码是本组件内产生的错误
     * 
     * @return int
     */
    public function getErrorCode() : int {
        return 0;
    }

    /**
     * 获取返回结果的描述信息，
     * 当有错误发生时，描述信息中可能含有错误的描述；
     * 当没有错误发生时，可能有些有价值的 tdengine 返回的结果，也会出现在 desc 中（所以别忽视desc）。
     * 
     * @return String
     */
    public function getDesc() : String {
        return '';
    }

    /**
     * 获取命令执行后影响了多少行数据。
     * 通常用于写入数据的命令执行后的验证，默认值为 0.
     * 
     * @return int
     */
    public function rowsAffected() : int {
        return 0;
    }

    /**
     * 获得调用 tdengine 服务端，执行命令后返回的原始结果。
     * 便于调用者从原始结果中获取更丰富的信息
     * 
     * @return String 原始结果，原则上应与 parseResult 时传入的 String 相同。
     */
    public function rawResult() : String {
        return '';
    }

    /**
     * 获取所有字段名的集合。
     * 字段排列顺序与data数据行中的顺序一致。
     * 
     * @return array
     */
    public function allFields() : array {
        return [];
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
        return 0;
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
        return 0;
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
        return [
            [], []
        ];
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
        return [
            'field1' => 0,
        ];
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
        return null;
    }


}