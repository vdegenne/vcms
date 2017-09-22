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
     * The User Object if the authentication succeed.
     * @var User
     */
    static $User;



    static function authenticate ($email, $password): bool
    {
        global $Resource, $Session;

        $sessionUserObject = $Session->get_user_classname();
        $authTable = $Resource->authentication_table;

        $usersEm = EntityManager::get($authTable, $sessionUserObject);

        /** @var \PDOStatement $s */
        $s = $usersEm->get_statement('SELECT * WHERE email=:email', $email);

        if ($s->rowCount() == 0)
            return false;

        /** @var User $User */
        $User = $s->fetch();
        $User->isAuthenticated = false;

        if (password_verify($password, $User->get_password())) {
            $User->set_password(null);
            $User->isAuthenticated = true;
            self::$User = $User;
            $Session->User = $User;
            return true;
        }
        else {
            return false;
        }
    }

}