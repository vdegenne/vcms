<?php
namespace vcms\database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use vcms\AutoLoader;
use vcms\VObject;
use vcms\VString;


class EntityManager {

    static protected $singletons;

    /**
     * @var Database
     */
    protected $Database;

    /**
     * The name of the table (schema and name).
     * Mainly used in the requests.
     * @var string
     */
    public $fulltablename;

    /**
     * @var string
     */
    public $schema;

    /**
     * @var string
     */
    public $tablename;

    /**
     * @var string
     */
    public $primarykey;

    public $objectname;

    protected $tableColumns;






    static function get (string $tablename,
                         string $objectname = null,
                         Database $Database = null,
                         bool $preventEval = false): EntityManager {
        $calledClass = get_called_class();

        if (isset(self::$singletons[$calledClass])
            && isset(self::$singletons[$calledClass][$tablename])
            && isset(self::$singletons[$calledClass][$tablename][$objectname])
        ) {
            return self::$singletons[$calledClass][$tablename][$objectname];
        }

        /** @var EntityManager $em */
        /* creating the singleton */
        $em = new $calledClass();

        if ($Database === null) {
            global $Database;
            if ($Database === null) {
                trigger_error('Needs a database object to make an EntityManager.', E_USER_ERROR);
            }
        }
        $em->Database = $Database;


        $em->fulltablename = $tablename;
        /* schema */
        if (($dotpos = strpos($tablename, '.')) !== FALSE) {
            $em->schema = substr($tablename, 0, $dotpos);
            $em->tablename = substr($tablename, $dotpos + 1);
        } else {
            // $em->tablename = $tablename;
            trigger_error(
                'Can\'t create the EntityManager without a schema. (Do you need to use "public" ?)',
                E_USER_ERROR);
        }

        self::resolveTableColumns($em);
        self::resolvePrimaryKey($em);

        /** If no object were specified, we try to resolve a name */
        if ($objectname === null) {
            $pieces = explode('.', $tablename);
            $lastPiece = array_pop($pieces);
            if ($lastPiece[strlen($lastPiece) - 1] === 's') {
                $lastPiece = substr($lastPiece, 0, -1);
            }
            $lastPiece = VString::ToCamelCase($lastPiece, '_');
            $pieces[] = $lastPiece;
            $objectname = implode('\\', $pieces);
        }
        $em->objectname = $objectname;

        /**
         * We use the autoloader search function to find files
         * associated with the entity name.
         * true means "do not include if files are found"
         */
        if (!$preventEval) {

            $justSearching = [];
            AutoLoader::searchClass($objectname, $justSearching);

            if (empty($justSearching)) {
                self::evalObject($em);
            }
        }

        self::$singletons[$calledClass][$tablename][$objectname] = $em;
        return $em;
    }




    function query (string $sql,
                    int $mode = PDO::FETCH_CLASS,
                    $arg3 = null,
                    array $ctorargs = array()): PDOStatement
    {

        $this->setSearchPath();

        if ($arg3 === null) {
            $arg3 = $this->objectname;
        }
        return $this->Database->query($sql, $mode, $arg3, $ctorargs);
    }



    /**
     * Alias for get_statement. get_statement function name is a bit confusing (keeping
     * the name for backward-compatibility).
     */
    function statement (string $sql, $placeholders = [], int $fetchMode = PDO::FETCH_CLASS, string $fetchObject = null): PDOStatement {
        return $this->get_statement($sql, $placeholders, $fetchMode, $fetchObject);
    }

    function get_statement (string $sql,
                            $placeholders = [],
                            int $fetchMode = PDO::FETCH_CLASS,
                            string $fetchObject = null): PDOStatement {

        return $this->execute(
            $this->prepare($sql, $fetchMode, $fetchObject),
            $placeholders);
    }





    function prepare (string $sql,
                      int $fetchmode = PDO::FETCH_CLASS,
                      string $fetchObject = null): PDOStatement {

//        self::bindTableToSql($sql, $this->fulltablename);
        $statement = $this->Database->prepare($sql);

        if ($fetchmode === 8) {
            $objectname = $this->objectname;
            $fetchObject !== null && $objectname = $fetchObject;
            $statement->setFetchMode(8, $objectname);
        } else {
            $statement->setFetchMode($fetchmode);
        }

        return $statement;
    }


