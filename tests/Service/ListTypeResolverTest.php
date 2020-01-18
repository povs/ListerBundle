<?php

namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\DependencyInjection\Locator\ListTypeLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use Povs\ListerBundle\View\ListView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListTypeResolverTest extends TestCase
{
    private $getResponseData;
    private $getDataData;

    public function setUp()
    {
        $listViewMock = $this->createMock(ListView::class);
        $responseMock = $this->createMock(Response::class);
        $this->getResponseData = [$listViewMock, [], $responseMock];
        $this->getDataData = [$listViewMock, [], ['res' => 'data']];
    }

    /**
     * @return ListTypeResolver
     */
    public function testResolveType(): ListTypeResolver
    {
        $listTypeLocatorMock = $this->createMock(ListTypeLocator::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $requestHandlerMock = $this->createMock(RequestHandler::class);
        $listTypeMock = $this->createMock(ListTypeInterface::class);

        $requestHandlerMock->expects($this->once())
            ->method('getLength')
            ->willReturn(20);
        $requestHandlerMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(2);
        $configMock->expects($this->once())
            ->method('getTypeConfiguration')
            ->willReturn(['config1' => 'foo', 'config2' => 'bar']);
        $configMock->expects($this->once())
            ->method('getListTypeClass')
            ->with('foo_type')
            ->willReturn('bar_type');
        $listTypeLocatorMock->expects($this->once())
            ->method('has')
            ->with('bar_type')
            ->willReturn(true);
        $listTypeLocatorMock->expects($this->once())
            ->method('get')
            ->with('bar_type')
            ->willReturn($listTypeMock);
        $listTypeMock->expects($this->once())
            ->method('configureSettings')
            ->willReturnCallback(static function (OptionsResolver $resolver) {
                $resolver->setDefined(['config1', 'config2']);
            });
        $listTypeMock->expects($this->once())
            ->method('setConfig')
            ->with(['config1' => 'foo', 'config2' => 'bar']);
        $listTypeMock->expects($this->once())
            ->method('getLength')
            ->with(20)
            ->willReturn(20);
        $listTypeMock->expects($this->once())
            ->method('getCurrentPage')
            ->with(2)
            ->willReturn(2);
        $listTypeMock->method('generateResponse')
            ->with($this->getResponseData[0], $this->getResponseData[1])
            ->willReturn($this->getResponseData[2]);
        $listTypeMock->method('generateData')
            ->with($this->getDataData[0], $this->getDataData[1])
            ->willReturn($this->getDataData[2]);

        $typeResolver = new ListTypeResolver($listTypeLocatorMock, $configMock, $requestHandlerMock);
        $typeResolver->resolveType('foo_type');

        return $typeResolver;
    }

    /**
     * @depends testResolveType
     * @param ListTypeResolver $typeResolver
     */
    public function testGetTypeName(ListTypeResolver $typeResolver): void
    {
        $this->assertEquals('foo_type', $typeResolver->getTypeName());
    }

    /**
     * @depends testResolveType
     * @param ListTypeResolver $typeResolver
     */
    public function testGetPerPage(ListTypeResolver $typeResolver): void
    {
        $this->assertEquals(20, $typeResolver->getPerPage());
    }

    /**
     * @depends testResolveType
     * @param ListTypeResolver $typeResolver
     */
    public function testGetCurrentPage(ListTypeResolver $typeResolver): void
    {
        $this->assertEquals(2, $typeResolver->getCurrentPage());
    }

    /**
     * @depends testResolveType
     * @param ListTypeResolver $typeResolver
     */
    public function testGetResponse(ListTypeResolver $typeResolver): void
    {
        $res = $typeResolver->getResponse($this->getResponseData[0], $this->getResponseData[1]);
        $this->assertEquals($this->getResponseData[2], $res);
    }

    /**
     * @depends testResolveType
     * @param ListTypeResolver $typeResolver
     */
    public function testGetData(ListTypeResolver $typeResolver): void
    {
        $res = $typeResolver->getData($this->getDataData[0], $this->getDataData[1]);
        $this->assertEquals($this->getDataData[2], $res);
    }

    public function testResolveTypeThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage(sprintf('List type "invalid_type_class" does not exists or does not implements %s', ListTypeInterface::class));

        $listTypeLocatorMock = $this->createMock(ListTypeLocator::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $requestHandlerMock = $this->createMock(RequestHandler::class);

        $configMock->expects($this->once())
            ->method('getListTypeClass')
            ->with('invalid_type')
            ->willReturn('invalid_type_class');
        $listTypeLocatorMock->expects($this->once())
            ->method('has')
            ->with('invalid_type_class')
            ->willReturn(false);

        $listTypeResolver = new ListTypeResolver($listTypeLocatorMock, $configMock, $requestHandlerMock);
        $listTypeResolver->resolveType('invalid_type');
    }

    public function testResolveTypeThrowsExceptionOnInvalidConfiguration(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Invalid type "foo_type" configuration. The option "extra_param" does not exist. Defined options are: ""');

        $listTypeLocatorMock = $this->createMock(ListTypeLocator::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $requestHandlerMock = $this->createMock(RequestHandler::class);
        $typeMock = $this->createMock(ListTypeInterface::class);
        $listTypeLocatorMock->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $listTypeLocatorMock->expects($this->once())
            ->method('get')
            ->willReturn($typeMock);
        $configMock->expects($this->once())
            ->method('getTypeConfiguration')
            ->willReturn(['extra_param' => 'some_val']);

        $listTypeResolver = new ListTypeResolver($listTypeLocatorMock, $configMock, $requestHandlerMock);
        $listTypeResolver->resolveType('foo_type');
    }

    public function testResolveOptionsThrowsExceptionOnInvalidConfiguration(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Invalid type "foo_type" options. The option "invalid_option" with value "foo" is expected to be of type "array", but is of type "string".');

        $listTypeLocatorMock = $this->createMock(ListTypeLocator::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $requestHandlerMock = $this->createMock(RequestHandler::class);
        $typeMock = $this->createMock(ListTypeInterface::class);
        $listViewMock = $this->createMock(ListView::class);
        $listTypeLocatorMock->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $listTypeLocatorMock->expects($this->once())
            ->method('get')
            ->willReturn($typeMock);
        $typeMock->expects($this->once())
            ->method('configureOptions')
            ->willReturnCallback(static function (OptionsResolver $resolver) {
                $resolver->setDefined('invalid_option');
                $resolver->setAllowedTypes('invalid_option', 'array');
            });

        $listTypeResolver = new ListTypeResolver($listTypeLocatorMock, $configMock, $requestHandlerMock);
        $listTypeResolver->resolveType('foo_type');
        $listTypeResolver->getData($listViewMock, ['invalid_option' => 'foo']);
    }
}
