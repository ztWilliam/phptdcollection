<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta\Parser;

use WztzTech\Iot\PhpTd\Collection\Meta\CollectionMeta;
use WztzTech\Iot\PhpTd\Connector\ITdQueryResult;

use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage, PhpTdException};

class PointParser {

    /**
     * 将结果集中的数据，转化成对应的 ICollectionPoint 对象数组
     * 
     * @param ITdQueryResult $queryResult 从 Meta 数据库中查询出的 Point 基本信息结果集
     * @param CollectionMeta $metaAgent 用于从meta库中取数据的工具
     * 
     * @return array 相应 class_type 的实例对象数组, 对象中会包括相应关联的 ICollectionStore 以及 ICollector 对象
     * 
     */
    public static function parsePoints( ITdQueryResult $queryResult, CollectionMeta $metaAgent ) : array {
        $rows = $queryResult->rowsAffected();

        $stores = [];
        $collectors = [];

        $points = [];

        for ($i = 0; $i < $rows; $i++) {
            $rowData = $queryResult->fetchDataRow($i);

            $pointName = $rowData['point_name'];
            $collectorName = $rowData['collector'];

            if (!array_key_exists($collectorName, $collectors)) {
                $collectors[$collectorName] = $metaAgent->collectorInfo($collectorName);
            }
            $collectorObj = $collectors[$collectorName];

            $storeName = $rowData['store'];
            if (!array_key_exists($storeName, $stores)) {
                $stores[$storeName] = $metaAgent->storeInfo($storeName);
            }
            $storeObj = $stores[$storeName];

            $classType = $rowData['class_type'];
            $pointDesc = $rowData['desc'];
            $pointKey = $rowData['point_key'];

            $classType = str_replace(ParserConstant::CLASS_TYPE_SEPARATOR_REPLACE, ParserConstant::CLASS_TYPE_SEPARATOR, $classType);

            $pointClass = new \ReflectionClass($classType);

            if ($pointClass->hasMethod(ParserConstant::POINT_CREATE_METHOD_NAME)) {

                $createMethod = $pointClass->getMethod(ParserConstant::POINT_CREATE_METHOD_NAME);
    
                $pointObj = $createMethod->invoke( null, //因为 createCollector 是静态方法，所以实例参数传 null 即可
                    $pointName, $pointDesc, $collectorObj, $storeObj      //只传注册时保存的属性，其他参数按默认值，由具体类的定义来做。
                );

                $pointObj->setKey($pointKey);
    
            } else {
                //“没有 创建 Collector 的 方法”，说明 ClassType 所指定的类，并未实现 ICollector 接口，需要报错：
                throw new PhpTdException(
                    sprintf(ErrorMessage::REFLECTION_ERR_INVALID_INTERFACE_MESSAGE, $classType, 'ICollectionPoint') , 
                    ErrorCode::REFLECTION_ERR_INVALID_INTERFACE);
            }

            $points[] = $pointObj;
        }

        return $points;
    }

}