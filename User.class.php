<?php
namespace vcms;

use vcms\database\DatabaseEntity;
use JsonSerializable;


class User extends DatabaseEntity
    implements JsonSerializable
{
    public $user_id;

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