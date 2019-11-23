<?php
namespace Povs\ListerBundle\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListExceptionTest extends TestCase
{
    public function testListNotConfigured(): void
    {
        $expected = 'List is not yet configured to be copied.';
        $actual = ListException::listNotConfigured()->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testListTypeNotConfigured(): void
    {
        $expected = 'List type "test" is not configured';
        $actual = ListException::listTypeNotConfigured('test')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testListNotBuilt(): void
    {
        $expected = 'List is not built. BuiltList method is required before generating';
        $actual = ListException::listNotBuilt()->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidListType(): void
    {
        $expected = 'List type "test" does not exists or does not implements Povs\ListerBundle\Type\ListType\ListTypeInterface';
        $actual = ListException::invalidListType('test')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidTypeConfiguration(): void
    {
        $expected = 'Invalid type "test" configuration. config_error';
        $actual = ListException::invalidTypeConfiguration('test', 'config_error')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidTypeOptions(): void
    {
        $expected = 'Invalid type "test" options. options_error';
        $actual = ListException::invalidTypeOptions('test', 'options_error')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testMissingTranslator(): void
    {
        $expected = 'Translator could not be found. Please install it running "composer require symfony/translation" or change list configuration';
        $actual = ListException::missingTranslator()->getMessage();
        $this->assertEquals($expected, $actual);
    }
}