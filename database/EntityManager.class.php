<?php
declare(strict_types=1);

namespace vcms\database;


use vcms\AutoLoader;
use vcms\VObject;
use vcms\VString;

class EntityManager extends VObject {

    static protected $singletons;

    /** @var Database */
    protected $Database;

    public $tablename;
    public $objectname;



    static function get (string $tablename, string $objectname = null, Database $Database = null): EntityManager
    {
        if (isset(self::$singletons[$tablename])) {
            return self::$singletons[$tablename];
        }

        $Em = new EntityManager();

        if ($Database === null) {
            global $Database;
            if ($Database === null) {
                throw new \Exception('needs a Database Object');
            } else {
                $Em->Database = $Database;
            }
        }

        $Em->tablename = $tablename;


        /** If no object were specified, we try to resolve a name */
        if ($objectname === null) {
            $pieces = explode('.', $tablename);
            $lastPiece = array_pop($pieces);
//            $lastPiece[0] = strtoupper($lastPiece[0]);
            if ($lastPiece[strlen($lastPiece) - 1] === 's') {
                $lastPiece = substr($lastPiece, 0, -1);
            }
            $lastPiece = VString::ToCamelCase($lastPiece, '_');
            array_push($pieces, $lastPiece);
            $objectname = implode('\\', $pieces);
        }
        $Em->objectname = $objectname;

        /** and we eval the object if no class were found */
        if (empty(AutoLoader::search($objectname, true))) {
            $Em->eval_object();
        }

        self::$singletons[$tablename] = $Em;
        return $Em;
    }


    function eval_object ()
    {

        $classdef = '';

        $response = $this->Database->query("select * from {$this->tablename} limit 0");

        for ($i = 0; $i < $response->columnCount(); ++$i) {
            $columnMeta = $response->getColumnMeta($i);
            $properties[] = $columnMeta['name'];
        }

        if (($lastAntiSlash = strrpos($this->objectname, '\\')) !== false) {
            $namespace = substr($this->objectname, 0, $lastAntiSlash);
            $classname = substr($this->objectname, $lastAntiSlash + 1);
            $classdef .= "namespace $namespace;\n";
        } else {
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