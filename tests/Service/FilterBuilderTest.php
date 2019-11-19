<?php

namespace Povs\ListerBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\Mapper\FilterField;
use Povs\ListerBundle\Mapper\FilterMapper;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class FilterBuilderTest extends TestCase
{
    public function testBuildFilterForm(): void
    {
        $filterMapperMock = $this->getFilterMapperMock();
        $formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $configMock = $this->createMock(ConfigurationResolver::class);
        $formBuilderMock = $this->createMock(FormBuilderInterface::class);
        $formMock = $this->createMock(FormInterface::class);

        $formFactoryMock->expects($this->once())
            ->method('createNamedBuilder')
            ->with(
                'filter_name',
                FormType::class,
                [],
                ['param1' => 'value1', 'param2' => 'value2']
            )
            ->willReturn($formBuilderMock);

        $configMock->expects($this->once())
            ->method('getRequestConfiguration')
            ->with('filter')
            ->willReturn('filter_name');
        $configMock->expects($this->once())
            ->method('getFormConfiguration')
            ->willReturn(['param1' => 'value1', 'param2' => 'value2']);

        $formBuilderMock->expects($this->once())
            ->method('setMethod')
            ->with(Request::METHOD_GET);
        $formBuilderMock->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                ['id1', 'input_type', ['data' => 'value1', 'required' => false, 'option1' => 'foo', 'option2' => 'bar']],
                ['id2', 'input_type', ['data' => 'value2', 'required' => true]],
                ['id3', 'another_type', ['data' => 'changed_value3', 'required' => true, 'option1' => 'foo']]
            )->willReturnSelf();
        $formBuilderMock->expects($this->once())
            ->method('getForm')
            ->willReturn($formMock);

        $listFilterBuilder = new FilterBuilder($formFactoryMock, $configMock);
        $form = $listFilterBuilder->buildFilterForm($filterMapperMock);

        $this->assertEquals($formMock, $form);
    }

    /**
     * @return MockObject|FilterMapper
     */
    private function getFilterMapperMock(): MockObject
    {
        $fieldsData = [
            ['id1', false, 'value1', 'input_type', ['option1' => 'foo', 'option2' => 'bar']],
            ['id2', true, 'value2', 'input_type', []],
            ['id3', false, 'value3', 'another_type', ['option1' => 'foo', 'required' => true, 'data' => 'changed_value3']]
        ];
        $fields = [];

        foreach ($fieldsData as $fieldsDatum) {
            $field = $this->createMock(FilterField::class);
            $field->expects($this->once())
                ->method('getValue')
                ->willReturn($fieldsDatum[2]);

            $field->expects($this->exactly(3))
                ->method('getOption')
                ->willReturnMap([
                    ['required', null, $fieldsDatum[1]],
                    ['input_type', null, $fieldsDatum[3]],
                    ['input_options', [], $fieldsDatum[4]]
                ]);

            $field->expects($this->once())
                ->method('getId')
                ->willReturn($fieldsDatum[0]);

            $fields[] = $field;
        }

        $filterMapperMock = $this->createMock(FilterMapper::class);
        $filterMapperMock->expects($this->once())
            ->method('getFields')
            ->willReturn(new ArrayCollection($fields));

        return $filterMapperMock;
    }
}