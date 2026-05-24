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
use Seven\RpcBundle\XmlRpc\ValueType\DoubleType;

#[AllowMockObjectsWithoutExpectations]
class DoubleTypeTest extends TestCase
{
    public function testPacking()
    {
        $typeInstance = new DoubleType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), 12.3);

        $this->assertEquals(
            array('value' => array('double' => '12.3')),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new DoubleType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $document->appendChild($valueEl = $document->createElement('double', 12.3));

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals(12.3, $value);
    }

}
