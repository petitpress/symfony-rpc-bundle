<?php

/*
 * This file is part of the Symfony bundle Seven/Rpc.
 *
 * (c) Sergey Kolodyazhnyy <sergey.kolodyazhnyy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seven\RpcBundle\Tests\XmlRpc\ValueType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Seven\RpcBundle\XmlRpc\ValueType\IntegerType;

#[AllowMockObjectsWithoutExpectations]
class IntegerTypeTest extends TestCase
{
    public function testPacking()
    {
        $typeInstance = new IntegerType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), 123);

        $this->assertEquals(
            array('value' => array('int' => 123)),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new IntegerType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $document->appendChild($valueEl = $document->createElement('i4', 123));

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals(123, $value);
    }

}
