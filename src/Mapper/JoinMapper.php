<?php
namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 *
 * @property JoinField[]|ArrayCollection $fields
 * @method JoinField get(string $id)
 */
class JoinMapper extends AbstractMapper
{
    /**
     * @var ListMapper
     */
    private $listMapper;

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    /**
     * JoinMapper constructor.
     *
     * @param ListMapper   $listMapper   fully built list mapper
     * @param FilterMapper $filterMapper fully build filter mapper
     */
    public function __construct(
        ListMapper $listMapper,
        FilterMapper $filterMapper
    ) {
        parent::__construct();
        $this->listMapper = $listMapper;
        $this->filterMapper = $filterMapper;
    }

    /**
     * @param string $path   ORM path to join
     * @param string $alias  join as alias
     * @param array $options
     *
     * @return JoinMapper
     */
    public function add(string $path, string $alias, array $options = []): JoinMapper
    {
        $this->addJoin($path, $options, $alias);

        return $this;
    }

    /**
     * @param string $path
     * @param bool   $lazy
     *
     * @return JoinField|null
     */
    public function getByPath(string $path, bool $lazy = false): ?JoinField
    {
        $field = $this->fields->filter(static function(JoinField $field) use ($path, $lazy) {
            return ($field->getAlias() === $path || $field->getPath() === $path) &&
                $field->getOption(JoinField::OPTION_LAZY) === $lazy;
        })->first();

        return $field ?: null;
    }

    /**
     * @param bool|null $lazy
     *
     * @return ArrayCollection
     */
    public function getFields(?bool $lazy = null): ArrayCollection
    {
        if (null === $lazy) {
            return $this->fields;
        }

        return $this->fields->filter(static function (JoinField $joinField) use ($lazy) {
            return $joinField->getOption(JoinField::OPTION_LAZY) === $lazy;
        });
    }

    /**
     * @return JoinMapper
     */
    public function build(): self
    {
        $this->buildListJoins();
        $this->buildFilterJoins();

        return $this;
    }

    private function buildListJoins(): void
    {
        foreach ($this->listMapper->getFields() as $field) {
            $paths = $field->getPaths();
            $joinType = $field->getOption(ListField::OPTION_JOIN_TYPE);
            $lazy = $field->getOption(ListField::OPTION_LAZY);

            $this->buildJoins($paths, $joinType, $lazy);

            if ($field->getOption(ListField::OPTION_SORTABLE) &&
                $field->getOption(ListField::OPTION_SORT_VALUE)
            ) {
                if ($sortPath = $field->getOption(ListField::OPTION_SORT_PATH)) {
                    $paths = (array) $sortPath;
                }

                $this->buildJoins($paths, $joinType, false);
            }
        }
    }

    private function buildFilterJoins(): void
    {
        foreach ($this->filterMapper->getFields() as $field) {
            if ($field->hasValue()) {
                $paths = $field->getPaths();
                $joinType = $field->getOption(FilterField::OPTION_JOIN_TYPE);
                $mapped = $field->getOption(FilterField::OPTION_MAPPED);

                if ($paths && $joinType && $mapped) {
                    $this->buildJoins($paths, $joinType, false);
                }
            }
        }
    }

    /**
     * @param array  $paths
     * @param string $joinType
     * @param bool   $lazy
     */
    private function buildJoins(array $paths, string $joinType, bool $lazy): void
    {
        foreach ($paths as $path) {
            if (!$path = $this->getPath($path)) {
                continue;
            }

            $options = [
                JoinField::OPTION_JOIN_TYPE => $joinType,
                JoinField::OPTION_LAZY => $lazy
            ];

            $this->addJoin($path, $options, null);
        }
    }

    /**
     * @param string      $path         join path
     * @param array       $options      JoinField options
     * @param string|null $alias        join alias. If null - alias will be auto generated by replacing "." with "_"
     *                                  For example path = entity.parentEntity => alias = entity_parentEntity
     *
     * @return JoinField|null if nothing was joined
     */
    private function addJoin(string $path, array $options, ?string $alias = null): ?JoinField
    {
        if ($joinField = $this->getByPath($path, $options[JoinField::OPTION_LAZY] ?? false)) {
            if ($alias) {
                $joinField->setAlias($alias);
            }

            return $joinField;
        }

        $pathElements = explode('.', $path);
        $pathCount = count($pathElements);
        $prop = array_pop($pathElements);

        if ($pathCount > 1) {
            $parent = $this->addJoin(implode('.', $pathElements), $options, null);
        } else {
            $parent = null;
        }

        $path = $parent ? sprintf('%s.%s', $parent->getPath(), $prop) : $prop;

        if (!$alias)  {
            $alias = str_replace('.', '_', $path);
        }

        $joinField = new JoinField($path, $prop, $alias, $options, $parent);
        $this->addField($joinField);

        return $joinField;
    }

    /**
     * Removes last element (attribute) from path string
     * For example order.user.name => order.user
     *
     * @param string $fullPath
     *
     * @return string|null
     */
    private function getPath(string $fullPath): ?string
    {
        $pathElements = explode('.', $fullPath);

        if (count($pathElements) === 1) {
            return null;
        }

        array_pop($pathElements);

        return implode('.', $pathElements);
    }
}