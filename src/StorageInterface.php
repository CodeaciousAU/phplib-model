<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 * @version $Id: StorageInterface.php 2535 2017-02-26 13:24:34Z glenn $
 */

namespace Codeacious\Model;


interface StorageInterface
{
    /**
     * @param string $entityName
     * @param array $criteria
     * @return AbstractEntity[]
     */
    public function findBy($entityName, array $criteria);

    /**
     * @param string $entityName
     * @param array $criteria
     * @return AbstractEntity|null
     */
    public function findOneBy($entityName, array $criteria);

    /**
     * @param string $dql
     * @param array $params
     * @return AbstractEntity[]
     */
    public function executeQuery($dql, array $params=[]);
}