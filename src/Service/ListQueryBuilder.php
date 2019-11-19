<?php
namespace Povs\ListerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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
use Povs\ListerBundle\Type\SelectorType\BasicSelectorType;
use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListQueryBuilder
{
    private const SELECT_PREFIX = 'field_';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var QueryTypeLocator
     */
    private $queryTypeLocator;

    /**
     * @var SelectorTypeLocator
     */
    private $selectorTypeLocator;

    /**
     * @var ConfigurationResolver
     */
    private $configuration;

    /**
     * ListQueryBuilder constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param QueryTypeLocator       $queryTypeLocator
     * @param SelectorTypeLocator    $selectorTypeLocator
     * @param ConfigurationResolver  $configuration
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        QueryTypeLocator $queryTypeLocator,
        SelectorTypeLocator $selectorTypeLocator,
        ConfigurationResolver $configuration
    ) {
        $this->em = $entityManager;
        $this->queryTypeLocator = $queryTypeLocator;
        $this->selectorTypeLocator = $selectorTypeLocator;
        $this->configuration = $configuration;
    }

    /**
     * @param ListField $field
     *
     * @return string
     */
    public static function getFieldAlias(ListField $field): string
    {
        return sprintf('%s%s', self::SELECT_PREFIX, $field->getId());
    }

    /**
     * @param ListInterface   $list
     * @param JoinMapper      $joinMapper
     * @param ListMapper      $listMapper
     * @param FilterMapper    $filterMapper
     * @param ListValueInterface $listValue
     *
     * @return QueryBuilder
     */
    public function buildQuery(
        ListInterface $list,
        JoinMapper $joinMapper,
        ListMapper $listMapper,
        FilterMapper $filterMapper,
        ListValueInterface $listValue
    ) :QueryBuilder {
        $this->queryBuilder = $this->em->createQueryBuilder()
            ->from($list->getDataClass(), $this->configuration->getAlias());

        $this->applyJoins($joinMapper);
        $this->applySelects($listMapper, $joinMapper);
        $this->applyFilter($filterMapper, $joinMapper);
        $this->applyGroup();
        $list->configureQuery($this->queryBuilder, $listValue);

        return $this->queryBuilder;
    }

    /**
     * Adds join dql parts.
     *
     * @param JoinMapper $joinMapper
     */
    private function applyJoins(JoinMapper $joinMapper): void
    {
        foreach ($joinMapper->getFields() as $field) {
            $joinPath = $field->getJoinPath($this->configuration->getAlias());

            if ($field->getOption(JoinField::OPTION_JOIN_TYPE) === JoinField::JOIN_INNER) {
                $this->queryBuilder->innerJoin($joinPath, $field->getAlias());
            } else {
                $this->queryBuilder->leftJoin($joinPath, $field->getAlias());
            }
        }
    }

    /**
     * Adds select and sort DQL parts
     *
     * @param ListMapper $listMapper
     * @param JoinMapper $joinMapper
     */
    private function applySelects(ListMapper $listMapper, JoinMapper $joinMapper): void
    {
        foreach ($listMapper->getFields() as $field) {
            $paths = $this->parsePaths($joinMapper, $field->getPaths());
            $alias = self::getFieldAlias($field);
            $selectorType = $field->getOption(ListField::OPTION_SELECTOR);

            if (!$this->selectorTypeLocator->has($selectorType)) {
                throw ListFieldException::invalidType($field->getId(), $selectorType, SelectorTypeInterface::class);
            }

            $statement =  $this->selectorTypeLocator->get($selectorType)->getStatement($paths);
            $this->queryBuilder->addSelect(sprintf('%s as %s', $statement, $alias));

            if ($field->getOption(ListField::OPTION_SORTABLE) &&
                ($dir = $field->getOption(ListField::OPTION_SORT_VALUE))
            ) {
                if ($sortPath = $field->getOption(ListField::OPTION_SORT_PATH)) {
                    $select = $this->parsePaths($joinMapper, (array) $sortPath)[0];
                } else {
                    $select = $alias;
                }

                $this->queryBuilder->addOrderBy($select, $dir);
            }
        }
    }

    /**
     * @param FilterMapper $filterMapper
     * @param JoinMapper   $joinMapper
     */
    private function applyFilter(FilterMapper $filterMapper, JoinMapper $joinMapper): void
    {
        foreach ($filterMapper->getFields() as $field) {
            if (!$field->getValue()) {
                continue;
            }

            /** @var BasicSelectorType $selector */
            $selector = $this->selectorTypeLocator->get(BasicSelectorType::class);
            $queryType = $field->getOption(FilterField::OPTION_QUERY_TYPE);
            $paths =  $this->parsePaths($joinMapper, $field->getPaths());
            $statement = $selector->getStatement($paths, $field->getOption(FilterField::OPTION_PROPERTY_DELIMITER));

            if (!$this->queryTypeLocator->has($queryType)) {
                throw ListFieldException::invalidType($field->getId(), $queryType, QueryTypeInterface::class);
            }

            $queryType = $this->queryTypeLocator->get($queryType);
            $resolver = new OptionsResolver();
            $queryType->configureOptions($resolver);
            $queryType->setOptions($resolver->resolve($field->getOption(FilterField::OPTION_QUERY_OPTIONS)));
            $queryType->setPaths($paths);
            $queryType->setPath($statement);
            $queryType->filter($this->queryBuilder, $field->getId(), $field->getValue());
        }
    }

    /**
     * Applies group by identifier on the base entity for one->many and many->many relations
     * Also for functions like count, group select etc.
     */
    private function applyGroup(): void
    {
        $statement = sprintf('%s.%s', $this->configuration->getAlias(), $this->configuration->getIdentifier());

        $this->queryBuilder->groupBy($statement);
    }

    /**
     * @param JoinMapper $joinMapper
     * @param array      $paths
     *
     * @return array
     */
    private function parsePaths(JoinMapper $joinMapper, array $paths): array
    {
        $parsedPaths = [];

        foreach ($paths as $path) {
            $pathElements = explode('.', $path);

            if (count($pathElements) === 1) {
                $prop = $pathElements[0];
                $path = null;
            } else {
                $prop = array_pop($pathElements);
                $path = implode('.', $pathElements);
            }

            if ($path) {
                if (!$joinField = $joinMapper->getByPath($path)) {
                    throw ListFieldException::invalidPath($path);
                }

                $alias = $joinField->getAlias();
            } else {
                $alias = $this->configuration->getAlias();
            }

            $parsedPaths[] = sprintf('%s.%s', $alias, $prop);
        }

        return $parsedPaths;
    }
}