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
     * @param string $type
     *
     * @return string
     */
    public function getTypeRoute(string $type): string
    {
        $typeName = $this->requestHandler->getName('type');

        return $this->generate([$typeName => $type]);
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
     * @return string
     */
    public function getRequestName(string $name): string
    {
        return $this->requestHandler->getName($name);
    }

    /**
     * @param array $params
     * @param bool  $merge
     *
     * @return string
     */
    private function generate(array $params, bool $merge = true): string
    {
        return $this->router->generate(
            $this->requestHandler->getRoute(),
            $merge ? array_merge($this->requestHandler->getRequest()->query->all(), $params) : $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}