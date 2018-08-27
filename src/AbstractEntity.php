<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use BadMethodCallException;

/**
 * Base class for entity models.
 *
 * @method \DateTime getLastUpdateDate()
 * @method AbstractEntity setLastUpdateDate(\DateTime $value)
 *
 * @method integer getLastUpdateUserId()
 * @method AbstractEntity setLastUpdateUserId(integer $value)
 */
abstract class AbstractEntity
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $lastUpdateDate;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $lastUpdateUserId;

    /**
     * @var array
     */
    private static $serializerRecursionStack = [];


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lastUpdateDate = new DateTime();
        $this->lastUpdateUserId = 0;
    }

    /**
     * Trigger the appropriate 'get' method when property syntax is used.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        $method = 'get'.ucfirst($property);
        return $this->$method();
    }

    /**
     * Trigger the appropriate 'set' method when property syntax is used.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set($property, $value)
    {
        $method = 'set'.ucfirst($property);
        $this->$method($value);
    }

    /**
     * Magic method which responds when isset() or empty() is called on a non-existent property.
     * Allows us to respond correctly for values that would exist because of the magic __get.
     *
     * @param string $property
     * @return boolean
     */
    public function __isset($property)
    {
        return (property_exists($this, $property) && $this->$property !== null);
    }

    /**
     * Provide default 'get' and 'set' methods for all protected properties.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        if (property_exists($this, $property))
        {
            if ($prefix == 'set' && count($arguments) == 1)
            {
                $this->$property = $arguments[0];
                return $this;
            }
            else if ($prefix == 'get' && count($arguments) == 0)
            {
                return $this->$property;
            }
        }

        throw new BadMethodCallException('Call to undefined method '.$name.'()');
    }

    /**
     * Convert the object to an array.
     *
     * @param boolean $includeEntities Include fields which are references to other entities
     * @param boolean $includeCollections Include fields which are collections of other entities
     * @return array
     */
    public function getArrayCopy($includeEntities=false, $includeCollections=false)
    {
        self::$serializerRecursionStack[] = $this;

        $array = array();
        foreach (get_object_vars($this) as $key => $val)
        {
            if (substr($key, 0, 1) == '_')
                continue; //skip property names beginning with an underscore

            if (is_object($val))
            {
                if ($val instanceof AbstractEntity)
                {
                    if (!$includeEntities)
                        continue;
                    if (in_array($val, self::$serializerRecursionStack, true))
                        continue;
                    $val = $val->getArrayCopy($includeEntities, $includeCollections);
                }
                else if ($val instanceof Collection)
                {
                    if (!$includeCollections)
                        continue;
                    $children = [];
                    foreach ($val as $child) /* @var $child AbstractEntity */
                    {
                        if (in_array($child, self::$serializerRecursionStack, true))
                            continue;
                        $children[] = $child->getArrayCopy($includeEntities, $includeCollections);
                    }
                    $val = $children;
                }
                else if ($val instanceof DateTime)
                    $val = self::dateToString($val);
                else
                    $val = (string)$val;
            }
            $array[$key] = $val;
        }

        array_pop(self::$serializerRecursionStack);
        return $array;
    }

    /**
     * Set properties using keys and values from an array.
     *
     * Ignores keys that are not applicable to this object.
     *
     * @param array|\Traversable $data
     * @return void
     */
    public function populate($data = array())
    {
        foreach ($data as $key => $val)
        {
            $method = 'set'.ucfirst($key);
            if (property_exists($this, $key) || method_exists($this, $method))
            {
                //We can't import entities to collections using populate()
                if (property_exists($this, $key) && ($this->$key instanceof Collection))
                    continue;

                //Attempt to convert date strings to objects for keys that end with 'Date'
                if (is_string($val) && substr($key, -4) == 'Date'
                    && ($date = self::stringToDate($val)))
                {
                    $val = $date;
                }

                $this->$method($val);
            }
        }
    }

    /**
     * Get the unique identifier of this entity, if possible.
     *
     * @return mixed
     */
    public function getId()
    {
        if (property_exists($this, 'id'))
            return $this->id;

        return null;
    }

    /**
     * Check the properties of this entity to determine whether the object would be valid for
     * insertion into a datastore. Return any errors that are found.
     *
     * @param StorageInterface $store The store where the entity would be inserted. If provided,
     *    additional validation is possible.
     * @return ValidationError[] Will return an empty array if there are no errors
     */
    public function validateForInsert(StorageInterface $store=null)
    {
        return array();
    }

    /**
     * Check the properties of this entity to determine whether the object would be valid for
     * updating a datastore. Return any errors that are found.
     *
     * @param StorageInterface $store The store where the entity resides. If provided,
     *    additional validation is possible.
     * @return ValidationError[] Will return an empty array if there are no errors
     */
    public function validateForUpdate(StorageInterface $store=null)
    {
        return array();
    }

    /**
     * @param string|null $string
     * @return DateTime|null
     */
    public static function stringToDate($string)
    {
        if (empty($string))
            return null;

        $date = date_create_from_format(DateTime::RFC3339, $string);
        if (empty($date))
            $date = date_create_from_format('Y-m-d\TH:i:s.uP', $string); //Match fractional seconds
        if (empty($date))
            return null;

        return $date;
    }

    /**
     * @param DateTime|null $date
     * @return string|null
     */
    public static function dateToString($date)
    {
        if ($date instanceof DateTime)
            return $date->format(DateTime::RFC3339);
        return null;
    }
}