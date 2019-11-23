<?php
namespace Povs\ListerBundle\Exception;

use Povs\ListerBundle\Type\ListType\ListTypeInterface;
use RuntimeException;
use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListException extends RuntimeException
{
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ListException
     */
    public static function listNotConfigured(): ListException
    {
        return new self(sprintf('List is not yet configured to be copied.'));
    }

    /**
     * @param string $typeName
     *
     * @return ListException
     */
    public static function listTypeNotConfigured(string $typeName): ListException
    {
        return new self(sprintf('List type "%s" is not configured', $typeName));
    }

    /**
     * @return ListException
     */
    public static function listNotBuilt(): ListException
    {
        return new self(sprintf('List is not built. BuiltList method is required before generating'));
    }

    /**
     * @param string $typeClassName
     *
     * @return ListException
     */
    public static function invalidListType(string $typeClassName): ListException
    {
        return new self(sprintf(
            'List type "%s" does not exists or does not implements %s',
            $typeClassName,
            ListTypeInterface::class
        ));
    }

    /**
     * @param string $typeName
     * @param string $message
     *
     * @return ListException
     */
    public static function invalidTypeConfiguration(string $typeName, string $message): ListException
    {
        return new self(sprintf('Invalid type "%s" configuration. %s', $typeName, $message));
    }

    /**
     * @param string $typeName
     * @param string $message
     *
     * @return ListException
     */
    public static function invalidTypeOptions(string $typeName, string $message): ListException
    {
        return new self(sprintf('Invalid type "%s" options. %s', $typeName, $message));
    }

    /**
     * @return ListException
     */
    public static function missingTranslator(): ListException
    {
        $message = 'Translator could not be found. Please install it running "composer require symfony/translation" or change list configuration';

        return new self($message);
    }
}