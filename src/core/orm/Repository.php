<?php

/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/11/18, 18:58
 **/


namespace App\Core\ORM;

use \App\Core\Database\Connection;
use \ReflectionClass;
use \PDOException;
use \PDO;

abstract class Repository
{

    /**
     * @var string
     */
    protected $db;

    /**
     * @var object
     */
    protected $class;

    /**
     * @var string
     */
    protected $objectTable;

    /**
     * @var string
     */
    protected $objectPrimaryKey;

    /**
     * @var array
     */
    protected $objectColumns;

    /**
     * @var string
     */
    protected $filter;

    /**
     * @var string
     */
    protected $sort;

    /**
     * @var string
     */
    protected $limit;

    /**
     * @var string
     */
    protected $select;

    /**
     * @var string
     */
    protected $lastInsertId;


    /**
     * Repository constructor.
     * @param object $class
     */
    public function __construct($class)
    {
        $this->db = Connection::getInstance();
        $this->class = new ReflectionClass($class);


        $className = $this->underscored($this->class->getShortName());

        // TODO: implement reflection class docComment

        $table = $this->class->getProperty("_table");
        $primaryKey = $this->class->getProperty("_primaryKey");
        $columns = $this->class->getProperty("_columns");

        $this->objectTable = is_null($table) ? $table : $className . "s";
        $this->objectPrimaryKey = is_null($primaryKey) ? $primaryKey : "id";
        $this->objectColumns = is_null($columns) ? implode($columns, ',') : "*";

        $this->initParams();
    }


    /**
     *  Initialize parameters
     */
    private function initParams()
    {
        $this->filter = null;

        $this->sort = null;

        $this->limit = null;

        $this->select = null;
    }


    /**
     *  Convert camelCase string to camel_case
     *
     * @param $str
     * @return string
     */
    private function underscored($str)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $str)), '_');
    }


    /**
     * @param $query
     * @return mixed
     */
    protected function executeQuery($query)
    {
        try {
            $connection = $this->db->setConnection();


            if ($this->filter != null) {
                $query .= " where " . $this->filter["columns"];
            }


            if ($this->sort != null) {
                $query .= " order by " . $this->sort;
            } else {
                $query .= " order by " . $this->objectPrimaryKey . " desc";
            }

            if ($this->limit != "") {
                $query .= " limit " . $this->limit;
            }


            $sth = $connection->prepare($query);

            if ($this->filter != null) {
                $sth->execute($this->filter["values"]);
            } else {
                $sth->execute();
            }

        } catch (PDOException $ex) {
            echo "WARNING ! Can't establish query from database...";
            throw $ex;
        }


        $this->initParams();
        return $sth;
    }


    /**
     * @param $array
     * @return $this
     */
    public function filter($array)
    {
        $columns = "";
        $exc = [];

        if ($this->isArrayMultiDimensional($array)) {

            foreach ($array as $statement) {

                if (count($statement) == 3) {
                    $column = $statement[0];
                    $operation = $statement[1];
                    $value = $statement[2];

                } else {
                    $column = $statement[0];
                    $operation = " = ";
                    $value = $statement[1];

                }

                if ($operation == "IN") {
                    $columns .= $column . " " . $operation . " " . $value . " and ";
                } else {
                    $columns .= $column . " " . $operation . "? and ";
                    array_push($exc, $value);
                }


            }
        } else {
            if (count($array) == 3) {
                $column = $array[0];
                $operation = $array[1];
                $value = $array[2];
            } else {
                $column = $array[0];
                $operation = " = ";
                $value = $array[1];
            }
            if ($operation == "IN") {
                $columns .= $column . " " . $operation . " " . $value . " and ";
            } else {
                $columns .= $column . " " . $operation . " ? and ";
                array_push($exc, $value);
            }


        }

        $this->filter = ["columns" => rtrim($columns, " and "), "values" => $exc];

        return $this;

    }


    /**
     * @property
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // TODO: validate column name

        $filter = preg_split('/(?=[A-Z])/', $method);
        if ($filter[0] == "filter" && $filter[1] == "By") {

            if ($this->filter != null) {
                $column = $this->filter["columns"] . " and ";
                $values = $this->filter["values"];
            } else {
                $column = "";
                $values = [];
            }

            foreach ($filter as $k => $name) {
                if ($k < 2) continue;
                $column .= strtolower($name) . "_";
            }
            $column = rtrim($column, '_') . "=?";
            array_push($values, $arguments[0]);

            $this->filter = ["columns" => $column, "values" => $values];
            return $this;

        }


        if ($filter[0] == "fetch" && $filter[1] == "By") {

            if ($this->filter != null) {
                $column = $this->filter["columns"] . " and ";
                $values = $this->filter["values"];
            } else {
                $column = "";
                $values = [];
            }

            // TODO: use underscore function
            foreach ($filter as $k => $name) {
                if ($k < 2) continue;
                $column .= strtolower($name) . "_";
            }
            $column = rtrim($column, '_') . "=?";
            array_push($values, $arguments[0]);

            $this->filter = ["columns" => $column, "values" => $values];


            $col = $this->objectColumns;

            if ($this->select != null) {
                $col = $this->select;
            }

            $sql = "select " . $col . " from  " . $this->objectTable;
            $arr = $this->executeQuery($sql);

            if ($arr->rowCount() > 0) {
                return $arr->fetchAll(PDO::FETCH_CLASS, $this->class->getName())[0];
            } else {
                return null;
            }

        } else {
            return null;
        }

    }


    /**
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        $this->select = $select;
        return $this;
    }


    /**
     * @param $num
     * @return $this
     */
    public function take($num)
    {
        $this->limit = $num;
        return $this;
    }


    /**
     * @param $sort
     * @return $this
     */
    public function sort($sort)
    {
        $this->sort = $sort;
        return $this;
    }


    /**
     * @param $object
     * @return array
     */
    private function clearProperties($object)
    {
        $properties = (array)$object;

        $newProperties = [];

        $className = $this->class->getName();
        foreach ($properties as $key => $value) {

            $key = str_replace('*', '', $key);
            if (!!preg_match('#\\b' . preg_quote($className, '#') . '\\b#i', $key)) {
                $key = substr($key, (strlen($className) + 1), strlen($key));
            }
            $key = strip_tags($key);

            $newProperties[$key] = $value;
        }

        $reservedProperties = ["_hiddenVars", "_timestamps", "_table", "_primaryKey", "_columns"];


        return array_diff_key($newProperties, array_flip($reservedProperties));
    }


    /**
     * @param $object
     * @return array
     *
     */
    protected function objectProperty($object)
    {
        $columns = null;
        $values = [];
        $pkMethod = 1;

        $properties = $this->clearProperties($object);


        foreach ($properties as $key => $value) {

            if ($value !== NULL) {

                if ($key == $this->objectPrimaryKey) {
                    $pkMethod = $this->objectPrimaryKey . "=" . $value;
                    continue;
                }

                $columns .= $key . "=?, ";
                array_push($values, $value);
            }
        }


        return ['columns' => rtrim($columns, ", "), 'values' => $values, 'where' => $pkMethod];
    }


    /**
     * @param $error
     * @param $message
     * @return array
     */
    protected function response($error, $message)
    {
        return array('error' => $error, 'message' => $message);

    }


    /**
     * @param $array
     * @return boolean
     */
    protected function isArrayMultiDimensional($array)
    {
        foreach ($array as $v) if (is_array($v)) return true;
        return false;
    }

    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }


}