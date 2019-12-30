<?php
namespace Povs\ListerBundle\View;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Service\ListQueryBuilder;
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
     * Query builder to fetch lazy loadable data
     *
     * @var QueryBuilder|null
     */
    private $queryBuilder;

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
     * @var int|null
     */
    private $id;

    /**
     * RowView constructor.
     *
     * @param ValueAccessor     $valueAccessor
     * @param QueryBuilder|null $queryBuilder
     */
    public function __construct(ValueAccessor $valueAccessor, ?QueryBuilder $queryBuilder)
    {
        $this->valueAccessor = $valueAccessor;
        $this->queryBuilder = $queryBuilder;
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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

        if (self::TYPE_BODY === $type) {
            $this->id = (int) $data[ListQueryBuilder::IDENTIFIER_ALIAS];
            $data = $this->valueAccessor->normalizeData($data, $this->queryBuilder);
        }

        foreach ($this->listView->getFieldViews() as $fieldView) {
            if ($type === self::TYPE_BODY) {
                $value = $this->valueAccessor->getFieldValue($fieldView, $data);
            } else {
                $value = $this->valueAccessor->getHeaderValue($fieldView);
                $fieldView->getListField()->setOption(ListField::OPTION_LABEL, $value);
            }

            $fieldView->init($this, $value);
            $this->fields[] = $fieldView;
            $this->value[$fieldView->getListField()->getId()] = $value;
        }
    }
}