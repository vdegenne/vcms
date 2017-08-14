<?php
declare(strict_types=1);

namespace vcms\database;


use vcms\AutoLoader;
use vcms\VObject;

class EntityManager extends VObject {

    /** @var Database */
    protected $Database;

    public $tablename;
    public $objectname;


    //    function __construct () {
    //        parent::__construct();
    //    }


    static function construct (string $tablename, string $objectname = null, Database $Database = null)
    {

        $Obj = new EntityManager();

        if ($Database === null) {
            global $Database;
            if ($Database === null) {
                throw new \Exception('needs a Database Object');
            } else {
                $Obj->Database = $Database;
            }
        }

        $Obj->tablename = $tablename;

        /**
         * If the objectname is null,
         * we try to resolve from the tablename
         */
        if ($objectname === null) {
            $pieces = explode('.', $tablename);
            $lastPiece = array_pop($pieces);
            $lastPiece[0] = strtoupper($lastPiece[0]);
            if ($lastPiece[strlen($lastPiece) - 1] === 's') {
                $lastPiece = substr($lastPiece, 0, -1);
            }
            array_push($pieces, $lastPiece);
            $Obj->objectname = implode('\\', $pieces);
        }


        /**
         * we can eval the object if it doesn't exist
         */
        if (empty(AutoLoader::search($Obj->objectname, true))) {
            $Obj->eval_object();
        }

        return $Obj;
    }


    function eval_object () {

        $classdef = '';
        
        $response = $this->Database->query("select * from {$this->tablename} limit 0");
        
        for ($i = 0; $i < $response->columnCount(); ++$i) {
            $columnMeta = $response->getColumnMeta($i);
            $properties[] = $columnMeta['name'];
        }

        if (($lastAntiSlash = strrpos($this->objectname, '\\')) >= 0) {
            $namespace = substr($this->objectname, 0, $lastAntiSlash);
            $classname = substr($this->objectname, $lastAntiSlash + 1);
            $classdef .= "namespace $namespace;\n";
        }
        else {
            $classname = $this->objectname;
        }

        $classdef .= "class $classname {}";

        eval($classdef);
    }

    function get_statement (string $SQL, array $placeholders = [], int $fetchMode = \PDO::FETCH_CLASS): \PDOStatement
    {
        try {
            /** @var \PDOStatement $statement */
            $statement = $this->Database->prepare($SQL);

            if ($fetchMode === \PDO::FETCH_CLASS) {
                $statement->setFetchMode(\PDO::FETCH_CLASS, $this->objectname);
            } else {
                $statement->setFetchMode($fetchMode);
            }

            if (!is_array($placeholders)) {
                preg_match_all('/:([a-zA-Z]{1}[0-9a-zA-Z_]+)/', $SQL, $find);
                if (count($find[1]) > 1) {
                    throw new \Exception('too much placeholders');
                }

                $placeholders = [$find[1][0] => $placeholders];
            }

            $statement->execute($placeholders);
            return $statement;
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}