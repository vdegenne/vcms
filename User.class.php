<?php
namespace vcms;

use vcms\database\DatabaseEntity;
use JsonSerializable;


class User extends DatabaseEntity
    implements JsonSerializable
{
    public $user_id;

    /**
     * Type of the user.
     * 0 : anonymous, 1: admin, 2: master, 3: slave
     * @var int
     */
    public $type = 0;
    public $username;
    protected $email;
    protected $password;

    public $isAuthenticated = false;


    function get_password () {
        return $this->password;
    }
    function set_password ($password) {
        $this->password = $password;
    }
    function jsonSerialize () {
        return get_object_vars($this);
    }
}