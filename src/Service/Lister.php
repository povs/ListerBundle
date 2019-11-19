<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Definition\ListerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class Lister implements ListerInterface
{
    /**
     * @var ListManager
     */
    private $listManager;

    /**
     * @param ListManager $listManager
     */
    public function __construct(ListManager $listManager)
    {
        $this->listManager = $listManager;
    }

    /**
     * @inheritDoc
     */
    public function buildList(string $list, ?string $type = null, array $parameters = []): ListerInterface
    {
        $this->listManager->buildList($list, $type, $parameters);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generateResponse(array $options = []): Response
    {
        return $this->listManager->getResponse($options);
    }

    /**
     * @inheritDoc
     */
    public function generateData(array $options = [])
    {
        return $this->listManager->getData($options);
    }
}