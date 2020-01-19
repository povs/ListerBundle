<?php

namespace Povs\ListerBundle\View;

use Povs\ListerBundle\Service\RequestHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RouterView
{
    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RequestHandler  $requestHandler
     * @param RouterInterface $router
     */
    public function __construct(RequestHandler $requestHandler, RouterInterface $router)
    {
        $this->requestHandler = $requestHandler;
        $this->router = $router;
    }

    /**
     * @param int $page
     *
     * @return string
     */
    public function getPageRoute(int $page): string
    {
        $pageName = $this->requestHandler->getName('page');

        return $this->generate([$pageName => $page]);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function getLengthRoute(int $length): string
    {
        $lengthName = $this->requestHandler->getName('length');
        $pageName = $this->requestHandler->getName('page');

        $params = [
            $lengthName => $length,
            $pageName => 1
        ];

        return $this->generate($params);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return string
     */
    public function getSortRoute(string $field, string $direction): string
    {
        $sortName = $this->requestHandler->getName('sort');

        return $this->generate([$sortName => [$field => $direction]]);
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->generate([], false);
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getRequestName(string $name): ?string
    {
        return $this->requestHandler->getName($name);
    }

    /**
     * @param array $params
     * @param bool  $merge
     *
     * @return string
     */
    public function generate(array $params, bool $merge = true): string
    {
        $request = $this->requestHandler->getRequest();

        if ($routeParams = $request->attributes->get('_route_params')) {
            $params = array_merge($params, $routeParams);
        }

        return $this->router->generate(
            $request->attributes->get('_route'),
            $merge ? array_merge($request->query->all(), $params) : $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
