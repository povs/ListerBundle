<?php
namespace Povs\ListerBundle\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Service\Paginator;
use Povs\ListerBundle\Service\RequestHandler;
use Povs\ListerBundle\Service\ValueAccessor;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ViewFactoryTest extends TestCase
{
    public function testBuildView(): void
    {
        $valueAccessorMock = $this->createMock(ValueAccessor::class);
        $requestHandlerMock = $this->createMock(RequestHandler::class);
        $routerMock = $this->createMock(RouterInterface::class);
        $paginatorFactoryMock = $this->createMock(PaginatorFactory::class);
        $listMapperMock = $this->createMock(ListMapper::class);
        $formMock = $this->createMock(FormInterface::class);
        $formViewMock = $this->createMock(FormView::class);
        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $paginatorMock = $this->createMock(Paginator::class);
        $fieldMock = $this->createMock(ListField::class);
        $paginatorFactoryMock->expects($this->once())
            ->method('buildPaginator')
            ->with($queryBuilderMock)
            ->willReturn($paginatorMock);
        $formMock->expects($this->once())
            ->method('createView')
            ->willReturn($formViewMock);
        $listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection([$fieldMock]));
        $fieldMock->expects($this->once())
            ->method('getOption')
            ->with('view_options', [])
            ->willReturn([]);

        $factory = new ViewFactory($valueAccessorMock, $requestHandlerMock, $routerMock, $paginatorFactoryMock);
        $view = $factory->buildView($listMapperMock, $formMock, $queryBuilderMock, 20, 2);
        $this->assertEquals(2, $view->getPager()->getCurrentPage());
        $this->assertEquals(20, $view->getPager()->getLength());
        $this->assertEquals($formViewMock, $view->getFilter());
        $this->assertCount(1, $view->getFieldViews());
    }
}