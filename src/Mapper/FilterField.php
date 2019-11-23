<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\Type\FilterType\FilterTypeInterface;
use Povs\ListerBundle\Type\QueryType\ComparisonQueryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterField extends AbstractField
{
    public const OPTION_QUERY_TYPE = 'query_type';
    public const OPTION_QUERY_OPTIONS = 'query_options';
    public const OPTION_INPUT_TYPE = 'input_type';
    public const OPTION_INPUT_OPTIONS = 'input_options';
    public const OPTION_VALUE = 'value';
    public const OPTION_MAPPED = 'mapped';
    public const OPTION_JOIN_TYPE = 'join_type';
    public const OPTION_PATH = 'path';
    public const OPTION_PROPERTY = 'property';
    public const OPTION_REQUIRED = 'required';

    /**
     * @var FilterTypeInterface|null
     */
    private $type;

    /**
     * @var array
     */
    private $paths;

    /**
     * @param string                   $id
     * @param array                    $options
     * @param FilterTypeInterface|null $filterType
     */
    public function __construct(string $id, array $options, ?FilterTypeInterface $filterType)
    {
        parent::__construct($id, $options);
        $this->type = $filterType;
        $this->paths = $this->normalizePaths($id);
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value): self
    {
        $this->setOption(self::OPTION_VALUE, $value);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getOption(self::OPTION_VALUE);
    }

    /**
     * @return FilterTypeInterface|null
     */
    public function getType(): ?FilterTypeInterface
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            self::OPTION_QUERY_TYPE,
            self::OPTION_QUERY_OPTIONS,
            self::OPTION_INPUT_TYPE,
            self::OPTION_INPUT_OPTIONS,
            self::OPTION_VALUE,
            self::OPTION_MAPPED,
            self::OPTION_JOIN_TYPE,
            self::OPTION_PATH,
            self::OPTION_PROPERTY,
            self::OPTION_REQUIRED
        ]);

        $resolver->setDefaults([
            self::OPTION_QUERY_TYPE => ComparisonQueryType::class,
            self::OPTION_QUERY_OPTIONS => [],
            self::OPTION_INPUT_TYPE => TextType::class,
            self::OPTION_INPUT_OPTIONS => [],
            self::OPTION_MAPPED => true,
            self::OPTION_JOIN_TYPE => JoinField::JOIN_INNER,
            self::OPTION_REQUIRED => false
        ]);

        $resolver->setAllowedTypes(self::OPTION_QUERY_TYPE, 'string');
        $resolver->setAllowedTypes(self::OPTION_QUERY_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::OPTION_INPUT_TYPE, 'string');
        $resolver->setAllowedTypes(self::OPTION_INPUT_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::OPTION_MAPPED, 'bool');
        $resolver->setAllowedValues(self::OPTION_JOIN_TYPE, [JoinField::JOIN_INNER, JoinField::JOIN_LEFT]);
        $resolver->setAllowedTypes(self::OPTION_PATH, ['string', 'array']);
        $resolver->setAllowedTypes(self::OPTION_PROPERTY, ['string', 'array']);
        $resolver->setAllowedTypes(self::OPTION_REQUIRED, 'bool');
    }
}