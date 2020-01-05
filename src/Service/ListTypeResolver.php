<?php
namespace Povs\ListerBundle\Service;

use Povs\ListerBundle\DependencyInjection\Locator\ListTypeLocator;
use Povs\ListerBundle\Exception\ListException;
use Povs\ListerBundle\View\ListView;
use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListTypeResolver
{
    /**
     * @var ListTypeLocator
     */
    private $typeLocator;

    /**
     * @var ConfigurationResolver
     */
    private $configurationResolver;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var ListTypeInterface
     */
    private $type;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * ListTypeResolver constructor.
     *
     * @param ListTypeLocator       $typeLocator
     * @param ConfigurationResolver $configurationResolver
     * @param RequestHandler        $requestHandler
     */
    public function __construct(
        ListTypeLocator $typeLocator,
        ConfigurationResolver $configurationResolver,
        RequestHandler $requestHandler
    ) {
        $this->typeLocator = $typeLocator;
        $this->configurationResolver = $configurationResolver;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function resolveType(string $type): ListTypeResolver
    {
        $this->typeName = $type;
        $class = $this->configurationResolver->getListTypeClass($this->typeName);

        if (!$this->typeLocator->has($class)) {
            throw ListException::invalidListType($class);
        }

        $this->type = $this->typeLocator->get($class);
        $this->resolveConfiguration();
        $this->perPage = $this->type->getLength($this->requestHandler->getLength());
        $this->currentPage = $this->type->getCurrentPage($this->requestHandler->getCurrentPage());

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param ListView $listView
     * @param array    $options
     *
     * @return Response
     */
    public function getResponse(ListView $listView, array $options = []): Response
    {
        return $this->type->generateResponse($listView, $this->resolveOptions($options));
    }

    /**
     * @param ListView $listView
     * @param array    $options
     *
     * @return mixed
     */
    public function getData(ListView $listView, array $options = [])
    {
        return $this->type->generateData($listView, $this->resolveOptions($options));
    }

    /**
     * Resolves and sets type configuration
     */
    private function resolveConfiguration(): void
    {
        $config = $this->configurationResolver->getTypeConfiguration($this->typeName);
        $resolver = new OptionsResolver();
        $this->type->configureSettings($resolver);

        try {
            $configuration = $resolver->resolve($config);
        } catch (Throwable $e) {
            throw ListException::invalidTypeConfiguration($this->getTypeName(), $e->getMessage());
        }

        $this->type->setConfig($configuration);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $this->type->configureOptions($resolver);

        try {
            $options = $resolver->resolve($options);
        } catch (Throwable $e) {
            throw ListException::invalidTypeOptions($this->getTypeName(), $e->getMessage());
        }

        return $options;
    }
}