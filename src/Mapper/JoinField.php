<?php

namespace Povs\ListerBundle\Mapper;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class JoinField extends AbstractField
{
    public const JOIN_INNER = 'INNER';
    public const JOIN_LEFT = 'LEFT';

    public const OPTION_JOIN_TYPE = 'join_type';
    public const OPTION_LAZY = 'lazy';
    public const OPTION_CONDITION_TYPE = 'condition_type';
    public const OPTION_CONDITION = 'condition';
    public const OPTION_CONDITION_PARAMETERS = 'condition_parameters';

    /**
     * @var string
     */
    private $property;

    /**
     * @var JoinField|null
     */
    private $parent;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $path;

    /**
     * JoinField constructor.
     *
     * @param string         $path
     * @param string         $property
     * @param string         $alias
     * @param array          $options
     * @param JoinField|null $parent
     */
    public function __construct(string $path, string $property, string $alias, array $options, ?JoinField $parent)
    {
        $id = ($options[self::OPTION_LAZY] ?? false) ? sprintf('%s_lazy', $path) : $path;
        parent::__construct($id, $options);
        $this->path = $path;
        $this->alias = $alias;
        $this->property = $property;
        $this->parent = $parent;
    }

    /**
     * @param string|null $alias
     *
     * @return string
     */
    public function getJoinPath(?string $alias): string
    {
        $parentAlias = $this->parent ? $this->parent->getAlias() : $alias;

        if ($parentAlias) {
            return sprintf('%s.%s', $parentAlias, $this->property);
        }

        return $this->property;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            self::OPTION_JOIN_TYPE,
            self::OPTION_LAZY,
            self::OPTION_CONDITION,
            self::OPTION_CONDITION_TYPE,
            self::OPTION_CONDITION_PARAMETERS
        ]);

        $resolver->setDefaults([
            self::OPTION_JOIN_TYPE => self::JOIN_INNER,
            self::OPTION_LAZY => false,
            self::OPTION_CONDITION_TYPE => Join::WITH
        ]);

        $resolver->setAllowedValues(self::OPTION_JOIN_TYPE, [self::JOIN_INNER, self::JOIN_LEFT]);
        $resolver->setAllowedTypes(self::OPTION_LAZY, 'bool');
        $resolver->setAllowedValues(self::OPTION_CONDITION_TYPE, [Join::WITH, Join::ON]);
        $resolver->setAllowedTypes(self::OPTION_CONDITION_PARAMETERS, 'array');
    }
}
