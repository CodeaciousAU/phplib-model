<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\Model\Exception;

use Codeacious\Model\ValidationError;

/**
 * Exception caused by invalid user input.
 */
class ValidationException extends \RuntimeException
{
    /**
     * @var ValidationError[]
     */
    private $validationErrors;

    /**
     * @param ValidationError[] $validationErrors
     */
    public function __construct(array $validationErrors)
    {
        parent::__construct('Input failed validation');
        $this->validationErrors = $validationErrors;
    }

    /**
     * @return ValidationError[]
     */
    public function getErrors()
    {
        return $this->validationErrors;
    }

    /**
     * Get any validation message that doesn't have a context set.
     *
     * @return string|null
     */
    public function getGeneralMessage()
    {
        foreach ($this->validationErrors as $error)
        {
            if ($error->getContext() === null)
                return $error->getMessage();
        }
        return null;
    }

    /**
     * Get all the validation messages that specify a context.
     *
     * @return array An array of the format ['context' => 'message', ...]
     */
    public function toContextArray()
    {
        $array = [];
        foreach ($this->validationErrors as $error)
        {
            if (!empty($error->getContext()))
                $array[$error->getContext()] = $error->getMessage();
        }

        return $array;
    }
}