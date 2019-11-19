<?php
namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method SelectorTypeInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class SelectorTypeLocator extends ServiceLocator
{

}