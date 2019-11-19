<?php
namespace Povs\ListerBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Povs\ListerBundle\DependencyInjection\Locator\FieldTypeLocator;
use Povs\ListerBundle\Exception\ListException;

/**
 * @property ListField[] $fields
 * @method ListField get(string $id)
 * @method ListField[]|ArrayCollection getFields()
 *
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListMapper extends AbstractMapper
{
    /**
     * @var FieldTypeLocator
     */
    private $fieldTypeLocator;

    /**
     * @var string list type
     */
    private $type;

    /**
     * @var ListMapper|null
     */
    private $listMapper;

    /**
     * ListMapper constructor.
     *
     * @param FieldTypeLocator $fieldTypeLocator
     * @param string           $listType
     * @param ListMapper|null  $listMapper
     */
    public function __construct(FieldTypeLocator $fieldTypeLocator, string $listType, ?ListMapper $listMapper = null)
    {
        parent::__construct();
        $this->fieldTypeLocator = $fieldTypeLocator;
        $this->type = $listType;
        $this->listMapper = $listMapper;
    }

    /**
     * @param string      $id        field id
     * @param string|null $fieldType field type
     * @param array       $options
     *
     * @return $this
     */
    public function add(string $id, ?string $fieldType = null, ?array $options = []): self
    {
        if (null !== $fieldType) {
            $fieldType = $this->fieldTypeLocator->get($fieldType);
            $options = array_merge($fieldType->getDefaultOptions($this->type), $options);
        }

        $listField = new ListField($id, $options, $fieldType);
        $this->addField($listField);

        return $this;
    }

    /**
     * @return ListMapper
     */
    public function build(): self
    {
        if (!$this->listMapper) {
            throw ListException::listNotConfigured();
        }

        $this->fields = $this->listMapper->getFields();

        return $this;
    }
}