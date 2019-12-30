<?php
namespace Povs\ListerBundle\Service;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Definition\ListInterface;
use Povs\ListerBundle\Definition\ListValueInterface;
use Povs\ListerBundle\DependencyInjection\Locator\ListLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Factory\MapperFactory;
use Povs\ListerBundle\Factory\ListValueFactory;
use Povs\ListerBundle\Factory\ViewFactory;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinMapper;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\View\ListView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListManagerTest extends TestCase
{
    private $listLocatorMock;
    private $listMock;
    private $configMock;
    private $typeResolverMock;
    private $mapperFactoryMock;
    private $filterBuilderMock;
    private $requestHandlerMock;
    private $valueFactoryMock;
    private $listQueryBuilderMock;
    private $viewFactoryMock;

    private $listMapperMock;
    private $filterMapperMock;
    private $filterFormMock;
    private $valueMock;
    private $joinMapperMock;
    private $queryBuilderMock;
    private $lazyQueryBuilderMock;
    private $viewMock;

    public function setUp()
    {
        $this->listLocatorMock = $this->createMock(ListLocator::class);
        $this->listMock = $this->createMock(ListInterface::class);
        $this->configMock = $this->createMock(ConfigurationResolver::class);
        $this->typeResolverMock = $this->createMock(ListTypeResolver::class);
        $this->mapperFactoryMock = $this->createMock(MapperFactory::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->requestHandlerMock = $this->createMock(RequestHandler::class);
        $this->valueFactoryMock = $this->createMock(ListValueFactory::class);
        $this->listQueryBuilderMock = $this->createMock(ListQueryBuilder::class);
        $this->viewFactoryMock = $this->createMock(ViewFactory::class);
        $this->listMapperMock = $this->createMock(ListMapper::class);
        $this->filterMapperMock = $this->createMock(FilterMapper::class);
        $this->filterFormMock = $this->createMock(FormInterface::class);
        $this->valueMock = $this->createMock(ListValueInterface::class);
        $this->joinMapperMock = $this->createMock(JoinMapper::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->lazyQueryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->viewMock = $this->createMock(ListView::class);
    }


    public function testGetResponse(): void
    {
        $this->setBuildCalls();
        $response = new Response();
        $this->typeResolverMock->expects($this->once())
            ->method('getResponse')
            ->with($this->viewMock, ['opt1' => 'foo', 'opt2' => 'bar'])
            ->willReturn($response);
        $listManager = $this->getListManager();
        $listManager->buildList('test_list', 'list_type', ['param1' => 'foo', 'param2' => 'bar']);
        $res = $listManager->getResponse(['opt1' => 'foo', 'opt2' => 'bar']);

        $this->assertEquals($response, $res);
    }

    public function testGetResponseThrowsExceptionIfNotBuilt(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionMessage('List is not built. BuiltList method is required before generating');
        $this->expectExceptionCode(500);
        $listManager = $this->getListManager();
        $listManager->getResponse([]);
    }

    public function testGetData(): void
    {
        $this->setBuildCalls();
        $this->typeResolverMock->expects($this->once())
            ->method('getData')
            ->with($this->viewMock, ['opt1' => 'foo', 'opt2' => 'bar'])
            ->willReturn('test_data');
        $listManager = $this->getListManager();
        $listManager->buildList('test_list', 'list_type', ['param1' => 'foo', 'param2' => 'bar']);
        $res = $listManager->getData(['opt1' => 'foo', 'opt2' => 'bar']);
        $this->assertEquals('test_data', $res);
    }

    public function testGetDataThrowsExceptionIfNotBuilt(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionMessage('List is not built. BuiltList method is required before generating');
        $this->expectExceptionCode(500);
        $listManager = $this->getListManager();
        $listManager->getData([]);
    }

    private function setBuildCalls(): void
    {
        $this->listLocatorMock->expects($this->once())
            ->method('get')
            ->with('test_list')
            ->willReturn($this->listMock);
        $this->configMock->expects($this->once())
            ->method('resolve')
            ->with($this->listMock);
        $this->listMock->expects($this->once())
            ->method('setParameters')
            ->with(['param1' => 'foo', 'param2' => 'bar']);
        $this->typeResolverMock->expects($this->once())
            ->method('resolveType')
            ->with('list_type')
            ->willReturnSelf();
        $this->typeResolverMock->expects($this->once())
            ->method('getTypeName')
            ->willReturn('list_type');
        $this->typeResolverMock->expects($this->once())
            ->method('getPerPage')
            ->willReturn(20);
        $this->typeResolverMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(5);
        $this->mapperFactoryMock->expects($this->once())
            ->method('createListMapper')
            ->with($this->listMock, 'list_type')
            ->willReturn($this->listMapperMock);
        $this->mapperFactoryMock->expects($this->once())
            ->method('createFilterMapper')
            ->with($this->listMock)
            ->willReturn($this->filterMapperMock);
        $this->mapperFactoryMock->expects($this->once())
            ->method('createJoinMapper')
            ->with($this->listMock, $this->listMapperMock, $this->filterMapperMock, $this->valueMock)
            ->willReturn($this->joinMapperMock);
        $this->filterBuilderMock->expects($this->once())
            ->method('buildFilterForm')
            ->with($this->filterMapperMock)
            ->willReturn($this->filterFormMock);
        $this->requestHandlerMock->expects($this->once())
            ->method('handleRequest')
            ->with($this->listMapperMock, $this->filterMapperMock, $this->filterFormMock);
        $this->valueFactoryMock->expects($this->once())
            ->method('createListValue')
            ->with($this->listMapperMock, $this->filterMapperMock)
            ->willReturn($this->valueMock);
        $this->listQueryBuilderMock->expects($this->once())
            ->method('buildQuery')
            ->with($this->listMock, $this->joinMapperMock, $this->listMapperMock, $this->filterMapperMock, $this->valueMock)
            ->willReturn($this->queryBuilderMock);
        $this->listQueryBuilderMock->expects($this->once())
            ->method('buildLazyQuery')
            ->with($this->listMock, $this->joinMapperMock, $this->listMapperMock)
            ->willReturn($this->lazyQueryBuilderMock);
        $this->viewFactoryMock->expects($this->once())
            ->method('createView')
            ->with($this->listMapperMock, $this->filterFormMock, $this->queryBuilderMock, $this->lazyQueryBuilderMock, 20, 5)
            ->willReturn($this->viewMock);
    }

    private function getListManager(): ListManager
    {
        return new ListManager(
            $this->typeResolverMock,
            $this->configMock,
            $this->requestHandlerMock,
            $this->filterBuilderMock,
            $this->listQueryBuilderMock,
            $this->viewFactoryMock,
            $this->mapperFactoryMock,
            $this->valueFactoryMock,
            $this->listLocatorMock
        );
    }
}