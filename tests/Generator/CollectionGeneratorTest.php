<?php

namespace Generator;

use PostmanGeneratorBundle\Generator\CollectionGenerator;
use Prophecy\Argument;

class CollectionGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $resourceRegistryMock = $this->prophesize('PostmanGeneratorBundle\Registry\ResourceRegistry');
        $requestGeneratorMock = $this->prophesize('PostmanGeneratorBundle\Generator\RequestGenerator');
        $folderGeneratorMock = $this->prophesize('PostmanGeneratorBundle\Generator\FolderGenerator');
        $resourceMock = $this->prophesize('Dunglas\ApiBundle\Api\ResourceInterface');
        $folderMock = $this->prophesize('PostmanGeneratorBundle\Model\Folder');
        $requestMock = $this->prophesize('PostmanGeneratorBundle\Model\Request');

        $resourceRegistryMock->getResources()->willReturn([$resourceMock->reveal()])->shouldBeCalledTimes(1);
        $folderGeneratorMock->generate($resourceMock->reveal())->willReturn($folderMock->reveal())->shouldBeCalledTimes(1);
        $requestGeneratorMock->generate($resourceMock->reveal())->willReturn([$requestMock->reveal()])->shouldBeCalledTimes(1);
        $folderMock->setRequests([$requestMock->reveal()])->shouldBeCalledTimes(1);
        $folderMock->setCollection(Argument::type('PostmanGeneratorBundle\Model\Collection'))->shouldBeCalledTimes(1);
        $folderMock->getRequests()->willReturn([$requestMock->reveal()])->shouldBeCalledTimes(1);

        $generator = new CollectionGenerator(
            $resourceRegistryMock->reveal(),
            $requestGeneratorMock->reveal(),
            $folderGeneratorMock->reveal(),
            false,
            'API Platform',
            'API Platform Postman collection'
        );

        $collection = $generator->generate();

        $this->assertRegExp('/([A-z\d]{8})-([A-z\d]{4})-([A-z\d]{4})-([A-z\d]{4})-([A-z\d]{12})/', $collection->getId());
        $this->assertFalse($collection->isPublic());
        $this->assertEquals('API Platform', $collection->getName());
        $this->assertEquals('API Platform Postman collection', $collection->getDescription());
        $this->assertCount(1, $collection->getFolders());
        $this->assertEquals([$folderMock->reveal()], $collection->getFolders());
        $this->assertCount(1, $collection->getRequests());
        $this->assertEquals([$requestMock->reveal()], $collection->getRequests());
    }
}
