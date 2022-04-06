<?php
namespace WztzTech\Iot\PhpTd\Util;

use \Yurun\Util\HttpRequest;
use \Yurun\Util\YurunHttp\Http\Response;
use \Yurun\Util\YurunHttp\Http\Request;

class HttpClient 
{
    // const RESULT_OPTION_JSON_OBJECT = "json_as_object";
    // const RESULT_OPTION_JSON_ARRAY = "json_as_array";
    const RESULT_OPTION_JSON = "json_as_object";
    const RESULT_OPTION_XML = "xml";
    const RESULT_OPTION_RAW_RESPONSE = "raw_response";

    //有了with选项，默认直接返回Raw Response对象：
    const RESULT_OPTION_WITH_REQUEST = "with_request";
    const RESULT_OPTION_WITH_TOTALTIME = "with_totaltime";

    const METHOD_GET = "GET";
    const METHOD_POST = "POST";
    const METHOD_PUT = "PUT";
    const METHOD_DEL = "DELETE";
    const METHOD_PATCH = "PATCH";

    /**
     * 默认的超时时间设置
     * 连接超时：3秒
     * 总时间超时：5秒
     */
    protected int $timeout = 5000;
    protected int $connectTimeout = 3000;

    protected Response $response;

    protected Request $request;

    protected $totalTime = 0;

    public function send($url, $method, $headers, $body, $options, 
                        $contentType = null, $timeout = null, $connectTimeout = null, $resultOption = []) {

        $httpRequest = new HttpRequest;

        if(!empty($timeout)) {
            $this->timeout = $timeout;
        }
        if(!empty($connectTimeout)) {
            $this->connectTimeout = $connectTimeout;
        }

        $this->request = $httpRequest->headers($headers)
                                    ->options($options)
                                    ->timeout($this->timeout, $this->connectTimeout)
                                    ->buildRequest($url, $body, empty($method) ? self::METHOD_POST : $method, $contentType);
        

        $beginTime = microtime(true);

        $this->response = $httpRequest->send($url, $body, $method, $contentType);

        $endTime = microtime(true);

        $this->totalTime = $endTime - $beginTime;

        return $this->responseResult($resultOption);

    }

    public function getTotalTime() {
        return $this->totalTime;
    }

    protected function responseResult($resultOption) {
        if(is_null($this->response)) {
            return null;
        }

        if(is_null($resultOption) || empty($resultOption)) {
            return $this->response->body();
        }

        if(in_array(self::RESULT_OPTION_WITH_REQUEST, $resultOption, true)) {
            $this->response->withRequest($this->request);
            
            if(in_array(self::RESULT_OPTION_WITH_TOTALTIME, $resultOption, true)) {
                $this->response->withTotalTime($this->totalTime);
            }

            //带 with 选项的，默认返回原始对象。
            return $this->response;
        }

        if (in_array(self::RESULT_OPTION_JSON, $resultOption, true)) {
            return $this->response->json();
        }

        if (in_array(self::RESULT_OPTION_XML, $resultOption, true)) {
            return $this->response->xml();
        }

        //以上选项都未匹配，则默认返回json结果
        return $this->response->json();
    }

}
