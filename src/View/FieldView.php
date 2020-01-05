<?php
namespace Povs\ListerBundle\View;

use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Mixin\OptionsTrait;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FieldView implements ViewInterface
{
    use OptionsTrait;

    /**
     * @var ListField
     */
    private $listField;

    /**
     * @var RowView
     */
    private $rowView;

    /**
     * @var mixed
     */
    private $value;

    /**
     * FieldView constructor.
     *
     * @param ListField $listField
     */
    public function __construct(ListField $listField)
    {
        $this->listField = $listField;
        $this->initOptions($listField->getOption(ListField::OPTION_VIEW_OPTIONS, []));
    }

    /**
     * @return RowView
     */
    public function getRow(): RowView
    {
        return $this->rowView;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->listField->getId();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->listField->getOption(ListField::OPTION_SORTABLE);
    }

    /**
     * @return string|null DESC|ASC
     */
    public function getSort(): ?string
    {
        return $this->listField->getOption(ListField::OPTION_SORT_VALUE);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->listField->getOption(ListField::OPTION_LABEL);
    }

    /**
     * @internal
     * @return ListField
     */
    public function getListField(): ListField
    {
        return $this->listField;
    }

    /**
     * @internal
     * @param RowView $rowView
     * @param mixed   $value
     */
    public function init(RowView $rowView, $value): void
    {
        $this->rowView = $rowView;
        $this->value = $value;
    }
}