<?php
declare (strict_types = 1);
namespace vcms\database;

use vcms\VcmsObject;
use Exception;
use vcms\VObject;

class EntityManager extends VObject
{
    
    /** @var Database */
    protected $Database;

    /** @var array */
    protected $placeholders = [];


    /** @var array */
    protected $objectPublicProperties = [];

    
    function __construct (Database $Database, $evalObject = false)
    {

        if (static::TABLE == '') {
            throw new Exception ('the constant TABLE needs to be defined for the manager');
        }


        $this->Database = $Database;

        if ($this::OBJECT !== '' && $evalObject) {
            if ($this->Database === null) {
                throw new Exception('a database must be provided in order to evaluate the object.');
            }
            $this->eval_object();
        }

        /**
         * we save the default public properties of the class (the column names of the table)
         * for the saving functions.
         */
        $this->objectPublicProperties = array_map(function (\ReflectionProperty $p) {
            return $p->name;
        }, (new \ReflectionClass (static::OBJECT))->getProperties(\ReflectionProperty::IS_PUBLIC));

    }


    
    private function eval_object ()
    {
        $classDef = '';
        
        $response = $this->Database->query('SELECT * FROM ' . static::TABLE . ' LIMIT 0;');
        
        for ($i = 0; $i < $response->columnCount(); ++$i) {
            
            $columnMeta = $response->getColumnMeta($i);
            $properties[] = $columnMeta['name'];

        }

        /* if there is at least one antislash, we append the namespace in the class definition */
        if (($lastAntiSlash = strrpos(static::OBJECT, '\\')) >= 0) {
            $namespace = substr(static::OBJECT, 0, $lastAntiSlash);
            $className = substr(static::OBJECT, $lastAntiSlash + 1);
            $classDef .= "namespace $namespace;\n";
        }
        else {
            $className = static::OBJECT;
        }

        $classDef .= "class $className {\n";

        foreach ($properties as $property) {
            $classDef .= "var \$$property;\n";
        }

        $classDef .= '}';
        
        eval($classDef);
    }




    static function create_manager (Database $database,
                                    string $managerName,
                                    string $tablePath,
                                    string $objectPath,
                                    bool $evalObject = true) {

        $definition = '';
        
        if (($lastAntiSlash = strrpos($managerName, '\\')) > 0) {
            $namespace = substr($managerName, 0, $lastAntiSlash);
            $className = substr($managerName, $lastAntiSlash + 1);
            $namespace = trim($namespace, '\\');
            $definition .= "namespace $namespace;\n";
        }
        else {
            $className = $managerName;
        }

        $definition .= "use vcms\database\EntityManager;\n";
        $definition .= "class {$className} extends EntityManager\n";
        $definition .= "{\n";
        $definition .= "    const TABLE = '{$tablePath}';\n";
        $definition .= "    const OBJECT = '{$objectPath}';\n";
        $definition .= "}\n";

        eval($definition);

        return new $managerName($database, $evalObject);
    }




    public function add_placeholder ($key, $value, bool $replace = false)
    {
        if (array_key_exists($key, $this->placeholders) && !$replace) {
            throw new \Exception (
                'the placeholder already exists, set the third argument to "true" if you want to replace it');
        }

        $this->placeholders[$key] = $value;
    }
    
    public function remove_placeholder ($key) : bool {
        
        if (array_key_exists($key, $this->placehoolders)) {
            unset($this->placeholders[$key]);
            return true;
        }

        return false;
    }


    
    function get_statement (String $sql, $placeholders = [], int $fetchMode = \PDO::FETCH_CLASS) : \PDOStatement {
        
        try {
            
            $statement = $this->Database->prepare($sql);

            if ($fetchMode === \PDO::FETCH_CLASS) {
                $statement->setFetchMode(\PDO::FETCH_CLASS, static::OBJECT);
            }
            else {
                $statement->setFetchMode($fetchMode);
            }

            
            if (!is_array($placeholders)) {
                preg_match_all('/:([a-zA-Z]{1}[0-9a-zA-Z_]+)/', $sql, $find);

                /* $find[0] contains all the strings matching
                   all the regular expression of preg_match_all.
                   $find[1] contains all the strings of the 1st
                   parenthesis representing a part of the string
                   matching the regular expression of preg_match_all
                */
                if(count($find[1]) > 1)
                    throw new \Exception('too much placeholders');

                $placeholders = [
                    $find[1][0] => $placeholders
                ];
            }

            $statement->execute($placeholders);

            return $statement;
            
        }
        catch (\PDOException $e) {
            throw new \Exception ($e->getMessage());
        }

        /*
        // we shouldn't pass placeholders if they don't exist
        $placeholders = array_merge ($this->placeholders, $placeholders);
        
        if (empty($placeholders)) {
            $statement->execute ();
        }
        else {
            $statement->execute ($placeholders);
        }
        */
    }


    
    private function persist ($Entity)
    {
        
        /* building the values line
         * the values line is made from the properties of the object the Manager operates for
         * if the id is null, we need to change it to default because it is a "serial" type 
         * in the database */
        $properties = $this->objectPublicProperties;

        if (($indexOfID = array_search('id', $properties)) !== false) {
            unset($properties[$indexOfID]);
        }

        /* build the sql string */
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) RETURNING *;',
            static::TABLE,
            implode(', ', $properties),
            ':' . implode (', :', $properties)
        );

        
        $placeholders = [];
        foreach ($this->objectPublicProperties as $prop) {

            if ($prop === 'id')
                continue;
            
            $placeholders[$prop] = $Entity->{$prop};
        }

        try {
            $statement = $this->get_statement ($sql, $placeholders);
        }
        catch (\PDOException $e) {
            throw $e;
        }

        /* if everything proceeded well, the sql returns the inserted row with the new id */
        $inserted = $statement->fetch();

        $Entity->id = $inserted->id;
    }

    private function merge ($Entity)
    {
        
    }
    
    public function save ($Entity, bool $forceSaving = false)
    {
        /* first we verify $Entity's class is of the same class the Manager operates */
        if (get_class($Entity) !== static::OBJECT) {
            throw new \Exception (
                'the $Entity argument needs to be of the same class as the class the Manager operates');
        }
        
        /* save or update the object if it exists in the database (based on the id) */
        /* or force saving if the user wants to insert an item with personalised id */
        
        if ($Entity->id === null || $forceSaving) { // the Entity needs to be saved
            $this->persist($Entity);
        }
        else {
            $this->merge($Entity);
        }
    }
}
