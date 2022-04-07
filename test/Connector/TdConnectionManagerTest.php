<?php
namespace WztzTech\Iot\PhpTd\Test\Connector;

use PHPUnit\Framework\TestCase;

use WztzTech\Iot\PhpTd\Connector\TdConnectionManager;
use WztzTech\Iot\PhpTd\Connector\Restful\RestfulTdConnection;

use WztzTech\Iot\PhpTd\Exception\{PhpTdException, ErrorCode, ErrorMessage};
use WztzTech\Iot\PhpTd\Util\HttpClient;

class TdConnectionManagerTest extends TestCase {
    
    /**
     * 测试按默认参数建立连接，能够生成可用的连接实例。
     */
    public function testGetConnectionWithDefault() {
        //创建一个用来模拟 tdengine 返回数据的对象
        $client = $this->createMock(HttpClient::class);

        // $client会被调用两次：
        // 第一次是建立连接的登录调用
        // 第二次是
        $client->expects($this->once())
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                json_decode('{"status":"succ","code":0,"desc":"/KfeAzX/f9na8qdtNZmtONryp201ma04bEl8LcvLUd7a8qdtNZmtONryp201ma04"}', false),
            );

        $manager = new TdConnectionManager();

        $connObj = $manager->getConnection([], $client);

        self::assertTrue($connObj instanceof RestfulTdConnection );        
    }

    /**
     * 测试错误的类名，无法创建connection对象，并抛出合适的异常
     */
    public function testConnecorReflectionWithWrongClassName()
    {
        $className = 'Wztz\Iot\Wrong\RestfulTdConnection';

        $manager = new TdConnectionManager();
        $manager->connector_class = $className;

        $this->expectException(PhpTdException::class);
        $this->expectExceptionCode(ErrorCode::REFLECTION_ERR_INVALID_CLASS_NAME);
        $manager->getConnection();

    }

    /**
     * 测试传入的类并不是 ITdConnection 接口的实现类 的情况
     */
    public function testConnectorReflectionWithInvalidInterface() {
        $className = 'WztzTech\Iot\PhpTd\Connector\Restful\RestfulTdQueryResult';

        $manager = new TdConnectionManager();
        $manager->connector_class = $className;

        $this->expectException(PhpTdException::class);
        $this->expectExceptionCode(ErrorCode::REFLECTION_ERR_INVALID_INTERFACE);
        $this->expectExceptionMessage(sprintf(ErrorMessage::REFLECTION_ERR_INVALID_INTERFACE_MESSAGE, $className, 'ITdConnection'));

        $manager->getConnection();

    }
}