<?php
namespace Povs\ListerBundle\Exception;

use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListFieldException extends ListException
{
    /**
     * @var string
     */
    private $fieldId;

    /**
     * ListFieldException constructor.
     *
     * @param string         $id      fieldId
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $id, string $message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->fieldId = $id;
    }

    /**
     * @param string $id
     * @param string $message
     *
     * @return ListException
     */
    public static function invalidFieldConfiguration(string $id, string $message): ListException
    {
        return new self($id, sprintf('Invalid field "%s" configuration. %s', $id, $message));
    }

    /**
     * @param string $id
     *
     * @return ListException
     */
    public static function fieldNotExists(string $id): ListException
    {
        return new self($id, sprintf('Field "%s" do not exists', $id));
    }

    /**
     * @param string $id
     *
     * @return ListException
     */
    public static function invalidPropertiesOption(string $id): ListException
    {
        return new self($id, sprintf('"%s" - properties can only be set for a single path', $id));
    }

    /**
     * @param string $path join path
     *
     * @return ListException
     */
    public static function invalidPath(string $path): ListException
    {
        return new self($path, sprintf('Could not find join for path "%s"', $path));
    }

    /**
     * @param string $id         field id
     * @param string $type       type fully qualified class name
     * @param string $definition type interface fully qualified name
     *
     * @return ListException
     */
    public static function invalidType(string $id, string $type, string $definition): ListException
    {
        $message = sprintf('Type "%s" does not exist or does not implement %s', $type, $definition);

        return new self($id, $message);
    }

    /**
     * @return string
     */
    public function getFieldId(): string
    {
        return $this->fieldId;
    }
}