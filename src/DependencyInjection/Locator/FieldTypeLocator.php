<?php

namespace Povs\ListerBundle\DependencyInjection\Locator;

use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @method FieldTypeInterface get($id)
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FieldTypeLocator extends ServiceLocator
{

}
