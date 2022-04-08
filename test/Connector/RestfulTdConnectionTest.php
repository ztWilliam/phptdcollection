<?php
namespace WztzTech\Iot\PhpTd\Test\Connector;

use PHPUnit\Framework\TestCase;

use WztzTech\Iot\PhpTd\Connector\TdConnectionManager;
use WztzTech\Iot\PhpTd\Connector\Restful\{RestfulTdConnection, RestfulTdResult, RestfulTDQueryResult};

use WztzTech\Iot\PhpTd\Exception\{PhpTdException, ErrorCode, ErrorMessage};
use WztzTech\Iot\PhpTd\Util\HttpClient;

class RestfulTdConnectionTest extends TestCase {

    /**
     * 不通过mock执行命令的测试
     * 适用于 tdengine 服务可用时的测试，测试程序是否真正能运行。
     * 
     * 在 tdengine 服务不可用时，建议将本测试跳过
     */
    public function testExec_CreateDb_Without_Mock() {
        //当在 tdengine 服务不可用时，可打开注释
        // $this->markTestSkipped();

        //创建一个用来模拟 tdengine 返回数据的对象
        $connManager = new TdConnectionManager();

        $conn = $connManager->getConnection([]);

        $result = $conn->exec('create database if not exists lin_test keep 365 days 30 update 2');

        //检查 $result 是否正确：
        $this->assertTrue($result instanceof RestfulTdResult);
        $this->assertEquals(0, $result->rowsAffected());
        
        $conn->close();

    }

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
        $this->assertTrue($result instanceof RestfulTdResult);
        $this->assertEquals(0, $result->rowsAffected());
        
        $conn->close();
    }
}