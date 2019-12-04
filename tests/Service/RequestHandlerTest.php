<?php
namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\FilterField;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RequestHandlerTest extends TestCase
{
    public function testHandlerRequest(): void
    {
        $listMapperMock = $this->createMock(ListMapper::class);
        $filterMapperMock = $this->createMock(FilterMapper::class);
        $formMock = $this->createMock(FormInterface::class);
        $listFieldMock = $this->createMock(ListField::class);
        $listFieldMock->expects($this->once())
            ->method('setOption')
            ->with('sort_value', 'DESC');
        $listMapperMock->expects($this->once())
            ->method('get')
            ->willReturn($listFieldMock);
        $formMock->expects($this->once())
            ->method('handleRequest');
        $formMock->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);
        $formMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $formMock->expects($this->once())
            ->method('getData')
            ->willReturn(['field1' => 'val1', 'field2' => 'val2']);
        $filterFieldMock1 = $this->createMock(FilterField::class);
        $filterFieldMock2 = $this->createMock(FilterField::class);
        $filterFieldMock1->expects($this->once())
            ->method('setValue')
            ->with('val1');
        $filterFieldMock2->expects($this->once())
            ->method('setValue')
            ->with('val2');
        $filterMapperMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['field1', $filterFieldMock1],
                ['field2', $filterFieldMock2]
            ]);

        $requestHandler = $this->getRequestHandler([['sort', null, ['field1' => 'desc', 'field2' => 'invalid_sort']]], [], [['sort', 'sort']]);
        $requestHandler->handleRequest($listMapperMock, $filterMapperMock, $formMock);
    }

    public function testGetCurrentPage(): void
    {
        $handler = $this->getRequestHandler([['page', null, 20]], [], [['page', 'page']]);

        $this->assertEquals(20, $handler->getCurrentPage());
    }

    public function testGetLength(): void
    {
        $handler = $this->getRequestHandler([['length', null, 20]], [], [['length', 'length']]);

        $this->assertEquals(20, $handler->getLength());
    }

    public function testGetRoute(): void
    {
        $handler = $this->getRequestHandler([], [['_route', null, 'test_route']], []);

        $this->assertEquals('test_route', $handler->getRoute());
    }

    public function testGetValue(): void
    {
        $handler = $this->getRequestHandler([['foo', null, 'custom_value']], [], [['custom_name', 'foo']]);

        $this->assertEquals('custom_value', $handler->getValue('custom_name'));
    }

    public function testGetName(): void
    {
        $handler = $this->getRequestHandler([], [], [['custom_name', 'foo']]);

        $this->assertEquals('foo', $handler->getName('custom_name'));
    }

    public function testGetRequest(): void
    {
        $handler = $this->getRequestHandler([], [], []);
        $this->assertNotNull($handler->getRequest());
    }

    private function getRequestHandler(array $query, array $attributes, array $configs): RequestHandler
    {
        $requestMock = $this->createMock(Request::class);
        $queryMock = $this->createMock(ParameterBag::class);
        $attributesMock = $this->createMock(ParameterBag::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $requestStackMock = $this->createMock(RequestStack::class);

        $queryMock->expects($this->exactly(count($query)))
            ->method('get')
            ->willReturnMap($query);
        $attributesMock->expects($this->exactly(count($attributes)))
            ->method('get')
            ->willReturnMap($attributes);
        $configMock->expects($this->exactly(count($configs)))
            ->method('getRequestConfiguration')
            ->willReturnMap($configs);
        $requestMock->query = $queryMock;
        $requestMock->attributes = $attributesMock;
        $requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        return new RequestHandler($requestStackMock, $configMock);
    }
}