<?php
namespace Povs\ListerBundle\View;

use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Service\ValueAccessor;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class RowView implements ViewInterface
{
    public const TYPE_BODY = 'body';
    public const TYPE_HEADER = 'header';

    /**
     * @var ValueAccessor
     */
    private $valueAccessor;

    /**
     * @var ListView
     */
    private $listView;

    /**
     * @var array row value
     */
    private $value = [];

    /**
     * @var FieldView[]
     */
    private $fields = [];

    /**
     * RowView constructor.
     *
     * @param ValueAccessor $valueAccessor
     */
    public function __construct(ValueAccessor $valueAccessor)
    {
        $this->valueAccessor = $valueAccessor;
    }

    /**
     * @return FieldView[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @return array ['label' => value][]
     */
    public function getLabeledValue(): array
    {
        $value = [];

        foreach ($this->fields as $field) {
            $value[$field->getLabel()] = $field->getValue();
        }

        return $value;
    }

    /**
     * @return ListView
     */
    public function getList(): ListView
    {
        return $this->listView;
    }

    /**
     * @internal
     *
     * @param ListView   $listView
     * @param string     $type
     * @param array|null $data
     */
    public function init(ListView $listView, string $type, ?array $data): void
    {
        $this->listView = $listView;
        $this->fields = [];
        $this->value = [];

        foreach ($this->listView->getFieldViews() as $fieldView) {
            if ($type === self::TYPE_BODY) {
                $value = $this->valueAccessor->getFieldValue($fieldView, $data);
            } else {
                $value = $this->valueAccessor->getHeaderValue($fieldView);
                $fieldView->getListField()->setOption(ListField::OPTION_LABEL, $value);
            }

            $fieldView->init($this, $value);
            $this->fields[] = $fieldView;
            $this->value[] = $value;
        }
    }
}