<?php

/**
 * @author Łukasz Pior <pior.lukasz@gmail.com>
 */

namespace Seven\RpcBundle\Tests\Transport;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TransportCurlTest extends TestCase
{
    #[DataProvider('providerMakeRequestWithCurlError')]
    public function testMakeRequestWithCurlError($errorCode, $errorMessage)
    {
        $this->expectException(\Seven\RpcBundle\Rpc\Exception\CurlTransportException::class);

        $transportMock = $this->getMockBuilder("Seven\\RpcBundle\\Rpc\\Transport\\TransportCurl")
                                ->onlyMethods(array("getCurlRequest"))
                                ->getMock();

        $requestMock = $this->createMock("Symfony\\Component\\HttpFoundation\\Request");

        $curlRequestMock = $this->getMockBuilder("Seven\\RpcBundle\\Rpc\\Transport\\Curl\\CurlRequest")
                                ->onlyMethods(array("execute", "getErrorNumber", "getErrorMessage"))
                                ->getMock();

        $curlRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $curlRequestMock->expects($this->once())
            ->method('getErrorNumber')
            ->willReturn($errorCode);

        $curlRequestMock->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn($errorMessage);

        $transportMock->expects($this->once())
            ->method('getCurlRequest')
            ->willReturn($curlRequestMock);

        $transportMock->makeRequest($requestMock);
    }

    public static function providerMakeRequestWithCurlError(): array
    {
        return array(
            array(CURLE_COULDNT_CONNECT, "Failed to connect() to host or proxy."),
            array(CURLE_OPERATION_TIMEOUTED, "Operation timeout. The specified time-out period was reached according to the conditions."),
        );
    }
}
