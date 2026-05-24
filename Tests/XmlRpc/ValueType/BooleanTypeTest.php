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
use Seven\RpcBundle\XmlRpc\ValueType\BooleanType;

#[AllowMockObjectsWithoutExpectations]
class BooleanTypeTest extends TestCase
{
    public function testPacking()
    {
        $typeInstance = new BooleanType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $domElement = $typeInstance->pack(new \DOMDocument(), true);

        $this->assertEquals(
            array('value' => array('boolean' => '1')),
            array($domElement->tagName => XmlAssertHelper::xml2array($domElement))
        );
    }

    public function testExtracting()
    {
        $typeInstance = new BooleanType($this->createMock("Seven\\RpcBundle\\XmlRpc\\Implementation"));
        $document = new \DOMDocument();
        $document->appendChild($valueEl = $document->createElement('boolean', 1));

        $value = $typeInstance->extract($valueEl);

        $this->assertEquals(true, $value);
    }

}
