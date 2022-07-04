<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta\Parser;

use WztzTech\Iot\PhpTd\Connector\ITdQueryResult;
use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage, PhpTdException};

class CollectorParser {

    /**
     * 将结果集中的数据，转化成对应的 ICollector 对象数组
     * 
     * @param ITdQueryResult $queryResult 从 Meta 数据库中查询出的 Collector 基本信息结果集
     * 
     * @return array 相应 class_type 的实例对象数组
     * 
     */
    public static function parseCollectors( ITdQueryResult $queryResult) : array {
        $rows = $queryResult->rowsAffected();
        $collectors = [];

        for ($i = 0; $i < $rows; $i++) {
            $rowData = $queryResult->fetchDataRow($i);

            $collectorName = $rowData['collector_name'];
            $classType = $rowData['class_type'];
            $collectorDesc = $rowData['desc'];

            $classType = str_replace(ParserConstant::CLASS_TYPE_SEPARATOR_REPLACE, ParserConstant::CLASS_TYPE_SEPARATOR, $classType);

            $collectorClass = new \ReflectionClass($classType);

            if ($collectorClass->hasMethod(ParserConstant::COLLECTOR_CREATE_METHOD_NAME)) {

                $createMethod = $collectorClass->getMethod(ParserConstant::COLLECTOR_CREATE_METHOD_NAME);
    
                $collectorObj = $createMethod->invoke( null, //因为 createCollector 是静态方法，所以实例参数传 null 即可
                    $collectorName, $collectorDesc      //只传注册时保存的属性，其他参数按默认值，由具体类的定义来做。
                );
    
            } else {
                //“没有 创建 Collector 的 方法”，说明 ClassType 所指定的类，并未实现 ICollector 接口，需要报错：
                throw new PhpTdException(
                    sprintf(ErrorMessage::REFLECTION_ERR_INVALID_INTERFACE_MESSAGE, $classType, 'ICollector') , 
                    ErrorCode::REFLECTION_ERR_INVALID_INTERFACE);
            }

            $collectors[] = $collectorObj;

        }

        return $collectors;
    }

}