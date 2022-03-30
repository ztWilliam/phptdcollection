<?php

namespace WztzTech\Iot\PhpTd\Connector;

/**
 * 调用 tdengine 服务端执行命令后，返回的结果的接口定义。
 * 所有的 tdengine 调用，都必须返回一个 ITdResult （或其派生接口）类型的对象，不能有任何 Exception 抛出。
 * 
 */
interface ITdResult{

    /**
     * 解析 tdengine 服务端执行命令后返回的结果，并转化为 ITdResult 类型的对象。
     * 
     * @param String $result 
     * 
     * @return ITdResult 任何实现 ITdResult 或其派生接口的对象实例，不允许返回null
     */
    public static function parseResult(String $result) : ITdResult;

    /**
     * 本结果是否执行失败。
     * 
     * @return bool 若为 True，表示本次的执行结果有错误返回，若为 False，表示本次执行无错误。
     */
    public function hasError() : bool;

    /**
     * Status 只有是 'succ' 时，表示执行成功，其他值都表示执行失败。
     * 当 tdengine 正常返回 status 时，该值即为 tdengine 返回的 status；
     * 当 tdengine 无法正常返回 status 时（例如网络错误或其他未知错误等），该值统一为 'failed' 。
     * 
     * 
     * @return String
     */
    public function getStatus() : String;

    /**
     * 获取错误码，通过错误码可以获取真实错误信息，便于追踪调试。
     * 
     * 当错误码为 0 时，表示无错误；
     * 当错误码 > 0 时，表示该错误码是 tdengine 返回的错误码
     * 当错误码 < 0 时，表示该错误码是本组件内产生的错误
     * 
     * @return int
     */
    public function getErrorCode() : int;

    /**
     * 获取返回结果的描述信息，
     * 当有错误发生时，描述信息中可能含有错误的描述；
     * 当没有错误发生时，可能有些有价值的 tdengine 返回的结果，也会出现在 desc 中（所以别忽视desc）。
     * 
     * @return String
     */
    public function getDesc() : String;

    /**
     * 获取命令执行后影响了多少行数据。
     * 通常用于写入数据的命令执行后的验证，默认值为 0.
     * 
     * @return int
     */
    public function rowsAffected() : int;

    /**
     * 获得调用 tdengine 服务端，执行命令后返回的原始结果。
     * 便于调用者从原始结果中获取更丰富的信息
     * 
     * @return String 原始结果，原则上应与 parseResult 时传入的 String 相同。
     */
    public function rawResult() : String;

}