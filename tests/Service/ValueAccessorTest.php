<?php
namespace Povs\ListerBundle\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Povs\ListerBundle\DependencyInjection\Locator\SelectorTypeLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\Type\FieldType\FieldTypeInterface;
use Povs\ListerBundle\Type\SelectorType\SelectorTypeInterface;
use Povs\ListerBundle\View\FieldView;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ValueAccessorTest extends TestCase
{
    /**
     * @var MockObject|ConfigurationResolver
     */
    private $configMock;

    /**
     * @var MockObject|ListTypeResolver
     */
    private $typeResolverMock;

    /**
     * @var MockObject|SelectorTypeLocator
     */
    private $selectorTypeLocatorMock;

    /**
     * @var MockObject|TranslatorInterface|null
     */
    private $translatorMock;

    public function testGetHeaderValue(): void
    {
        $this->createMocks(true);
        $fieldViewMock = $this->createMock(FieldView::class);
        $fieldViewMock->expects($this->once())
            ->method('getLabel')
            ->willReturn('label');
        $this->configMock->expects($this->once())
            ->method('getTranslate')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('getTranslationDomain')
            ->willReturn('domain');
        $this->translatorMock->expects($this->once())
            ->method('trans')
            ->with('label', [], 'domain')
            ->willReturn('translatedLabel');

        $listValueAccessor = $this->getAccessor();
        $this->assertEquals('translatedLabel', $listValueAccessor->getHeaderValue($fieldViewMock));
    }

    public function testGetHeaderValueWithoutTranslator(): void
    {
        $this->expectException(ListException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Translator could not be found. Please install it running "composer require symfony/translation" or change list configuration');

        $this->createMocks(false);
        $fieldViewMock = $this->createMock(FieldView::class);
        $fieldViewMock->expects($this->once())
            ->method('getLabel')
            ->willReturn('label');
        $this->configMock->expects($this->once())
            ->method('getTranslate')
            ->willReturn(true);

        $listValueAccessor = $this->getAccessor();
        $listValueAccessor->getHeaderValue($fieldViewMock);
    }

    public function testGetFieldValue(): void
    {
        $this->createMocks(true);
        $selectorTypeMock = $this->createMock(SelectorTypeInterface::class);
        $fieldViewMock = $this->createMock(FieldView::class);
        $listFieldMock = $this->createMock(ListField::class);
        $fieldTypeMock = $this->createMock(FieldTypeInterface::class);
        $fieldViewMock->expects($this->once())
            ->method('getListField')
            ->willReturn($listFieldMock);
        $listFieldMock->expects($this->exactly(4))
            ->method('getOption')
            ->willReturnMap([
               ['selector', null, 'selector'],
               ['translate', null, true],
               ['translation_domain', null, 'domain'],
               ['translation_prefix', null, 'prefix_']
            ]);
        $listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $listFieldMock->expects($this->once())
            ->method('getType')
            ->willReturn($fieldTypeMock);
        $this->selectorTypeLocatorMock->expects($this->once())
            ->method('get')
            ->willReturn($selectorTypeMock);
        $selectorTypeMock->expects($this->once())
            ->method('getValue')
            ->with('foo')
            ->willReturn('bar');
        $this->translatorMock->expects($this->once())
            ->method('trans')
            ->with('prefix_bar', [], 'domain')
            ->willReturn('translated_val');
        $this->typeResolverMock->expects($this->once())
            ->method('getTypeName')
            ->willReturn('list_type');
        $fieldTypeMock->expects($this->once())
            ->method('getValue')
            ->with('translated_val', 'list_type')
            ->willReturn(['processed', 'value']);

        $val = $this->getAccessor()->getFieldValue($fieldViewMock, ['field_id' => 'foo'], true);
        $this->assertEquals('processed value', $val);
    }

    private function createMocks(bool $withTranslator): void
    {
        $this->configMock = $this->createMock(ConfigurationResolver::class);
        $this->typeResolverMock = $this->createMock(ListTypeResolver::class);
        $this->selectorTypeLocatorMock = $this->createMock(SelectorTypeLocator::class);

        if ($withTranslator) {
            $this->translatorMock = $this->createMock(TranslatorInterface::class);
        }
    }

    /**
     * @return ValueAccessor
     */
    private function getAccessor(): ValueAccessor
    {
        return new ValueAccessor(
            $this->configMock,
            $this->typeResolverMock,
            $this->selectorTypeLocatorMock,
            $this->translatorMock
        );
    }
}