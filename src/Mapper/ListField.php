<?php
namespace Povs\ListerBundle\Mapper;

use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Povs\ListerBundle\Type\SelectorType\BasicSelectorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListField extends AbstractField
{
    public const SORT_DESC = 'DESC';
    public const SORT_ASC = 'ASC';

    public const OPTION_LABEL = 'label';
    public const OPTION_SORTABLE = 'sortable';
    public const OPTION_SORT_VALUE = 'sort_value';
    public const OPTION_SORT_PATH = 'sort_path';
    public const OPTION_PATH = 'path';
    public const OPTION_JOIN_TYPE = 'join_type';
    public const OPTION_PROPERTY = 'property';
    public const OPTION_SELECTOR = 'selector';
    public const OPTION_VIEW_OPTIONS = 'view_options';
    public const OPTION_TRANSLATE = 'translate';
    public const OPTION_TRANSLATION_DOMAIN = 'translation_domain';
    public const OPTION_TRANSLATION_PREFIX = 'translation_prefix';
    public const OPTION_TRANSLATE_NULL = 'translate_null';
    public const OPTION_VALUE = 'value';
    public const OPTION_FIELD_TYPE_OPTIONS = 'field_type_options';
    public const OPTION_LAZY = 'lazy';

    /**
     * @var FieldTypeInterface
     */
    private $type;

    /**
     * @var string[]
     */
    private $paths;

    /**
     * @param string                  $id
     * @param array                   $options
     * @param FieldTypeInterface|null $type
     */
    public function __construct(string $id, array $options, ?FieldTypeInterface $type = null)
    {
        parent::__construct($id, $options);
        $this->type = $type;
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
     * @return FieldTypeInterface|null
     */
    public function getType(): ?FieldTypeInterface
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            self::OPTION_LABEL,
            self::OPTION_SORTABLE,
            self::OPTION_SORT_VALUE,
            self::OPTION_SORT_PATH,
            self::OPTION_PATH,
            self::OPTION_JOIN_TYPE,
            self::OPTION_PROPERTY,
            self::OPTION_SELECTOR,
            self::OPTION_VIEW_OPTIONS,
            self::OPTION_TRANSLATE,
            self::OPTION_TRANSLATION_DOMAIN,
            self::OPTION_TRANSLATION_PREFIX,
            self::OPTION_TRANSLATE_NULL,
            self::OPTION_VALUE,
            self::OPTION_FIELD_TYPE_OPTIONS,
            self::OPTION_LAZY
        ]);

        $resolver->setDefaults([
            self::OPTION_SORTABLE => true,
            self::OPTION_JOIN_TYPE => JoinField::JOIN_LEFT,
            self::OPTION_SELECTOR => BasicSelectorType::class,
            self::OPTION_VIEW_OPTIONS => [],
            self::OPTION_TRANSLATE => false,
            self::OPTION_TRANSLATE_NULL => false,
            self::OPTION_FIELD_TYPE_OPTIONS => [],
            self::OPTION_LAZY => false
        ]);

        $resolver->setRequired([
            self::OPTION_LABEL
        ]);

        $resolver->setAllowedTypes(self::OPTION_LABEL, ['string', 'int']);
        $resolver->setAllowedTypes(self::OPTION_SORTABLE, 'bool');
        $resolver->setAllowedValues(self::OPTION_SORT_VALUE, [self::SORT_ASC, self::SORT_DESC]);
        $resolver->setAllowedTypes(self::OPTION_SORT_PATH, 'string');
        $resolver->setAllowedTypes(self::OPTION_PATH, ['string', 'array']);
        $resolver->setAllowedValues(self::OPTION_JOIN_TYPE, [JoinField::JOIN_INNER, JoinField::JOIN_LEFT]);
        $resolver->setAllowedTypes(self::OPTION_PROPERTY, ['string', 'array']);
        $resolver->setAllowedTypes(self::OPTION_SELECTOR, 'string');
        $resolver->setAllowedTypes(self::OPTION_VIEW_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::OPTION_TRANSLATE, 'bool');
        $resolver->setAllowedTypes(self::OPTION_TRANSLATION_DOMAIN, 'string');
        $resolver->setAllowedTypes(self::OPTION_TRANSLATION_PREFIX, 'string');
        $resolver->setAllowedTypes(self::OPTION_TRANSLATE_NULL, 'bool');
        $resolver->setAllowedTypes(self::OPTION_VALUE, 'callable');
        $resolver->setAllowedTypes(self::OPTION_FIELD_TYPE_OPTIONS, 'array');
        $resolver->setAllowedTypes(self::OPTION_LAZY, 'bool');
    }
}