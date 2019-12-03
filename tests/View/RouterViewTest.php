<?php
namespace Povs\ListerBundle\View;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Service\RequestHandler;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RouterViewTest extends TestCase
{
    private $requestHandler;
    private $router;

    public function setUp()
    {
        $this->requestHandler = $this->createMock(RequestHandler::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    public function testGetPageRoute(): void
    {
        $this->requestHandler->expects($this->once())
            ->method('getName')
            ->with('page')
            ->willReturn('foo');

        $view = $this->getRouterView(['foo' => 5], true);
        $this->assertEquals('test_route', $view->getPageRoute(5));
    }

    public function testGetLengthRoute(): void
    {
        $this->requestHandler->expects($this->exactly(2))
            ->method('getName')
            ->willReturnMap([
                ['length', 'length_name'],
                ['page', 'page_name']
            ]);

        $view = $this->getRouterView(['length_name' => 100, 'page_name' => 1], true);
        $this->assertEquals('test_route', $view->getLengthRoute(100));
    }

    public function testGetSortRoute(): void
    {
        $this->requestHandler->expects($this->once())
            ->method('getName')
            ->with('sort')
            ->willReturn('sort_name');

        $view = $this->getRouterView(['sort_name' => ['foo' => 'bar']], true);
        $this->assertEquals('test_route', $view->getSortRoute('foo', 'bar'));
    }

    public function testGetGetRoute(): void
    {
        $view = $this->getRouterView([], false);
        $this->assertEquals('test_route', $view->getRoute());
    }

    public function testGetRequestName(): void
    {
        $this->requestHandler->expects($this->once())
            ->method('getName')
            ->with('foo')
            ->willReturn('bar');
        $view = $this->getRouterView(null);
        $this->assertEquals('bar', $view->getRequestName('foo'));
    }

    private function getRouterView(?array $params = null, bool $merge = false, array $requestParams = []): RouterView
    {
        if (true === $merge) {
            $requestMock = $this->createMock(Request::class);
            $paramsBagMock = $this->createMock(ParameterBag::class);
            $this->requestHandler->expects($this->once())
                ->method('getRequest')
                ->willReturn($requestMock);
            $paramsBagMock->expects($this->once())
                ->method('all')
                ->willReturn($requestParams);
            $requestMock->query = $paramsBagMock;
        }

        if (null !== $params) {
            $this->requestHandler->expects($this->once())
                ->method('getRoute')
                ->willReturn('route');

            $this->router->expects($this->once())
                ->method('generate')
                ->with('route', $params, 0)
                ->willReturn('test_route');
        }

        return new RouterView($this->requestHandler, $this->router);
    }
}