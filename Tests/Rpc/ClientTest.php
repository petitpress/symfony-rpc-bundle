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
use Seven\RpcBundle\Rpc\Client;
use Seven\RpcBundle\Rpc\Method\MethodCall;

#[AllowMockObjectsWithoutExpectations]
class ClientTest extends TestCase
{

    public function testSuccessCall()
    {
        $implMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Implementation");
        $transportMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Transport\\TransportInterface");
        $httpRequestMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Request");
        $httpResponseMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Response");
        $methodReturnMock = $this->createMock("Seven\\RpcBundle\\Rpc\\Method\\MethodReturn");

        $client = new Client("http://webservice.url/path", $implMock, $transportMock);

        $implMock->expects($this->once())
            ->method("createHttpRequest")
            ->with($this->callback(function (MethodCall $methodCall) {
                return $methodCall->getMethodName() == "method.name" &&
                    $methodCall->getParameters() == array('param_1', 'param_2');
            }))
            ->willReturn($httpRequestMock);

        $transportMock->expects($this->once())
            ->method("makeRequest")
            ->willReturn($httpResponseMock);

        $implMock->expects($this->once())
            ->method("createMethodResponse")
            ->with($this->equalTo($httpResponseMock))
            ->willReturn($methodReturnMock);

        $methodReturnMock->expects($this->once())
            ->method("getReturnValue")
            ->willReturn("return_value");

        $this->assertEquals('return_value', $client->call("method.name", array('param_1', 'param_2')));
    }

}
