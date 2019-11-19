<?php
namespace Povs\ListerBundle\Type\ListType;

use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class AbstractListTypeTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        $this->subject = $this->getMockForAbstractClass(AbstractListType::class);
    }

    public function testConfigureSettings(): void
    {
        $optionResolver = new OptionsResolver();
        $this->subject->configureSettings($optionResolver);
        $this->assertEmpty($optionResolver->getDefinedOptions());
        $this->assertEmpty($optionResolver->getRequiredOptions());
    }

    public function testConfigureOptions(): void
    {
        $optionResolver = new OptionsResolver();
        $this->subject->configureOptions($optionResolver);
        $this->assertEmpty($optionResolver->getDefinedOptions());
        $this->assertEmpty($optionResolver->getRequiredOptions());
    }
}