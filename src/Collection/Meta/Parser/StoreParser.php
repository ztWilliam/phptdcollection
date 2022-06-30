<?php
namespace WztzTech\Iot\PhpTd\Collection\Meta\Parser;

use WztzTech\Iot\PhpTd\Collection\Meta\Analyzer\StoreCounterData;
use WztzTech\Iot\PhpTd\Connector\ITdQueryResult;
use WztzTech\Iot\PhpTd\Exception\{ErrorCode, ErrorMessage, PhpTdException};

class StoreParser {

    /**
     * 将结果集中的数据，转化成对应的 ICollectionStore 对象数组
     * 
     * @param ITdQueryResult $queryResult 从 Meta 数据库中查询出的 store 基本信息结果集
     * 
     * @return array 以 store_name 为 key， 以 相应 class_type 的实例对象为 value 的对象数组
     */
    public static function parseStore( ITdQueryResult $queryResult) : array {

        $rows = $queryResult->rowsAffected();
        $stores = [];

        for( $i = 0; $i < $rows; $i++ ) {
            $rowData = $queryResult->fetchDataRow($i);

            $storeName = $rowData['store_name'];
            $classType = $rowData['class_type'];
            $storeDesc = $rowData['desc'];

            $classType = str_replace(ParserConstant::CLASS_TYPE_SEPARATOR_REPLACE, ParserConstant::CLASS_TYPE_SEPARATOR, $classType);

            $storeClass = new \ReflectionClass($classType);

            if ($storeClass->hasMethod(ParserConstant::STORE_CREATE_METHOD_NAME)) {

                $createMethod = $storeClass->getMethod(ParserConstant::STORE_CREATE_METHOD_NAME);
    
                $storeObj = $createMethod->invoke( null, //因为 createStore 是静态方法，所以实例参数传 null 即可
                    $storeName, $storeDesc      //只传注册时保存的属性，其他参数按默认值，由具体类的定义来做。
                );
    
            } else {
                //“没有 创建store 的 方法”，说明 ClassType 所指定的类，并未实现 ICollectionStore 接口，需要报错：
                throw new PhpTdException(
                    sprintf(ErrorMessage::REFLECTION_ERR_INVALID_INTERFACE_MESSAGE, $classType, 'ICollectionStore') , 
                    ErrorCode::REFLECTION_ERR_INVALID_INTERFACE);
            }

            $stores[$storeName] = $storeObj;
    
        }

        return $stores;
    }

    /**
     * 将结果集中的数据，转化成 StoreCounterData 对象数组
     * 
     * @param ITdQueryResult $queryResult 从 Meta 数据库中查询出的 store 最新的统计信息结果集
     * 
     * @return array 以 store_name 为 key， 以 StoreCounterData 对象为 value 的对象数组
     */
    public static function parseStoreInfo( ITdQueryResult $queryResult) : array {
        $rows = $queryResult->rowsAffected();
        $storeData = [];

        for( $i = 0; $i < $rows; $i++ ) {
            $rowData = $queryResult->fetchDataRow($i);
            $storeName = $rowData['store_name'];

            $storeDataObj = new StoreCounterData(
                $storeName,
                $rowData['counting_time'],
                is_null($rowData['point_count']) ? 0 : $rowData['point_count'],
                is_null($rowData['collector_count']) ? 0 : $rowData['collector_count'],
                is_null($rowData['data_count']) ? 0 : $rowData['data_count'],
                is_null($rowData['data_size']) ? 0 : $rowData['data_size']
            );
            
            $storeData[$storeName] = $storeDataObj;
    
        }

        return $storeData;
    }
    
}