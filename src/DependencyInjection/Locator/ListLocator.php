<?php

namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Declaration\ListInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method ListInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListLocator extends ServiceLocator
{

}
