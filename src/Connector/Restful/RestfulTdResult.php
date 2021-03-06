<?php
namespace WztzTech\Iot\PhpTd\Connector\Restful;

use PHPUnit\Util\Json;
use WztzTech\Iot\PhpTd\Connector\{ITdResult};
use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage};
use WztzTech\Iot\PhpTd\Exception\PhpTdException;

class RestfulTdResult implements ITdResult {

    const TD_SUCC_TAG = "succ";

    protected $lastIsError = false;
    protected $lastStatus = '';
    protected $lastErrorCode = 0;
    protected $lastDesc = '';
    protected $lastRows = 0;

    protected $lastRawResult = '';

    protected function __construct(object $jsonResult) {
        $this->lastStatus = $jsonResult->status;

        $this->lastIsError = ($jsonResult->status == self::TD_SUCC_TAG) ? false : true ;

        $this->lastErrorCode = \property_exists($jsonResult, 'code') ? $jsonResult->code : 0;
        
        $this->lastDesc = \property_exists($jsonResult, 'desc') ? $jsonResult->desc : '' ;

        $this->lastRows = \property_exists($jsonResult, 'rows') ? $jsonResult->rows : 0 ;

        $this->lastRawResult = json_encode($jsonResult);
    }

    /**
     * 解析 tdengine 服务端执行命令后返回的结果，并转化为 ITdResult 类型的对象。
     * 
     * @param String $result 必须是有效的json字符串
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

        $jsonResult = json_decode($result, false);

        return new static($jsonResult);
    }

    /**
     * 本结果是否执行失败。
     * 
     * @return bool 若为 True，表示本次的执行结果有错误返回，若为 False，表示本次执行无错误。
     */
    public function hasError() : bool {
        return $this->lastIsError;
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
        return $this->lastStatus;
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
        return $this->lastErrorCode;
    }

    /**
     * 获取返回结果的描述信息，
     * 当有错误发生时，描述信息中可能含有错误的描述；
     * 当没有错误发生时，可能有些有价值的 tdengine 返回的结果，也会出现在 desc 中（所以别忽视desc）。
     * 
     * @return String
     */
    public function getDesc() : String {
        return $this->lastDesc;
    }

    /**
     * 获取命令执行后影响了多少行数据。
     * 通常用于写入数据的命令执行后的验证，默认值为 0.
     * 
     * @return int
     */
    public function rowsAffected() : int {
        return $this->lastRows;
    }

    /**
     * 获得调用 tdengine 服务端，执行命令后返回的原始结果。
     * 便于调用者从原始结果中获取更丰富的信息
     * 
     * @return String 原始结果，原则上应与 parseResult 时传入的 String 相同。
     */
    public function rawResult() : String {
        return $this->lastRawResult;
    }


}