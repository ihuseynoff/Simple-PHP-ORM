<?php

/**
 * Author: Ilkin Huseynov
 * E-mail: ilkin@huseynov.me
 * Web: huseynov.me
 * Date: 7/11/18, 18:58
 **/


namespace App\Core\ORM;

interface RepositoryInterface
{

    /**
     * @return mixed
     */
    public function fetch();

    /**
     * @param $pk
     * @return mixed
     */
    public function fetchByPk($pk);

    /**
     * @param $pk
     * @return mixed
     */
    public function deleteByPk($pk);

    /**
     * @param $object
     * @return mixed
     */
    public function persist($object);

    /**
     * @return mixed
     */
    public function isExist();

    /**
     * @return mixed
     */
    public function count();


}



