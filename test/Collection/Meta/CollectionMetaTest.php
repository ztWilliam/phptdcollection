<?php
namespace WztzTech\Iot\PhpTd\Test\Collection\Meta;

use PhpParser\ErrorHandler\Collecting;
use PHPUnit\Framework\TestCase;
use WztzTech\Iot\PhpTd\Collection\BaseCollectionStore;
use WztzTech\Iot\PhpTd\Collection\BaseCollector;
use WztzTech\Iot\PhpTd\Collection\Demo\CollectorDemo;
use WztzTech\Iot\PhpTd\Collection\Demo\StoreDemo;
use WztzTech\Iot\PhpTd\Collection\Meta\CollectionMeta;
use WztzTech\Iot\PhpTd\Connector\TdConnectionManager;
use WztzTech\Iot\PhpTd\Exception\PhpTdException;
use WztzTech\Iot\PhpTd\Exception\TdException;
use WztzTech\Iot\PhpTd\Util\HttpClient;

use function PHPUnit\Framework\exactly;

class CollectionMetaTest extends TestCase {

    public function testInit_reset_Without_Mock() {
        //本测试代码会真正执行init并reset数据，默认为跳过，请务必慎重执行！！
        // $this->markTestSkipped();

        $meta = CollectionMeta::getMetaAgent();

        $initResult = $meta->init(true);

        //验证meta库是否创建，并创建初始的三个超级表：
        $this->assertEquals(0, $initResult);
    }

    public function testRegisterStore_Without_Mock() {
        // $this->markTestSkipped();

        $meta = CollectionMeta::getMetaAgent();

        $store = BaseCollectionStore::createStore('BaseStore_1', '用于测试看看的 store。' );

        $registerResult = $meta->registerStore($store);
        $this->assertEquals(0, $registerResult);

        $demoStore = StoreDemo::createStore('DemoStore_1', '测试继承自 BaseCollectionStore 的类型');
        $registerResult = $meta->registerStore($demoStore);

        $this->assertEquals(0, $registerResult);

        //将因为此测试而新创建的db移除：
        $connManager = new TdConnectionManager();
        $conn = $connManager->getConnection();
        $conn->exec('DROP DATABASE basestore_1');
        $conn->exec('DROP DATABASE demostore_1');

    }

    public function testInit_Exist_Without_Reset() {
        $client = $this->createMock(HttpClient::class);

        $client->expects(exactly(2))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                //连接登录
                json_decode('{"status":"succ","code":0,"desc":"/KfeAzX/f9na8qdtNZmtONryp201ma04bEl8LcvLUd7a8qdtNZmtONryp201ma04"}', false),
                //判断是否存在（ use db 成功）
                json_decode('{"status":"succ","head":["affected_rows"],"column_meta":[["affected_rows",4,4]],"rows":1,"data":[[0]]}', false)
            );

        $meta = CollectionMeta::getMetaAgent($client);

        $meta->init();
    }

    public function testRegisterStore_initDB_Failed() {
        
        $store = $this->createMock(BaseCollectionStore::class);

        $store->expects(exactly(1))->method('getName')->willReturn('BaseTestStore_2');
        $store->expects(exactly(1))->method('getDesc')->willReturn('测试init出错用的store');

        $store->expects(exactly(1))->method('initDB')->willThrowException(new TdException());

        $meta = CollectionMeta::getMetaAgent();

        $this->expectException(PhpTdException::class);
        $meta->registerStore($store);


    }

    public function testAllStores_WithMock() {
        $client = $this->createMock(HttpClient::class);

        $client->expects(exactly(3))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                //连接登录
                json_decode('{"status":"succ","code":0,"desc":"/KfeAzX/f9na8qdtNZmtONryp201ma04bEl8LcvLUd7a8qdtNZmtONryp201ma04"}', false),
                //查询基本信息的结果
                json_decode('{"status":"succ","head":["store_name","class_type","desc"],"column_meta":[["store_name",8,128],["class_type",8,200],["desc",10,200]],"data":[["store_BaseStore_1","WztzTech||Iot||PhpTd||Collection||BaseCollectionStore","用于测试看看的 store。"],["store_DemoStore_1","WztzTech||Iot||PhpTd||Collection||Demo||StoreDemo","测试继承自 BaseCollectionStore 的类型"]],"rows":2}', false),
                //查询数据统计信息的结果
                json_decode('{"status":"succ","head":["store_name","counting_time","point_count","collector_count","data_count","data_size","store_name"],"column_meta":[["store_name",8,128],["counting_time",9,8],["point_count",4,4],["collector_count",4,4],["data_count",5,8],["data_size",5,8],["store_name",8,128]],"data":[["store_BaseStore_1",1656258396330,0,0,0,null,"store_BaseStore_1"],["store_DemoStore_1",1656258396357,0,0,0,null,"store_DemoStore_1"]],"rows":2}' , false)
            );

        $meta = CollectionMeta::getMetaAgent($client);

        $stores = $meta->allStores();

        $this->assertCount(2, $stores);

        $this->assertArrayHasKey('store_BaseStore_1', $stores[0]);
        $this->assertArrayHasKey('store_DemoStore_1', $stores[0]);
        $this->assertArrayHasKey('store_BaseStore_1', $stores[1]);
        $this->assertArrayHasKey('store_DemoStore_1', $stores[1]);

        $this->assertInstanceOf("WztzTech\\Iot\\PhpTd\\Collection\\Demo\\StoreDemo", $stores[0]['store_DemoStore_1']);

        $this->assertInstanceOf("WztzTech\\Iot\\PhpTd\\Collection\\Meta\\Analyzer\\StoreCounterData", $stores[1]['store_BaseStore_1']);

    }

    public function testAllStores_WithoutMock() {
        $meta = CollectionMeta::getMetaAgent();

        $stores = $meta->allStores();

        $this->assertArrayHasKey('BaseStore_1', $stores[0]);
        $this->assertArrayHasKey('DemoStore_1', $stores[0]);
        $this->assertArrayHasKey('BaseStore_1', $stores[1]);
        $this->assertArrayHasKey('DemoStore_1', $stores[1]);

        $this->assertInstanceOf("WztzTech\\Iot\\PhpTd\\Collection\\Demo\\StoreDemo", $stores[0]['DemoStore_1']);

        $this->assertInstanceOf("WztzTech\\Iot\\PhpTd\\Collection\\Meta\\Analyzer\\StoreCounterData", $stores[1]['BaseStore_1']);

    }

    public function testRegisterCollector_WithoutMock() {
        $collector = BaseCollector::createCollector('BaseCollectorTest_1', '测试注册基础Collector');

        $collector_demo = CollectorDemo::createCollector('DemoCollector_Test_1', '测试其他类型的Collector');

        $meta = CollectionMeta::getMetaAgent();

        $meta->registerCollector($collector);

        //只要不报错就行：
        $this->assertEquals(0,0);

        $meta->registerCollector($collector_demo);

        //只要不报错就行：
        $this->assertEquals(0,0);
    }

}