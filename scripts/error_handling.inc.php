<?php

register_shutdown_function( "fatal_handler" );

function fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        error_mail(format_error( $errno, $errstr, $errfile, $errline));
    }
}

// Gestionnaire d'erreurs
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // Ce code d'erreur n'est pas inclus dans error_reporting()
        return;
    }

    switch ($errno) {
        case E_USER_ERROR:
//            echo "<b>Mon ERREUR</b> [$errno] $errstr<br />\n";
//            echo "  Erreur fatale sur la ligne $errline dans le fichier $errfile";
//            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
//            echo "Arrêt...<br />\n";
            exit(1);
            break;

        case E_USER_WARNING:
//            echo "<b>Mon ALERTE</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
//            echo "<b>Mon AVERTISSEMENT</b> [$errno] $errstr<br />\n";
            break;

        default:
//            echo "Type d'erreur inconnu : [$errno] $errstr<br />\n";
            break;
    }

    /* Ne pas exécuter le gestionnaire interne de PHP */
    return true;
}
$old_error_handler = set_error_handler("myErrorHandler");