<?php
/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\Rpc;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Seven\RpcBundle\Rpc\Server;
use Seven\RpcBundle\Tests\Rpc\Asserts\CallbackHandler;

#[AllowMockObjectsWithoutExpectations]
class ServerTest extends TestCase
{
    public function testRequestHandle()
    {
        $httpRequestMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Response");
        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $methodCallMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Method\\MethodCall");
        $handlerMock = $this->getMockBuilder(CallbackHandler::class)->onlyMethods(array('op'))->getMock();

        $handlerMock->expects($this->once())
            ->method('op')
            ->with($this->equalTo('parameter_1'), $this->equalTo('parameter_2'))
            ->willReturn('return_value');

        $methodCallMock->expects($this->any())
            ->method('getMethodName')
            ->willReturn('method.op');

        $methodCallMock->expects($this->any())
            ->method('getParameters')
            ->willReturn(array('parameter_1', 'parameter_2'));

        $implementationMock->expects($this->once())
            ->method('createMethodCall')
            ->with($this->equalTo($httpRequestMock))
            ->willReturn($methodCallMock);

        $implementationMock->expects($this->once())
            ->method('createHttpResponse')
            ->with($this->isInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn"))
            ->willReturn($httpResponseMock);

        $server = new Server($implementationMock);
        $server->addHandler('method', $handlerMock);

        $httpResponse = $server->handle($httpRequestMock);

        $this->assertSame($httpResponseMock, $httpResponse);
    }

    public function testRequestHandleWithException()
    {
        $httpRequestMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Response");
        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $methodCallMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Method\\MethodCall");
        $handlerMock = $this->getMockBuilder(CallbackHandler::class)->onlyMethods(array('op'))->getMock();

        $handlerMock->expects($this->once())
            ->method('op')
            ->with($this->equalTo('parameter_1'), $this->equalTo('parameter_2'))
            ->willReturnCallback(function () {
                throw new \Exception('Test', 10);
            });

        $methodCallMock->expects($this->any())
            ->method('getMethodName')
            ->willReturn('method.op');

        $methodCallMock->expects($this->any())
            ->method('getParameters')
            ->willReturn(array('parameter_1', 'parameter_2'));

        $implementationMock->expects($this->once())
            ->method('createMethodCall')
            ->with($this->equalTo($httpRequestMock))
            ->willReturn($methodCallMock);

        $implementationMock->expects($this->once())
            ->method('createHttpResponse')
            ->with($this->isInstanceOf("Seven\\RpcBundle\\Rpc\\Method\\MethodFault"))
            ->willReturn($httpResponseMock);

        $server = new Server($implementationMock);
        $server->addHandler('method', $handlerMock);

        $httpResponse = $server->handle($httpRequestMock);

        $this->assertSame($httpResponseMock, $httpResponse);
    }

    public function testServerCallWithCallback()
    {
        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $callbackMock = $this->getMockBuilder(CallbackHandler::class)->onlyMethods(array('callbackMethod'))->getMock();

        $callbackMock->expects($this->once())
            ->method('callbackMethod')
            ->with($this->equalTo("parameter_1"), $this->equalTo("parameter_2"))
            ->willReturn("result_value");

        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodName', array($callbackMock, 'callbackMethod'));

        $this->assertEquals("result_value", $server->call('rpcMethodName', array("parameter_1", "parameter_2")));
    }

    public function testServerCallWithObject()
    {
        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', new \Seven\RpcBundle\Tests\Rpc\Asserts\ServerHandler());

        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallWithClassName()
    {
        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");

        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidGroupname()
    {
        $this->expectException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('test', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidMethodname()
    {
        $this->expectException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('test', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('concat', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidMethodnameInGroup()
    {
        $this->expectException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', "Seven\\RpcBundle\\Tests\\Rpc\\Asserts\\ServerHandler");
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup.nonexists', array("parameter_1", "parameter_2")));
    }

    public function testServerCallExceptionInvalidCallback()
    {
        $this->expectException("Seven\\RpcBundle\\Exception\\MethodNotExists");

        $implementationMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $server = new Server($implementationMock);
        $server->addHandler('rpcMethodGroup', 111);
        $this->assertEquals("parameter_1:parameter_2", $server->call('rpcMethodGroup', array("parameter_1", "parameter_2")));
    }

}
