<?php
namespace Povs\ListerBundle\Definition;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
interface ListerInterface
{
    /**
     * @param string      $list        fully qualified name of list class (must implement ListInterface)
     * @param string|null $type        list type name
     * @param array       $parameters  list parameters that will be passed to the List setParameters method
     * @see ListInterface
     *
     * @return ListerInterface
     */
    public function buildList(string $list, string $type, array $parameters = []): ListerInterface;

    /**
     * @param array $options
     *
     * @return Response
     */
    public function generateResponse(array $options = []): Response;

    /**
     * @param array $options
     *
     * @return mixed
     */
    public function generateData(array $options = []);
}