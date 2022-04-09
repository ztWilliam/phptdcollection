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
        //当在 tdengine 服务不可用时，可打开注释，跳过此测试
        $this->markTestSkipped();

        //创建一个用来模拟 tdengine 返回数据的对象
        $connManager = new TdConnectionManager();

        $conn = $connManager->getConnection([]);

        $result = $conn->exec('create database lin_test keep 365 days 30 update 2');

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
        $this->assertEquals(1, $result->rowsAffected());
        
        $conn->close();
    }

    public function testQuery_Select() {
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
                json_decode('{"status":"succ", "head":["ts","level","content"], "column_meta":[["ts",9,8],["level",2,1],["content",8,512]], "data":[["2022-03-22 08:59:09.377374",0,"user:root login from 172.17.0.2, result:success"],["2022-03-22 08:59:09.385644",0,"stable:0.log.taosadapter_restful_http_total, is created in sdb, uid:14615152489670908782"],["2022-03-22 08:59:09.389024",0,"stable:0.log.taosadapter_restful_http_fail, is created in sdb, uid:14615152589361126608"],["2022-03-22 08:59:09.391720",0,"stable:0.log.taosadapter_restful_http_request_in_flight, is created in sdb, uid:14615152637813726493"],["2022-03-22 08:59:09.394237",0,"stable:0.log.taosadapter_restful_http_request_latency, is created in sdb, uid:14615152684135620129"],["2022-03-22 08:59:09.396740",0,"stable:0.log.taosadapter_system, is created in sdb, uid:14615152725139136413"],["2022-03-22 09:00:09.142396",0,"user:root login from 172.17.0.2, result:success"],["2022-03-22 09:21:26.441697",0,"user:linww, is created successfully"],["2022-03-22 09:21:52.259268",0,"user:linww login from 172.17.0.2, result:success"],["2022-03-27 06:17:57.117823",0,"user:root login from 172.17.0.2, result:success"],["2022-03-27 06:18:16.843814",0,"user:root login from 172.17.0.2, result:success"]], "rows":11}', false)
            );

        $connManager = new TdConnectionManager();

        $conn = $connManager->getConnection([], $client);

        $result = $conn->query('select * from dnode_1_log');

        //检查 $result 是否正确：
        $this->assertTrue($result instanceof RestfulTdQueryResult);
        $this->assertEquals(11, $result->rowsAffected());
        $this->assertEquals("2022-03-22 08:59:09.389024", $result->getFieldValue(2, 'ts'));
        $this->assertEquals(2, $result->fieldType('level'));
        
        $conn->close();
    }

    public function testQuery_Select_WithoutMock() {
        //当在 tdengine 服务不可用时，可打开注释，跳过此测试
        $this->markTestSkipped();

        //创建一个用来模拟 tdengine 返回数据的对象
        $connManager = new TdConnectionManager();

        $conn = $connManager->getConnection([]);

        $result = $conn->exec('select * from dnode_1_log');

        //检查 $result 是否正确：
        $this->assertTrue($result instanceof RestfulTdResult);
        
        $conn->close();

    }
}