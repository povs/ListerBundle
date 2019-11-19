<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\Mapper\FilterField;
use Povs\ListerBundle\Mapper\FilterMapper;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterBuilder
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ConfigurationResolver
     */
    private $configuration;

    /**
     * FilterBuilder constructor.
     *
     * @param FormFactoryInterface  $formFactory
     * @param ConfigurationResolver $configuration
     */
    public function __construct(FormFactoryInterface $formFactory, ConfigurationResolver $configuration)
    {
        $this->formFactory = $formFactory;
        $this->configuration = $configuration;
    }

    /**
     * @param FilterMapper $filterMapper
     *
     * @return FormInterface
     */
    public function buildFilterForm(FilterMapper $filterMapper): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder(
            $this->configuration->getRequestConfiguration('filter'),
            FormType::class,
            [],
            $this->configuration->getFormConfiguration()
        );

        $formBuilder->setMethod(Request::METHOD_GET);

        foreach ($filterMapper->getFields() as $field) {
            $defaultInputOptions = [
                'data' => $field->getValue(),
                'required' => $field->getOption(FilterField::OPTION_REQUIRED)
            ];

            $formBuilder->add(
                $field->getId(),
                $field->getOption(FilterField::OPTION_INPUT_TYPE),
                array_merge($defaultInputOptions, $field->getOption(FilterField::OPTION_INPUT_OPTIONS, []))
            );
        }

        return $formBuilder->getForm();
    }
}