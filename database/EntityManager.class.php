<?php
namespace vcms\database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use vcms\AutoLoader;
use vcms\VObject;
use vcms\VString;


class EntityManager extends VObject {

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
    public $primaryKey;

    public $objectname;

    protected $tableFields;



    /*************
     * Functions
     *************/

    static function get (string $tablename,
                         string $objectname = null,
                         Database $Database = null): EntityManager
    {
        $calledClass = get_called_class();

        if (isset(self::$singletons[$calledClass])
            && isset(self::$singletons[$calledClass][$tablename])
            && isset(self::$singletons[$calledClass][$tablename][$objectname])) {
            return self::$singletons[$calledClass][$tablename][$objectname];
        }

        /** @var EntityManager $Em */
        /* creating the singleton */
        $Em = new $calledClass();

        if ($Database === null) {
            global $Database;
            if ($Database === null) {
                throw new Exception('Needs a Database Object.');
            }
        }

        $Em->Database = $Database;
        $Em->fulltablename = $tablename;
        /* schema */
        if (($dotpos = strpos($tablename, '.')) !== FALSE) {
            $Em->schema = substr($tablename, 0, $dotpos);
            $Em->tablename = substr($tablename, $dotpos + 1);
        }
        else {
            $Em->tablename = $tablename;
        }

        $Em->tableFields = $Em->get_table_fields();
        $Em->primaryKey = $Em->resolve_primary_key();

        /** If no object were specified, we try to resolve a name */
        if ($objectname === null) {
            $pieces = explode('.', $tablename);
            $lastPiece = array_pop($pieces);
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

        self::$singletons[$calledClass][$tablename][$objectname] = $Em;
        return $Em;
    }

    function resolve_primary_key ()
    {
        $sql = "
SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
FROM   pg_index i
JOIN   pg_attribute a ON a.attrelid = i.indrelid
                        AND a.attnum = ANY(i.indkey)
WHERE  i.indrelid = '$this->fulltablename'::regclass
AND    i.indisprimary;
";

        $s = $this->Database->query($sql);
        $fetch = $s->fetch();
        if (!isset($fetch['attname'])) {
            throw new Exception('Couldn\'t find the primary key.');
        }
        return $fetch['attname'];
    }
    function get_table_fields (): array
    {
        $sql = "
SELECT *
FROM information_schema.columns
WHERE table_schema=:table_schema
AND table_name=:table_name;
";

        $s = $this->get_statement(
            $sql,
            ['table_schema' => $this->schema, 'table_name' => $this->tablename],
            8, // 8 = FETCH_CLASS
            'StdClass'
        );

        return $s->fetchAll();
    }


    function get_entity_from_id (int $entity_id)
    {
        $sql = "
SELECT *
FROM $this->fulltablename
WHERE $this->primaryKey=:entity_id;
";

        $Entity = $this->get_statement($sql, $entity_id)->fetch();
        if ($Entity === FALSE) {
            $Entity = null;
        }
        return $Entity;
    }

    function save_entity (DatabaseEntity $Entity)
    {
        if (($existingEntity = $this->get_entity_from_id($Entity->{$this->primaryKey})) !== null) {
            return $this->persist($Entity, $existingEntity);
        }
        else {
            /* save */
        }
    }

    function persist (DatabaseEntity $Entity, DatabaseEntity $existingEntity = null)
    {
        if ($existingEntity === null) {
            return $this->save_entity($Entity); // roundabout
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
            array_map(function ($a) { return "$a=:$a"; }, $alteredAttrs));

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


    function delete_entity (DatabaseEntity $Entity)
    {
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
        }
        else {
            $code = 100;
            $message = 'deleted';
            $data = $removed;
        }

        return [$code, $message, $data];
    }





    /******************************
     * @param string $SQL
     * @param array $placeholders
     * @param int $fetchMode
     * @param string|null $objectname
     * @return PDOStatement
     * @throws Exception
     ******************************/
    function get_statement (string $SQL,
                            $placeholders = [],
                            int $fetchMode = PDO::FETCH_CLASS,
                            string $objectname = null): PDOStatement
    {

        $SQL = self::bind_table_to_sql($SQL, $this->fulltablename);

        try {
            /** @var PDOStatement $statement */
            $statement = $this->Database->prepare($SQL);

            if ($fetchMode === PDO::FETCH_CLASS) {
                if ($objectname === null) {
                    $objectname = $this->objectname;
                }
                $statement->setFetchMode(PDO::FETCH_CLASS, $objectname);
            } else {
                $statement->setFetchMode($fetchMode);
            }

            /* If the placeholder is a string */
            if (!is_array($placeholders)) {
                preg_match_all('/:([a-zA-Z]{1}[0-9a-zA-Z_]+)/', $SQL, $find);
                if (count($find[1]) > 1) {
                    throw new Exception('too much placeholders');
                }

                $placeholders = [$find[1][0] => $placeholders];
            }

            $statement->execute($placeholders);
            return $statement;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }



    /******************************
     *
     ******************************/
    function eval_object ()
    {
        $classdef = '';

        $response = $this->Database->query("select * from {$this->fulltablename} limit 0");

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

        $classdef .= "class $classname extends \\vcms\database\DatabaseEntity {}";

        eval($classdef);

    }



    static function from_position (string $sql, string &$beforeKeyword = null): int
    {
        preg_match('/FROM|WHERE|NATURAL|INNER|JOIN|ORDER|GROUP/i', $sql, $match);
        if (count($match) !== 0) {
            $beforeKeyword = strtolower($match[0]);
            return ($beforeKeyword === 'from') ? -1 : strpos($sql, $match[0]);
        }
        return -1;
    }

    static function add_from_statement (string $sql, string $fromStmt) : string
    {
        $frompos = static::from_position($sql, $beforeKeyword);
        if ($frompos >= 0) {
            return substr($sql, 0, $frompos) . "$fromStmt " . substr($sql, $frompos);
        }
        elseif ($frompos === -1 && $beforeKeyword !== 'from') {
            return $sql . " $fromStmt";
        }
        else {
            return $sql;
        }
    }

    static function bind_table_to_sql (string $sql, string $tablename) : string
    {
        /* UPDATE NOT IMPLEMENTED */
        if (preg_match('/^\s*UPDATE/i', $sql)) {
            return $sql;
        }
        else {
            return self::add_from_statement($sql, 'FROM ' . $tablename);
        }
    }
}