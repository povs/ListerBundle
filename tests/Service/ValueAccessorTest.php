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
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function testGetFieldValueType(): void
    {
        $this->createMocks(true);
        $selectorTypeMock = $this->createMock(SelectorTypeInterface::class);
        $fieldViewMock = $this->createMock(FieldView::class);
        $listFieldMock = $this->createMock(ListField::class);
        $fieldTypeMock = $this->createMock(FieldTypeInterface::class);
        $fieldViewMock->expects($this->once())
            ->method('getListField')
            ->willReturn($listFieldMock);
        $listFieldMock->expects($this->exactly(6))
            ->method('getOption')
            ->willReturnMap([
                ['selector', null, 'selector'],
                ['translate', null, true],
                ['translation_domain', null, 'domain'],
                ['translation_prefix', null, 'prefix_'],
                ['value', null, null],
                ['field_type_options', null, ['opt' => 'val']]
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
            ->with(['field_id' => 'foo'], 'id')
            ->willReturn('foo');
        $this->typeResolverMock->expects($this->once())
            ->method('getTypeName')
            ->willReturn('list_type');
        $fieldTypeMock->expects($this->once())
            ->method('getValue')
            ->with('foo', 'list_type', ['opt' => 'val'])
            ->willReturn('processed value');
        $this->translatorMock->expects($this->once())
            ->method('trans')
            ->with('prefix_processed value', [], 'domain')
            ->willReturn('translated_val');

        $val = $this->getAccessor()->getFieldValue($fieldViewMock, ['field_id' => 'foo']);
        $this->assertEquals('translated_val', $val);
    }

    public function testGetFieldValueCallable(): void
    {
        $this->createMocks(true);
        $selectorTypeMock = $this->createMock(SelectorTypeInterface::class);
        $fieldViewMock = $this->createMock(FieldView::class);
        $listFieldMock = $this->createMock(ListField::class);
        $fieldViewMock->expects($this->once())
            ->method('getListField')
            ->willReturn($listFieldMock);
        $listFieldMock->expects($this->exactly(3))
            ->method('getOption')
            ->willReturnMap([
                ['selector', null, 'selector'],
                ['translate', null, false],
                ['value', null, static function(array $data, string $type) {
                    return sprintf('%s %s %s', $data[0], $data[1], $type);
                }]
            ]);
        $listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $this->typeResolverMock->expects($this->once())
            ->method('getTypeName')
            ->willReturn('list_type');
        $this->selectorTypeLocatorMock->expects($this->once())
            ->method('get')
            ->willReturn($selectorTypeMock);
        $selectorTypeMock->expects($this->once())
            ->method('getValue')
            ->with(['field_id' => 'foo'], 'id')
            ->willReturn(['foo', 'bar']);

        $val = $this->getAccessor()->getFieldValue($fieldViewMock, ['field_id' => 'foo']);
        $this->assertEquals('foo bar list_type', $val);
    }

    public function testGetFieldValueIdentifiers(): void
    {
        $this->createMocks(true);
        $selectorTypeMock = $this->createMock(SelectorTypeInterface::class);
        $fieldViewMock = $this->createMock(FieldView::class);
        $listFieldMock = $this->createMock(ListField::class);
        $fieldViewMock->expects($this->once())
            ->method('getListField')
            ->willReturn($listFieldMock);
        $listFieldMock->expects($this->exactly(3))
            ->method('getOption')
            ->willReturnMap([
                ['selector', null, 'selector'],
                ['translate', null, false]
            ]);
        $listFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn('id');
        $listFieldMock->expects($this->exactly(2))
            ->method('getPaths')
            ->willReturn(['key1' => 'path1', 'key2' => 'path2', 'key3' => 'path3', 'key4' => 'path4']);
        $this->selectorTypeLocatorMock->expects($this->once())
            ->method('get')
            ->willReturn($selectorTypeMock);
        $selectorTypeMock->expects($this->once())
            ->method('getValue')
            ->with(['field_id' => 'foo'], 'id')
            ->willReturn(['foo', 'bar', 'foo1', 'bar1']);

        $val = $this->getAccessor()->getFieldValue($fieldViewMock, ['field_id' => 'foo']);
        $this->assertEquals(['key1' => 'foo', 'key2' => 'bar', 'key3' => 'foo1', 'key4' => 'bar1'], $val);
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