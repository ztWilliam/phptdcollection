<?php
namespace WztzTech\Iot\PhpTd\Test\Connector;

use PHPUnit\Framework\TestCase;

use WztzTech\Iot\PhpTd\Connector\TdConnectionManager;
use WztzTech\Iot\PhpTd\Connector\Restful\RestfulTdConnection;

use WztzTech\Iot\PhpTd\Exception\{PhpTdException, ErrorCode, ErrorMessage};
use WztzTech\Iot\PhpTd\Util\HttpClient;

class RestfulTdConnectionTest extends TestCase {

    public function testExec_CreateDb() {
        //创建一个用来模拟 tdengine 返回数据的对象
        $client = $this->createMock(HttpClient::class);

        // $client会被调用两次：
        // 第一次是建立连接的登录调用
        // 第二次是
        $client->expects($this->exactly(2))
            ->method('send')
            // ->withConsecutive(
            //     [],
            //     []
            // )
            ->willReturnOnConsecutiveCalls(
                json_decode('{"status":"succ","code":0,"desc":"/KfeAzX/f9na8qdtNZmtONryp201ma04bEl8LcvLUd7a8qdtNZmtONryp201ma04"}', false),
                json_decode('{"status":"succ","head":["affected_rows"],"column_meta":[["affected_rows",4,4]],"rows":1,"data":[[0]]}', false)
            );

        $connManager = new TdConnectionManager();

        $conn = $connManager->getConnection([], $client);

        $result = $conn->exec('create database if not exists lin_test keep 365 days 30 update 2');

        //检查 $result 是否正确：

        $this->markTestIncomplete();
    }
}