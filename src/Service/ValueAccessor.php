<?php
namespace Povs\ListerBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Povs\ListerBundle\DependencyInjection\Locator\SelectorTypeLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\Exception\ListQueryException;
use Povs\ListerBundle\Mapper\ListField;
use Povs\ListerBundle\View\FieldView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ValueAccessor
{
    /**
     * @var ConfigurationResolver
     */
    private $configuration;

    /**
     * @var ListTypeResolver
     */
    private $typeResolver;

    /**
     * @var SelectorTypeLocator
     */
    private $selectorTypeLocator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ValueAccessor constructor.
     *
     * @param ConfigurationResolver $configurationResolver
     * @param ListTypeResolver      $listTypeResolver
     * @param SelectorTypeLocator   $selectorTypeLocator
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        ConfigurationResolver $configurationResolver,
        ListTypeResolver $listTypeResolver,
        SelectorTypeLocator $selectorTypeLocator,
        ?TranslatorInterface $translator
    ) {
        $this->configuration = $configurationResolver;
        $this->typeResolver = $listTypeResolver;
        $this->selectorTypeLocator = $selectorTypeLocator;
        $this->translator = $translator;
    }

    /**
     * Fetches lazy loadable data
     *
     * @param array             $data         data that is fetched not lazily
     * @param QueryBuilder|null $queryBuilder queryBuilder for fetching lazy data query
     *
     * @return array
     */
    public function normalizeData(array $data, ?QueryBuilder $queryBuilder): array
    {
        if ($queryBuilder) {
            $selector = sprintf(
                '%s.%s = :identifier',
                $this->configuration->getAlias(),
                $this->configuration->getIdentifier()
            );

            try {
                $query = clone $queryBuilder;
                $lazyData = $query->andWhere($selector)
                    ->setParameter('identifier', $data[ListQueryBuilder::IDENTIFIER_ALIAS])
                    ->getQuery()
                    ->getResult()[0];

                $data = array_merge($data, $lazyData);
            } catch (Throwable $e) {
                throw ListQueryException::invalidQueryConfiguration($e->getMessage(), $query->getDQL());
            }
        }

        unset($data[ListQueryBuilder::IDENTIFIER_ALIAS]);

        return $data;
    }

    /**
     * @param FieldView $fieldView
     *
     * @return string
     */
    public function getHeaderValue(FieldView $fieldView): string
    {
        $value = $fieldView->getLabel();
        $translate = $this->configuration->getTranslate();

        if ($translate) {
            $domain = $this->configuration->getTranslationDomain();
            $value = $this->translate($value, $domain);
        }

        return $value;
    }

    /**
     * @param FieldView $fieldView
     * @param array     $data
     *
     * @return mixed
     */
    public function getFieldValue(FieldView $fieldView, array $data)
    {
        $listField = $fieldView->getListField();
        $value = $this->selectorTypeLocator->get($listField->getOption(ListField::OPTION_SELECTOR))
            ->getValue($data, $listField->getId());

        $this->addIdentifiers($listField, $value);
        $this->processFieldValue($listField, $value);
        $this->translateValue($listField, $value);

        return $value;
    }

    /**
     * @param ListField    $listField
     * @param mixed        $value
     */
    private function processFieldValue(ListField $listField, &$value): void
    {
        if ($callable = $listField->getOption(ListField::OPTION_VALUE)) {
            $value = $callable($value, $this->typeResolver->getTypeName());
        }

        if ($type = $listField->getType()) {
            $value = $type->getValue(
                $value,
                $this->typeResolver->getTypeName(),
                $listField->getOption(ListField::OPTION_FIELD_TYPE_OPTIONS)
            );
        }
    }

    /**
     * @param ListField $listField
     * @param mixed     $value
     */
    private function addIdentifiers(ListField $listField, &$value): void
    {
        if (is_array($value) && count($listField->getPaths()) === count($value)) {
            $newValue = [];
            $i = 0;

            foreach ($listField->getPaths() as $key => $path) {
                $newValue[$key] = $value[$i];
                $i++;
            }

            $value = $newValue;
        }
    }

    /**
     * @param ListField $listField
     * @param mixed     $value
     */
    private function translateValue(ListField $listField, &$value): void
    {
        if ((true === $listField->getOption(ListField::OPTION_TRANSLATE)) &&
            (null !== $value || true === $listField->getOption(ListField::OPTION_TRANSLATE_NULL))
        ) {
            $domain = $listField->getOption(ListField::OPTION_TRANSLATION_DOMAIN);
            $prefix = $listField->getOption(ListField::OPTION_TRANSLATION_PREFIX);

            $value = $this->translate(sprintf('%s%s', $prefix, $value), $domain);
        }
    }

    /**
     * @param string      $id
     * @param string|null $domain
     *
     * @return string|null
     */
    private function translate(string $id, ?string $domain): ?string
    {
        if (!$this->translator) {
            throw ListException::missingTranslator();
        }

        return $this->translator->trans($id, [], $domain);
    }
}