<?php
/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/9/18, 15:40
 **/

namespace App\Core\Model;


abstract class Model implements \JsonSerializable
{

    /**
     * @var array
     */
    protected $_hiddenVars;

    /**
     * @var boolean
     */
    protected $_timestamps;

    /**
     * @var string
     */
    protected $_table;


    /**
     * @var string
     */
    protected $_primaryKey;


    /**
     * @var string
     */
    protected $_columns;


    /**
     * @var string
     */
    private $created_at;

    /**
     * @var string
     */
    private $modified_at;

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return string
     */
    public function getModifiedAt(): string
    {
        return $this->modified_at;
    }

    /**
     * @param string $modified_at
     */
    public function setModifiedAt($modified_at)
    {
        $this->modified_at = $modified_at;
    }


    /**
     * @return array
     */
    function jsonSerialize()
    {
        $result = [];

        $objectVars = self::getObjectVars();

        //iterate object columns and create key->value array
        foreach ($objectVars as $key => $value) {


            if ($this->{$key} != null) {
                $result[$key] = $this->{$key};
            }
        }

        return $result;
    }


    /**
     *  Get optimized variables of object
     * @return array
     */
    private function getObjectVars()
    {
        // TODO: implement access to private variables

//        $reflect = new \ReflectionClass($this);
//        $objectVars = $reflect->getProperties();


        $objectVars = get_object_vars($this);
        $reservedProperties = ["_hiddenVars", "_timestamps", "_table", "_primaryKey", "_columns"];


        // if hiddenVars is not null, merge it with reserved ones
        if ($this->_hiddenVars !== null) {
            $reservedProperties = array_merge($reservedProperties, $this->_hiddenVars);
        }

        $optimisedVars = array_diff_key($objectVars, array_flip($reservedProperties));

        return $optimisedVars;
    }


}