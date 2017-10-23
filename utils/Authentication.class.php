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

        $usersEm = EntityManager::get(
            $Resource->Config->authentication_table,
            $Session->get_user_classname()
        );

        /** @var User $User */
        $User = $usersEm->statement('SELECT * from knowledges.users WHERE email=:email', $email)->fetch();

        if ($User === FALSE) {
            return false;
        }

        $User->isAuthenticated = false;

        if (password_verify($password, $User->getPassword())) {
            $User->setPassword(null);
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