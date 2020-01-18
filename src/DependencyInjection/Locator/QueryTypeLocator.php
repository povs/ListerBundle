<?php

namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Type\QueryType\QueryTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method QueryTypeInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class QueryTypeLocator extends ServiceLocator
{

}
