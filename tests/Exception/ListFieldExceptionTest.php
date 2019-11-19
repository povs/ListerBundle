<?php
namespace Povs\ListerBundle\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListFieldExceptionTest extends TestCase
{
    public function testInvalidFieldConfiguration(): void
    {
        $expected = 'Invalid field "test" configuration. config_error';
        $actual = ListFieldException::invalidFieldConfiguration('test', 'config_error')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testFieldNotExists(): void
    {
        $expected = 'Field "id" do not exists';
        $actual = ListFieldException::fieldNotExists('id')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidPropertiesOption(): void
    {
        $expected = '"id" - properties can only be set for a single path';
        $actual = ListFieldException::invalidPropertiesOption('id')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidPath(): void
    {
        $expected = 'Could not find join for path "path"';
        $actual = ListFieldException::invalidPath('path')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testInvalidType(): void
    {
        $expected = 'Type "foo" does not exist or does not implement bar';
        $actual = ListFieldException::invalidType('id', 'foo', 'bar')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testGetFieldId(): void
    {
        $exception = new ListFieldException('id');
        $this->assertEquals('id', $exception->getFieldId());
    }
}