    function execute (PDOStatement $statement, $placeholders = []): PDOStatement {

        $this->setSearchPath();

        /* If the placeholder is a string */
        if (!is_array($placeholders)) {
            if (preg_match_all('/:([a-zA-Z]{1}[0-9a-zA-Z_]+)/', $statement->queryString, $matches)) {
                if (count($matches[1]) > 1) {
                    throw new Exception('too much placeholders');
                }
                $placeholders = [$matches[1][0] => $placeholders];
            } else {
                $placeholders = [$placeholders];
            }
        }

        /* array to string conversion */
        foreach ($placeholders as &$p) {
            if (gettype($p) === 'array') {
                $p = '{' . join(',', array_keys($p)) . '}';
            }
        }

        $statement->execute($placeholders);
        return $statement;
    }


    function hasRows (string $query): bool {
        return
            intval($this->query("SELECT EXISTS($query) AS query")->fetchColumn())
                ? true
                : false;
    }




    function setSearchPath () {
        $this->Database->setSearchPath($this->schema);
    }









    static function resolvePrimaryKey (EntityManager $manager) {
        $manager->primarykey = $manager->Database->query(
            'SELECT a.attname--, format_type(a.atttypid, a.atttypmod) AS data_type
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid
                                AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = \'' . $manager->fulltablename . '\'::REGCLASS
            AND i.indisprimary'
        )->fetchColumn();

        if ($manager->primarykey === false) {
            trigger_error(
                'Couldn\'t find the primary key for the EntityManager.',
                E_USER_ERROR);
        }
    }



    protected static function resolveTableColumns (EntityManager $manager) {
        $manager->tableColumns = $manager->Database->query(
            "SELECT *
             FROM information_schema.columns
             WHERE table_schema='$manager->schema'
               AND table_name='$manager->tablename'"
        )->fetchAll(PDO::FETCH_ASSOC);
    }










    function getEntityFromId (int $entity_id, ...$fields) {
        $sql = "SELECT *
                FROM {$this->fulltablename}
                WHERE {$this->primarykey}={$entity_id}";

        $entity = $this->query($sql)->fetch();

        return ($entity !== FALSE) ? $entity : null;
    }





    function saveEntity (DatabaseEntity &$entity)
    {
        $primarykey = $this->primarykey;

        if (isset($entity->$primarykey)
            && ($existing = $this->getEntityFromId($entity->$primarykey)) !== null
        ) {
            $this->persist($entity, $existing);
        }
        else {
            $this->insertEntity($entity);
        }
    }


    /**
     * @param DatabaseEntity $entity
     * @return mixed
     */
    function insertEntity (DatabaseEntity &$entity)
    {
        foreach ($this->tableColumns as $column) {
            if ($column['is_nullable'] === 'NO') {
                $notNullables[$column['column_name']] = 0;
            } else {
                $nullables[$column['column_name']] = 0;
            }
            if (isset($column['column_default'])) {
                $hasDefault[$column['column_name']] = 0;
            }
        }

        foreach (get_object_vars($entity) as $prop => $value) {

            if (isset($notNullables[$prop])) {
                if ($value === null) {
                    if (!isset($hasDefault[$prop])) {
                        trigger_error(
                            'You need to set "' . $notnullname . '" to insert the entity in the database.',
                            E_USER_ERROR);
                    }
                }
                $values[$prop] = $value;
                unset($notNullables[$prop]);
            } elseif (isset($nullables[$prop])) {
                $values[$prop] = $value;
            }
        }

        foreach ($notNullables as $notnullname => $zero) {
            if (!isset($hasDefault[$notnullname])) {
                trigger_error(
                    'You need to set "' . $notnullname . '" to insert the entity in the database.',
                    E_USER_ERROR);
            }
        }


        $sql = 'INSERT INTO ' . $this->fulltablename .
            ' (' . join(',', array_keys($values)) . ')' .
            ' VALUES' .
            ' (:' . join(',:', array_keys($values)) . ')' .
            ' RETURNING *';


        // array to string conversion
        foreach ($values as &$v) {
            if (gettype($v) === 'array') {
                $v = '{' . join(',', $v) . '}';
            }
        }

        $stmt = $this->statement($sql, $values);
        if (($fetch = $stmt->fetch()) === FALSE) {
            trigger_error('Couldn\'t insert the entity', E_USER_ERROR);
        }

        $entity = $fetch;
    }




