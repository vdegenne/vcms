<?php
namespace vcms\utils;

use vcms\database\Database;
use vcms\resources\VResourceConfig;
use vcms\Session;
use vcms\User;
use vcms\VcmsObject;
use vcms\database\EntityManager;
use vcms\VObject;

class Authentication extends VObject {
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

    function __construct (Database $Database)
    {
        $this->Database = $Database;
    }

    static function create_from_handler (string $db_handler, string $usersTable)
    {
        $A = new Authentication(Database::get_from_handler($db_handler));
        $A->usersTable = $usersTable;

        /* create the users entity manager */
//        $A->usersManager = EntityManager::get($A->usersTable, 'vcms\User');

        return $A;
    }


    /**
     * DEPRECATED, see authenticate function
     * @param $username
     * @param $password
     * @return bool
     */
    function verify ($username, $password): bool
    {
        $sql = <<<SQL
select * from {$this->usersTable}
where email=:email;
SQL;

        /** @var \PDOStatement $s */
        $s = $this->usersManager->get_statement($sql, ['email' => $username]);

        if ($s->rowCount() === 0) return false;

        /** @var User $User */
        $User = $s->fetch();
        $User->isAuthenticated = false;

        if (password_verify($password, $User->get_password())) {
            $this->User = $User;
            $this->User->set_password('');
            $this->User->isAuthenticated = true;
            return true;
        } else {
            return false;
        }
    }


    function authenticate ($email, $password): bool
    {
        global $Session;
        /** @var \vcms\resources\VResource $Resource */
        /** @var Session $Session */
        $sessionUserObject = $Session->get_session_user_object();
        $usersEm = EntityManager::get($this->usersTable, $sessionUserObject);

        $sql = "SELECT * FROM {$this->usersTable} WHERE email=:email;";

        /** @var \PDOStatement $s */
        $s = $usersEm->get_statement($sql, $email);

        if ($s->rowCount() == 0)
            return false;

        /** @var User $User */
        $User = $s->fetch();
        $User->isAuthenticated = false;

        if (password_verify($password, $User->get_password())) {
            $User->set_password(null);
            $User->isAuthenticated = true;
            $this->User = $User;
            $Session->User = $User;
            return true;
        }
        else {
            return false;
        }
    }

}