<?php

namespace Povs\ListerBundle\Type\QueryType;

use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class AbstractQueryTypeTest extends TestCase
{
    private $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMockForAbstractClass(AbstractQueryType::class);
    }

    public function testSetOptions(): void
    {
        $options = ['foo' => 'bar'];
        $this->subject->setOptions($options);
        $this->assertEquals('bar', $this->subject->getOption('foo'));
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $this->subject->configureOptions($optionResolver);
        $this->assertEmpty($optionResolver->getDefinedOptions());
        $this->assertEmpty($optionResolver->getRequiredOptions());
    }

    public function testHasAggregation(): void
    {
        $this->assertFalse($this->subject->hasAggregation());
    }
}