    function persist (DatabaseEntity &$Entity, DatabaseEntity $existingEntity = null)
    {
        if ($existingEntity === null) {
            $this->saveEntity($Entity); // round-about
        }

        /* we should only update the changed informations */
        $trackedVars = $Entity->trackedVars;
        $alteredAttrs = array_filter($trackedVars, function ($v) use ($Entity, $existingEntity) {
            return $Entity->$v !== $existingEntity->$v;
        });

        if (empty($alteredAttrs)) {
            return [
                'code' => 201,
                'message' => 'nothing to save.',
                'data' => []
            ];
        }

        $setValues = join(', ',
            array_map(function ($a) {
                return "$a=:$a";
            }, $alteredAttrs));

        $setPlaceholders = [];
        foreach ($alteredAttrs as $alteredAttr) {
            $setPlaceholders[$alteredAttr] = $Entity->$alteredAttr;
        }

        $sql = "
UPDATE $this->fulltablename
SET $setValues
WHERE $this->primaryKey=:entity_id
RETURNING *;
";
        $s = $this->get_statement(
            $sql,
            array_merge($setPlaceholders, ['entity_id' => $existingEntity->{$this->primaryKey}])
        );

        $Entity->reset_tracked_vars();

        return [
            'code' => 100,
            'message' => 'updated',
            'data' => $s->fetch()
        ];
    }


    function delete_entity (DatabaseEntity $Entity) {
        if ($Entity->{$this->primaryKey} === null) {
            throw new Exception('"The entity has no identifier."');
        }


        $sql = "
DELETE FROM $this->fulltablename
WHERE $this->primaryKey=:entity_id
RETURNING *;
";
        $removed = $this->get_statement($sql, $Entity->{$this->primaryKey})->fetch();

        if ($removed === FALSE) {
            $code = 101;
            $message = 'nothing to delete';
        } else {
            $code = 100;
            $message = 'deleted';
            $data = $removed;
        }

        return [$code, $message, $data];
    }


    function beginTransaction () {
        $this->Database->beginTransaction();
    }

    function commit () {
        $this->Database->commit();
    }

    function rollback () {
        $this->Database->rollBack();
    }




    static function evalObject (EntityManager $em) {
        $classdef = '';

        // uncomment and change if you need properties evaluation
        //        $response = $this->Database->query('select * from '.$this->fulltablename.' limit 0');
        //
        //        for ($i = 0; $i < $response->columnCount(); ++$i) {
        //            $columnMeta = $response->getColumnMeta($i);
        //            $properties[] = $columnMeta['name'];
        //        }

        if (($lastAntiSlash = strrpos($em->objectname, '\\')) !== false) {
            $namespace = substr($em->objectname, 0, $lastAntiSlash);
            $classname = substr($em->objectname, $lastAntiSlash + 1);
            $classdef .= 'namespace ' . $namespace . ';';
        } else {
            $classname = $em->objectname;
        }

        $classdef .= 'class ' . $classname . ' extends \\vcms\database\DatabaseEntity {}';

        eval($classdef);

    }




    static function bindTableToSql (string &$sql, string $tablename) {
        /* UPDATE NOT IMPLEMENTED */
        if (preg_match('/^\s*(UPDATE|INSERT)/i', $sql)) {
            //            return $sql;
        } else {
            self::addFromStatement($sql, "FROM $tablename AS obj");
        }
    }

    static function addFromStatement (string &$sql, string $fromStmt)
    {
        preg_match('/FROM|WHERE|NATURAL|INNER|JOIN|ORDER|GROUP/i', $sql, $match);

        if (count($match)) {
            if ($frompos >= 0) {
                $sql = substr($sql, 0, $frompos) . "$fromStmt " . substr($sql, $frompos);
            } elseif ($frompos === -1 && $beforeKeyword !== 'from') {
                $sql = $sql . " $fromStmt";
            }
        }
    }

    static function fromPosition (string $sql, string &$beforeKeyword = null) {

        if (count($match)) {
            $beforeKeyword = strtolower($match[0]);
            return ($beforeKeyword === 'from') ? -1 : strpos($sql, $match[0]);
        }
        return -1;
    }

}