<?php
namespace vcms\utils;

use vcms\database\Database;
use vcms\User;
use vcms\VcmsObject;
use vcms\database\EntityManager;

class Authentication extends VcmsObject
{
    /**
     * The Database Object used for the authentication.
     * @var Database
     */
    protected $Database;

    /**
     * Table used for the authentication.
     * @var string
     */
    protected $usersTable;

    /**
     * The User Object if the authentication succeed.
     * @var User
     */
    protected $User;

    /**
     * @var UsersManager
     */
    protected $usersManager;

    static function create_from_handler (string $db_handler, string $usersTable)
    {
        $A = new Authentication(Database::get_from_handler($db_handler));
        $A->usersTable = $usersTable;

        /* create the users entity manager */
        $A->usersManager = EntityManager::create_manager(
            $A->Database,
            'vcms\UsersManager',
            $A->usersTable,
            'vcms\User',
            false
        );

        return $A;
    }

    function __construct (Database $Database)
    {
        $this->Database = $Database;
    }


    function verify ($username, $password): bool
    {
        $sql = "
                select * from {$this->usersTable}
                where username=:username;
        ";

        /** @var \PDOStatement $s */
        $s = $this->usersManager->get_statement($sql, ['username' => $username]);

        if ($s->rowCount() === 0) return false;

        $User = $s->fetch();
        $User->isAuthenticated = false;

        if (password_verify($password, $User->password)) {
            $this->User = $User;
            $this->User->password = '';
            $this->User->isAuthenticated = true;
            return true;
        }
        else {
            return false;
        }
    }

}