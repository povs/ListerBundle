<?php
namespace Povs\ListerBundle\Exception;

use Throwable;

/**
 * @author Povilas Margaiatis <p.margaitis@gmail.com>
 */
class ListQueryException extends ListException
{
    /**
     * @var string
     */
    private $ormError;

    /**
     * @var string
     */
    private $dql;

    /**
     * ListQueryException constructor.
     *
     * @param string         $ormError error message that is thrown by ORM
     * @param string         $dql      DQL that is built
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $ormError, string $dql, $message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->ormError = $ormError;
        $this->dql = $dql;
    }

    /**
     * @param string $ormError
     * @param string $dql
     *
     * @return ListException
     */
    public static function invalidQueryConfiguration(string $ormError, string $dql): ListException
    {
        return new self(
            $ormError,
            $dql,
            sprintf('Query error: %s. DQL: %s', $ormError, $dql)
        );
    }

    /**
     * @return string
     */
    public function getOrmError(): string
    {
        return $this->ormError;
    }

    /**
     * @return string
     */
    public function getDql(): string
    {
        return $this->dql;
    }
}