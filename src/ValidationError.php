<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\Model;

/**
 * Communicates a problem with some user-supplied data.
 */
class ValidationError
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $context;


    /**
     * @param string $type A string identifying the type of error. The TYPE_ constants define
     *    standard types, but any string can be used. A validator should produce no more than one
     *    error of each type for a given context.
     * @param string $message A message describing the error in user-friendly terms
     * @param string|null $context A string identifying the property that contained the error,
     *    if applicable. Use colons to specify a path, if the data was part of some structure
     *    (eg. manager:postalAddress:city)
     */
    public function __construct($type, $message, $context=null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     * @return ValidationError This
     */
    public function setType($value)
    {
        $this->type = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $value
     * @return ValidationError This
     */
    public function setMessage($value)
    {
        $this->message = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $value
     * @return ValidationError This
     */
    public function setContext($value)
    {
        $this->context = $value;
        return $this;
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_CONFLICTING.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function conflicting($message, $context=null)
    {
        return new static(static::TYPE_CONFLICTING, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_INVALID.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function invalid($message, $context=null)
    {
        return new static(static::TYPE_INVALID, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_MISSING.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function missing($message, $context=null)
    {
        return new static(static::TYPE_MISSING, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_NOT_NUMBER.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function notNumber($message, $context=null)
    {
        return new static(static::TYPE_NOT_NUMBER, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_TOO_LONG.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function tooLong($message, $context=null)
    {
        return new static(static::TYPE_TOO_LONG, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_TOO_SHORT.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function tooShort($message, $context=null)
    {
        return new static(static::TYPE_TOO_SHORT, $message, $context);
    }

    /**
     * Convenience method for construcfting a ValidationError with TYPE_UNRECOGNIZED.
     *
     * @param string $message
     * @param string|null $context
     * @return ValidationError
     */
    public static function unrecognized($message, $context=null)
    {
        return new static(static::TYPE_UNRECOGNIZED, $message, $context);
    }


    /**
     * A value of a property conflicts with the current state of the model, or with another model.
     */
    const TYPE_CONFLICTING = 'conflictingValue';
    /**
     * Indicates generic failure of a validation rule, when none of the more specific types are
     * applicable.
     */
    const TYPE_INVALID = 'notValid';
    /**
     * A required property was not supplied, or was left empty when a non-empty value is required.
     */
    const TYPE_MISSING = 'isEmpty';
    /**
     * A number was expected, but another type was supplied and conversion was not possible.
     */
    const TYPE_NOT_NUMBER = 'notDigits';
    /**
     * A string value exceeds the maximum length allowed.
     */
    const TYPE_TOO_LONG = 'stringLengthTooLong';
    /**
     * A string value doesn't meet the minimum length required.
     */
    const TYPE_TOO_SHORT = 'stringLengthTooShort';
    /**
     * A property was supplied which is not part of the model.
     */
    const TYPE_UNRECOGNIZED = 'unrecognized';
}