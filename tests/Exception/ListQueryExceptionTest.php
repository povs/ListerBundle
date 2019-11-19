<?php
namespace Povs\ListerBundle\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListQueryExceptionTest extends TestCase
{
    public function testInvalidQueryConfiguration(): void
    {
        $expected = 'Query error: bad sql. DQL: bad dql';
        $actual = ListQueryException::invalidQueryConfiguration('bad sql', 'bad dql')->getMessage();
        $this->assertEquals($expected, $actual);
    }

    public function testGetOrmError(): void
    {
        $exception = new ListQueryException('foo', 'bar');
        $this->assertEquals('foo', $exception->getOrmError());
    }

    public function testGetDql(): void
    {
        $exception = new ListQueryException('foo', 'bar');
        $this->assertEquals('bar', $exception->getDql());
    }
}