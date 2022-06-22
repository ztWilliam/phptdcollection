<?php
namespace WztzTech\Iot\PhpTd\Test\Collection\Meta;

use PHPUnit\Framework\TestCase;
use WztzTech\Iot\PhpTd\Collection\BaseCollectionStore;
use WztzTech\Iot\PhpTd\Collection\Meta\CollectionMeta;
use WztzTech\Iot\PhpTd\Util\HttpClient;

use function PHPUnit\Framework\exactly;

class CollectionMetaTest extends TestCase {

    public function testInit_reset_Without_Mock() {
        //本测试代码会真正执行init并reset数据，默认为跳过，请务必慎重执行！！
        $this->markTestSkipped();

        $meta = CollectionMeta::getMetaAgent();

        $initResult = $meta->init(true);

        //验证meta库是否创建，并创建初始的三个超级表：
        $this->assertEquals(0, $initResult);
    }

    public function testRegisterStore_Without_Mock() {
        $this->markTestSkipped();

        $meta = CollectionMeta::getMetaAgent();

        $store = BaseCollectionStore::createStore('BaseStore_1', '用于测试看看的 store。' );

        $registerResult = $meta->registerStore($store);

        $this->assertEquals(0, $registerResult);

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

}