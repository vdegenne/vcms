<?php
namespace vdegenne;


class DatabaseManager
{
    
    /** @var Database */
    protected $DB; // Database
  
    /** @var array */
    protected $placeholders = [];


    private function __construct(Database $Database) {
        $this->DB = $Database;
    }

    public static function get(Database $Database = null, $schema = null)
    {
        /* get the name of the inherited class who called the function */
        $childClass = get_called_class();

        if ($schema === null) {
            throw new Exception('the Manager needs a schema name');
        }
        
        if (static::$DatabaseManager === null) {

            if ($Database === null) {
                throw new Exception ('no database instance');
            }

            static::$DatabaseManager = new $childClass($Database);
            static::$DatabaseManager->add_placeholder('schema', $schema);
        }

    
        return static::$DatabaseManager;
    }


    public function query($sql, array $placeholders = null, $fetchMode = \PDO::FETCH_ASSOC) {
        try {
            $s = $this->DB->prepare($sql);
            if ($fetchMode === \PDO::FETCH_CLASS)
                $s->setFetchMode(\PDO::FETCH_CLASS, static::OBJECT);
            else
                $s->setFetchMode($fetchMode);
        } catch (\PDOException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR); // change to throw new ?
        }
        is_null($placeholders) ? $s->execute() : $s->execute($placeholders);
        return $s;
    }

    public function queryObject($sql, array $placeholders = null) {
        return $this->query($sql, $placeholders, \PDO::FETCH_CLASS);
    }


    public function add_placeholder($k, $v) {
        $this->placeholders[$k] = $v;
    }

    public function placeholds($str) {
        $placeholders = [];
        foreach ($this->placeholders as $k => $v) {
            if (preg_match("/:$k/", $str)) {
                $placeholders[$k] = $v;
            }
        }

        return $placeholders;
    }

    /**
     * Same as above but better name
     *
     * @param $str
     * @return array
     */
    function get_required_placeholders ($str) {
        $placeholders = [];
        foreach ($this->placeholders as $k => $v) {
            if (preg_match("/:$k/", $str)) {
                $placeholders[$k] = $v;
            }
        }
        return $placeholders;
    }


    /**
     * @param string $SQL The sql containing the placeholder to replace
     * @param string $pName Placeholder's name
     * @param array $values Values of the list
     * @return array|bool A list ($newSQL, $placeholder) or FALSE if the placeholder was not found.
     */
    function replace_placeholder_list ($SQL, $pName, Array $values) {
        if (($pPos = strpos($SQL, ":$pName")) === false) { return false; }

        $pNameLength = strlen($pName);

        $pListNames = $pList = [];
        $i = 0;
        foreach ($values as $v) {
            $pListNames[] = ":$pName$i";
            $pList[":$pName$i"] = $v;
            $i++;
        }

        $modifiedSQL =  substr($SQL, 0, $pPos) .
            implode(',', $pListNames) .
            substr($SQL, $pPos + ($pNameLength + 1));

        return [$modifiedSQL, $pList];
    }


    function __set ($k, $v) {
        if (array_key_exists($k, get_object_vars($this))) {
            $this->{$k} = $v;
        }
    }
    function __get ($name) {
        return $this->{$name};
    }
}