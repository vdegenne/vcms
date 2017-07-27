<?php
namespace vcms;

use JsonSerializable;

class User extends VObject
    implements JsonSerializable
{
    protected $user_id;

    protected $email;
    protected $password;

    public $isAuthenticated;

    function jsonSerialize () {
        return get_object_vars($this);
    }

}