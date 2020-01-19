<?php

namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method ListTypeInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListTypeLocator extends ServiceLocator
{

}
