<?php
namespace Povs\ListerBundle\Mapper;

use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Exception\ListFieldException;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
abstract class AbstractMapperTest extends TestCase
{
    public function testHas(): void
    {
        $mapper = $this->getMapper(['id']);
        $this->assertTrue($mapper->has('id'));
        $this->assertFalse($mapper->has('another_id'));
    }

    public function testGetExists(): void
    {
        $mapper = $this->getMapper(['id']);
        $this->assertEquals('id', $mapper->get('id')->getId());
    }

    public function testGetThrowsExceptionOnNonExist(): void
    {
        $this->expectException(ListFieldException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Field "id" do not exists');
        $mapper = $this->getMapper(['another_id']);
        $mapper->get('id');
    }

    public function testRemove(): void
    {
        $mapper = $this->getMapper(['id']);
        $mapper->remove('other_id');
        $this->assertTrue($mapper->has('id'));
        $mapper->remove('id');
        $this->assertFalse($mapper->has('id'));
    }

    public function testGetFields(): void
    {
        $mapper = $this->getMapper(['id', 'id2', 'id3']);
        $this->assertCount(3, $mapper->getFields());
    }

    /**
     * @param string[] $ids
     *
     * @return AbstractMapper
     */
    abstract protected function getMapper(array $ids): AbstractMapper;
}