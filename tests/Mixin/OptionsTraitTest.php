<?php
namespace Povs\ListerBundle\Mixin;

use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class OptionsTraitTest extends TestCase
{
    private const OPTIONS = [
        'option1' => 'value1',
        'option2' => 'value2'
    ];

    private $options;

    public function setUp()
    {
        $this->options = $this->getMockForTrait(OptionsTrait::class);
        $this->options->initOptions(self::OPTIONS);
    }

    public function testSetOption(): void
    {
        $this->options->setOption('foo', 'bar');
        $this->assertEquals('bar', $this->options->getOption('foo'));
    }

    public function testSetOptionOverwrite(): void
    {
        $this->options->setOption('option1', 'new_value');
        $this->assertEquals('new_value', $this->options->getOption('option1'));
    }

    public function testGetOption(): void
    {
        $this->assertEquals('value2', $this->options->getOption('option2'));
    }

    public function testGetOptionDefault(): void
    {
        $this->assertEquals('default', $this->options->getOption('foo', 'default'));
    }

    public function testHasOption(): void
    {
        $this->assertTrue($this->options->hasOption('option1'));
    }

    public function testDontHaveOption(): void
    {
        $this->assertFalse($this->options->hasOption('something'));
    }
}