<?php
namespace Povs\ListerBundle\Mapper;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class JoinField extends AbstractField
{
    public const JOIN_INNER = 'INNER';
    public const JOIN_LEFT = 'LEFT';

    public const OPTION_JOIN_TYPE = 'join_type';

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
        parent::__construct($path, $options);
        $this->path = $path;
        $this->alias = $alias;
        $this->property = $property;
        $this->parent = $parent;
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    public function getJoinPath(string $alias): string
    {
        $parentAlias = $this->parent ? $this->parent->getAlias() : $alias;

        return sprintf('%s.%s', $parentAlias, $this->property);
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
        $resolver->setDefined([self::OPTION_JOIN_TYPE]);

        $resolver->setDefaults([
            self::OPTION_JOIN_TYPE => self::JOIN_INNER,
        ]);
        
        $resolver->setAllowedValues(self::OPTION_JOIN_TYPE, [self::JOIN_INNER, self::JOIN_LEFT]);
    }
}