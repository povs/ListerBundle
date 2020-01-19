<?php

namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class JoinMapperTest extends AbstractMapperTest
{
    /**
     * @return JoinMapper
     */
    public function testAdd(): JoinMapper
    {
        $mapper = $this->getMapper([]);

        $mapper->add('entity1', 'e1');
        $mapper->add('entity1.entity2', 'e2');
        $mapper->add('e2.entity3', 'e3');
        $mapper->add('e2.entity3.entity6', 'e6');
        $mapper->add('e3.entity4.entity5', 'e5');

        $this->assertCount(6, $mapper->getFields());

        return $mapper;
    }

    /**
     * @depends testAdd
     * @param JoinMapper $mapper
     */
    public function testGetByPath(JoinMapper $mapper): void
    {
        $this->assertEquals('e5', $mapper->getByPath('entity1.entity2.entity3.entity4.entity5')->getAlias());
        $this->assertEquals('e5', $mapper->getByPath('e5')->getAlias());
        $this->assertEquals('e3', $mapper->getByPath('entity1.entity2.entity3')->getAlias());
    }

    /**
     * @return JoinMapper
     */
    public function testBuildListFields(): JoinMapper
    {
        $paths = [
            [['prop'], false],
            [['entity1.prop'], true],
            [['entity1.prop2'], false],
            [['entity1.entity2.prop', 'entity1.entity2.prop2'], true],
            [['entity3.entity4.entity5.prop', 'entity3.prop'], false]
        ];
        $fields = [];

        foreach ($paths as $path) {
            $field = $this->createMock(ListField::class);
            $field->expects($this->exactly(5))
                ->method('getOption')
                ->willReturnMap([
                    ['join_type', null, 'INNER'],
                    ['sortable', null, true],
                    ['sort_value', null, 'ASC'],
                    ['sort_path', null, 'custom_path'],
                    ['lazy', null, $path[1]]
                ]);
            $field->expects($this->once())
                ->method('getPaths')
                ->willReturn($path[0]);

            $fields[] = $field;
        }

        $listMapperMock = $this->createMock(ListMapper::class);
        $listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));

        $filterMapperMock = $this->createMock(FilterMapper::class);
        $filterMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection([]));

        $mapper = new JoinMapper($listMapperMock, $filterMapperMock);
        $mapper->build();

        $this->assertCount(6, $mapper->getFields());
        $this->assertCount(2, $mapper->getFields(true));
        $this->assertCount(4, $mapper->getFields(false));
        $this->assertEquals('a.entity1', $mapper->getByPath('entity1', true)->getJoinPath('a'));
        $this->assertEquals('a.entity1', $mapper->getByPath('entity1', false)->getJoinPath('a'));
        $this->assertEquals('entity1_a.entity2', $mapper->getByPath('entity1.entity2', true)->getJoinPath('a'));
        $this->assertEquals('a.entity3', $mapper->getByPath('entity3', false)->getJoinPath('a'));
        $this->assertEquals('entity3_a.entity4', $mapper->getByPath('entity3.entity4', false)->getJoinPath('a'));
        $this->assertEquals('entity3_entity4_a.entity5', $mapper->getByPath('entity3.entity4.entity5', false)->getJoinPath('a'));

        return $mapper;
    }

    /**
     * @depends testBuildListFields
     * @param JoinMapper $mapper
     */
    public function testAliasOverwrite(JoinMapper $mapper): void
    {
        $mapper->add('entity3.entity4.entity5', 'custom_a', []);
        $this->assertCount(6, $mapper->getFields());
        $this->assertCount(2, $mapper->getFields(true));
        $this->assertCount(4, $mapper->getFields(false));
        $this->assertEquals('custom_a', $mapper->getByPath('entity3.entity4.entity5')->getAlias());
    }

    public function testBuildFilterFields(): void
    {
        $paths = [
            ['prop'],
            ['entity1.prop'],
            ['entity1.entity2.prop', 'entity1.entity2.prop2'],
            ['entity3.entity4.entity5.prop', 'entity3.prop']
        ];
        $fields = [];

        foreach ($paths as $path) {
            $field = $this->createMock(FilterField::class);
            $field->expects($this->exactly(2))
                ->method('getOption')
                ->willReturnMap([
                    ['join_type', null, 'INNER'],
                    ['mapped', null, true],
                ]);
            $field->expects($this->once())
                ->method('getPaths')
                ->willReturn($path);
            $field->expects($this->once())
                ->method('hasValue')
                ->willReturn(true);

            $fields[] = $field;
        }

        $listMapperMock = $this->createMock(ListMapper::class);
        $listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection());

        $filterMapperMock = $this->createMock(FilterMapper::class);
        $filterMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));

        $mapper = new JoinMapper($listMapperMock, $filterMapperMock);
        $mapper->build();

        $this->assertCount(5, $mapper->getFields());
        $this->assertEquals('a.entity1', $mapper->getByPath('entity1')->getJoinPath('a'));
        $this->assertEquals('entity1_a.entity2', $mapper->getByPath('entity1.entity2')->getJoinPath('a'));
        $this->assertEquals('a.entity3', $mapper->getByPath('entity3')->getJoinPath('a'));
        $this->assertEquals('entity3_a.entity4', $mapper->getByPath('entity3.entity4')->getJoinPath('a'));
        $this->assertEquals('entity3_entity4_a.entity5', $mapper->getByPath('entity3.entity4.entity5')->getJoinPath('a'));
    }

    /**
     * @param array $ids
     *
     * @return AbstractMapper|JoinMapper
     */
    protected function getMapper(array $ids): AbstractMapper
    {
        $listMapper = $this->createMock(ListMapper::class);
        $filterMapper = $this->createMock(FilterMapper::class);
        $mapper = new JoinMapper($listMapper, $filterMapper);

        foreach ($ids as $id) {
            $mapper->add($id, 'alias', []);
        }

        return $mapper;
    }
}
