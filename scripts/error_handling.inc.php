<?php
namespace vcms;


function fatal_handler ()
{
    $error = error_get_last();

    if ($error !== null) {
        echo 'aborting from fatal_handler (' . $error['message'] . ')' . "\n";
    }
}


function error_handler ($errno, $errstr, $errfile, $errline)
{
    if ($errno === 2) {
        return;
    }
    echo 'aborting from error_handler (in ' . $errfile . ' ('. $errline . '))' . "<br>\n";
    echo $errstr . '(code: '.$errno.')';
    exit(1);
}


$oldErrorHandler = set_error_handler("vcms\\error_handler");
register_shutdown_function( "vcms\\fatal_handler" );