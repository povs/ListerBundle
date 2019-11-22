<?php
namespace Povs\ListerBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Definition\ListInterface;
use Povs\ListerBundle\Definition\ListValueInterface;
use Povs\ListerBundle\DependencyInjection\Locator\QueryTypeLocator;
use Povs\ListerBundle\DependencyInjection\Locator\SelectorTypeLocator;
use Povs\ListerBundle\Exception\ListFieldException;
use Povs\ListerBundle\Mapper\FilterField;
use Povs\ListerBundle\Mapper\FilterMapper;
use Povs\ListerBundle\Mapper\JoinField;
use Povs\ListerBundle\Mapper\JoinMapper;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mapper\ListMapper;
use Povs\ListerBundle\Type\QueryType\QueryTypeInterface;
use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListQueryBuilderTest extends TestCase
{
    private $emMock;
    private $queryTypeLocatorMock;
    private $selectorTypeLocatorMock;
    private $selectorTypeMock;
    private $configMock;
    private $listMock;
    private $joinMapperMock;
    private $listMapperMock;
    private $filterMapperMock;
    private $listValueMock;
    private $queryBuilderMock;

    public function setUp()
    {
        $this->emMock = $this->createMock(EntityManagerInterface::class);
        $this->queryTypeLocatorMock = $this->createMock(QueryTypeLocator::class);
        $this->selectorTypeLocatorMock = $this->createMock(SelectorTypeLocator::class);
        $this->selectorTypeMock = $this->createMock(SelectorTypeInterface::class);
        $this->configMock = $this->createMock(ConfigurationResolver::class);
        $this->listMock = $this->createMock(ListInterface::class);
        $this->joinMapperMock = $this->createMock(JoinMapper::class);
        $this->listMapperMock = $this->createMock(ListMapper::class);
        $this->filterMapperMock = $this->createMock(FilterMapper::class);
        $this->listValueMock = $this->createMock(ListValueInterface::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
    }

    public function testBuildQueryJoins(): void
    {
        $fieldsData = [
            ['ent1', 'INNER', 'al1'],
            ['al1.ent2', 'LEFT', 'al2'],
        ];
        $fields = [];

        foreach ($fieldsData as $datum) {
            $field = $this->createMock(JoinField::class);
            $field->expects($this->once())
                ->method('getJoinPath')
                ->with('alias')
                ->willReturn($datum[0]);
            $field->expects($this->once())
                ->method('getOption')
                ->with('join_type')
                ->willReturn($datum[1]);
            $field->expects($this->once())
                ->method('getAlias')
                ->willReturn($datum[2]);
            $fields[] = $field;
        }

        $this->setCalls(false, true, true);
        $this->joinMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));
        $this->queryBuilderMock->expects($this->once())
            ->method('innerJoin')
            ->with('ent1', 'al1');
        $this->queryBuilderMock->expects($this->once())
            ->method('leftJoin')
            ->with('al1.ent2', 'al2');

        $this->executeBuildQuery();
    }

    public function testBuildQueryApplySelects(): void
    {
        $fieldsData = [
            ['id1', ['foo'], 'selector', true, 'ASC', 'sort.path'],
            ['id2', ['ent1.bar'], 'selector', true, 'DESC', null],
        ];
        $fields = [];

        foreach ($fieldsData as $datum) {
            $field = $this->createMock(ListField::class);
            $field->method('getId')
                ->willReturn($datum[0]);
            $field->expects($this->once())
                ->method('getPaths')
                ->willReturn($datum[1]);
            $field->method('getOption')
                ->willReturnMap([
                   ['selector', null, $datum[2]],
                   ['sortable', null, $datum[3]],
                   ['sort_value', null, $datum[4]],
                   ['sort_path', null, $datum[5]]
                ]);
            $fields[] = $field;
        }

        $this->setCalls(true, false, true);
        $this->listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));
        $this->selectorTypeLocatorMock->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);
        $this->selectorTypeLocatorMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturn($this->selectorTypeMock);
        $joinFieldMock = $this->createMock(JoinField::class);
        $joinFieldMock2 = $this->createMock(JoinField::class);
        $joinFieldMock->expects($this->once())
            ->method('getAlias')
            ->willReturn('ent1');
        $joinFieldMock2->expects($this->once())
            ->method('getAlias')
            ->willReturn('sort');

        $this->joinMapperMock->expects($this->exactly(2))
            ->method('getByPath')
            ->willReturnMap([
                ['ent1', $joinFieldMock],
                ['sort', $joinFieldMock2]
            ]);
        $this->selectorTypeMock
            ->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [$this->queryBuilderMock, ['alias.foo'], 'id1'],
                [$this->queryBuilderMock, ['ent1.bar'], 'id2']
            );

        $this->selectorTypeMock->expects($this->once())
            ->method('getSortPath')
            ->with('id2')
            ->willReturn('field_id2');

        $this->queryBuilderMock->expects($this->exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(
                ['sort.path', 'ASC'],
                ['field_id2', 'DESC']
            )->willReturnSelf();

        $this->executeBuildQuery();
    }

    public function testBuildQueryApplyFilter(): void
    {
        $fieldsData = [
            ['id1', ['foo'], 'value1'],
            ['id2', ['ent1.bar'],'value2'],
            ['id3', ['ent2.bar'], null],
        ];
        $fields = [];

        foreach ($fieldsData as $datum) {
            $field = $this->createMock(FilterField::class);
            if ($datum[2]) {
                $field->expects($this->exactly(2))
                    ->method('getValue')
                    ->willReturn($datum[2]);
                $field->expects($this->once())
                    ->method('getId')
                    ->willReturn($datum[0]);
                $field->expects($this->once())
                    ->method('getPaths')
                    ->willReturn($datum[1]);
                $field->expects($this->exactly(2))
                    ->method('getOption')
                    ->willReturnMap([
                        ['query_type', null, 'query_type'],
                        ['property_delimiter', null, 'delimiter'],
                        ['query_options', null, []]
                    ]);
            } else {
                $field->expects($this->once())
                    ->method('getValue')
                    ->willReturn($datum[2]);
            }

            $fields[] = $field;
        }

        $this->setCalls(true, true, false);
        $this->filterMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));
        $joinFieldMock = $this->createMock(JoinField::class);
        $joinFieldMock->expects($this->once())
            ->method('getAlias')
            ->willReturn('ent1');
        $this->joinMapperMock->expects($this->once())
            ->method('getByPath')
            ->willReturn($joinFieldMock);
        $queryTypeMock = $this->createMock(QueryTypeInterface::class);
        $this->queryTypeLocatorMock->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);
        $this->queryTypeLocatorMock->expects($this->exactly(2))
            ->method('get')
            ->willReturn($queryTypeMock);

        $queryTypeMock->expects($this->exactly(2))
            ->method('configureOptions');
        $queryTypeMock->expects($this->exactly(2))
            ->method('setOptions')
            ->with([]);
        $queryTypeMock->expects($this->exactly(2))
            ->method('setPaths')
            ->withConsecutive(
                [['alias.foo']],
                [['ent1.bar']]
            );
        $queryTypeMock->expects($this->exactly(2))
            ->method('setPath')
            ->withConsecutive(
                ['alias.foo'],
                ['ent1.bar']
            );
        $queryTypeMock->expects($this->exactly(2))
            ->method('filter')
            ->withConsecutive(
                [$this->queryBuilderMock, 'id1', 'value1'],
                [$this->queryBuilderMock, 'id2', 'value2']
            );

        $this->executeBuildQuery();
    }

    public function testBuildQueryApplyGroup(): void
    {
        $this->configMock->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('foo');

        $this->queryBuilderMock->expects($this->once())
            ->method('groupBy')
            ->with('alias.foo');

        $this->setCalls(true, true, true);
        $this->executeBuildQuery();
    }

    public function testApplySelectsThrowsExceptionOnInvalidSelectorType(): void
    {
        $this->expectException(ListFieldException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage(sprintf('Type "invalid_type" does not exist or does not implement %s', SelectorTypeInterface::class));
        $listFieldMock = $this->createMock(ListField::class);
        $listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $listFieldMock->expects($this->once())
            ->method('getOption')
            ->willReturn('invalid_type');
        $this->setCalls(true, false, false);
        $this->selectorTypeLocatorMock->method('has')
            ->with('invalid_type')
            ->willReturn(false);
        $this->listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection([$listFieldMock]));
        $this->executeBuildQuery();
    }

    public function testApplyFilterThrowsExceptionOnInvalidQueryType(): void
    {
        $this->expectException(ListFieldException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage(sprintf('Type "invalid_type" does not exist or does not implement %s', QueryTypeInterface::class));
        $filterFieldMock = $this->createMock(FilterField::class);
        $filterFieldMock->expects($this->once())
            ->method('getOption')
            ->willReturnMap([
                ['query_type', null, 'invalid_type'],
            ]);
        $filterFieldMock->expects($this->once())
            ->method('getValue')
            ->willReturn('val');
        $this->setCalls(true, true, false);
        $this->selectorTypeLocatorMock->method('has')
            ->with('invalid_type')
            ->willReturn(false);
        $this->filterMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection([$filterFieldMock]));
        $this->executeBuildQuery();
    }

    public function testExceptionIsThrownOnInvalidPath(): void
    {
        $this->expectException(ListFieldException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Could not find join for path "foo"');
        $listFieldMock = $this->createMock(ListField::class);
        $listFieldMock->expects($this->once())
            ->method('getPaths')
            ->willReturn(['foo.bar']);
        $this->listMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection([$listFieldMock]));
        $this->joinMapperMock->expects($this->once())
            ->method('getByPath')
            ->willReturn(null);
        $this->setCalls(true, false, false);
        $this->executeBuildQuery();
    }

    private function executeBuildQuery(): void
    {
        $queryBuilder = new ListQueryBuilder(
            $this->emMock,
            $this->queryTypeLocatorMock,
            $this->selectorTypeLocatorMock,
            $this->configMock
        );

        $queryBuilder->buildQuery(
            $this->listMock,
            $this->joinMapperMock,
            $this->listMapperMock,
            $this->filterMapperMock,
            $this->listValueMock
        );
    }

    private function setCalls(bool $setJoinFields, bool $setListFields, bool $setFilterFields): void
    {
        $this->emMock->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);
        $this->queryBuilderMock->expects($this->once())
            ->method('from')
            ->with('data_class', 'alias')
            ->willReturnSelf();
        $this->listMock->expects($this->once())
            ->method('getDataClass')
            ->willReturn('data_class');
        $this->configMock->method('getAlias')
            ->willReturn('alias');

        if ($setJoinFields) {
            $this->joinMapperMock->expects($this->once())
                ->method('getFields')
                ->willReturn(new ArrayCollection());
        }

        if ($setListFields) {
            $this->listMapperMock->expects($this->once())
                ->method('getFields')
                ->willReturn(new ArrayCollection());
        }

        if ($setFilterFields) {
            $this->filterMapperMock->expects($this->once())
                ->method('getFields')
                ->willReturn(new ArrayCollection());
        }
    }
}