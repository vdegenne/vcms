<?php
namespace vcms;


class RequestFactory {

    static function create_request (string $uri, string $method = 'GET'): Request
    {
        return new Request($uri, $method);
    }
}