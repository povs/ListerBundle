<?php
namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method FilterTypeInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterTypeLocator extends ServiceLocator
{

}