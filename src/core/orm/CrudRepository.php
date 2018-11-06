<?php

/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/11/18, 18:58
 **/

namespace App\Core\ORM;

use \PDO;
use \PDOException;

abstract class CrudRepository extends Repository implements RepositoryInterface
{

    /**
     * constructor.
     * @param $class
     */
    public function __construct($class)
    {
        parent::__construct($class);
    }


    /**
     * @param $object
     * @return array
     * @throws \Exception
     */
    public function persist($object)
    {
        try {
            $connection = $this->db->setConnection();

            $params = $this->objectProperty($object);


            if ($params['where'] == 1) {
                $query = "INSERT INTO $this->objectTable SET " . $params['columns'];

                $message = "Data successfully inserted!";

            } else {
                $query = "UPDATE  $this->objectTable SET " . $params['columns'] . " WHERE " . $params['where'];

                $message = "Data successfully updated!";
            }

            $sth = $connection->prepare($query);

            $sth->execute($params['values']);


            $this->lastInsertId = $connection->lastInsertId();

            return $this->response(false, $message);

        } catch (PDOException $ex) {
            echo "WARNING ! Can't persist data to database...";
            throw $ex;
        }
    }


    /**
     * @param $query
     * @param null $class
     * @return mixed
     */
    protected function nativeFetch($query, $class = null)
    {
        $class = ($class != null) ? $class : $this->class->getName();

        $result = $this->executeQuery($query);
        return $result->fetchAll(PDO::FETCH_CLASS, $class);
    }


    /**
     * @return mixed
     */
    public function fetch()
    {
        $col = $this->objectColumns;

        if ($this->select != null) {
            $col = $this->select;
        }

        $sql = "select " . $col . " from  " . $this->objectTable;
        $result = $this->executeQuery($sql);
        return $result->fetchAll(PDO::FETCH_CLASS, $this->class->getName());
    }


    /**
     * @param $pk
     * @return  mixed
     * @throws \Exception
     */
    public function fetchByPk($pk)
    {
        $col = $this->objectColumns;

        if ($this->select != null) {
            $col = $this->select;
        }

        $sql = "select " . $col . " from  " . $this->objectTable;
        $this->filter([$this->objectPrimaryKey, $pk]);
        $arr = $this->executeQuery($sql);


        if ($arr->rowCount() > 0) {
            return $arr->fetchAll(PDO::FETCH_CLASS, $this->class->getName())[0];
        } else {
            return null;
        }


    }


    /**
     * @param $pk
     * @return array
     * @throws \Exception
     */
    public function deleteByPK($pk)
    {
        $error = true;

        $pk = is_array($pk) ? implode(",", $pk) : $pk;


        try {
            $connection = $this->db->setConnection();

            $sth = $connection->prepare("delete from $this->objectTable where $this->objectPrimaryKey in (" . $pk . ") ");
            $sth->execute();

            $error = false;

        } catch (PDOException $ex) {
            echo "WARNING ! Can't delete data from database...";
            throw $ex;
        }

        return $this->response($error, "Record(s) successfully deleted!");

    }


    /**
     *
     * @return mixed
     */
    public function count()
    {
        $sql = "select count(" . $this->primaryKey . ") as count from " . $this->objectTable;
        $sth = $this->executeQuery($sql);
        $arr = $sth->fetch(PDO::FETCH_ASSOC);
        return $arr["count"];
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function isExist()
    {
        $r = false;

        try {

            $sql = "select " . $this->objectPrimaryKey . " from " . $this->objectTable;
            $sth = $this->executeQuery($sql);
            if ($sth->rowCount() > 0) $r = true;

        } catch (PDOException $ex) {
            echo "WARNING ! Can't check data in database...";
            throw $ex;
        }

        return $r;
    }


}