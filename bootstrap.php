<?php
namespace vcms;

use vcms\resources\FeedbackResource;
use vcms\database\Credential;
use vcms\database\Database;

/**
 * Initialising some variables as inputs for
 * starting the bootstrap.
 */
define('PROJECT_LOCATION', dirname(getcwd()));
define('INCLUDE_DIRPATHS', [
    __DIR__ => null,
    PROJECT_LOCATION . '/includes' => null
]);

/**
 * Registring the autoloader as soon as possible
 * So we can start using dynamic imports.
 */
require_once __DIR__ . '/__autoloader.inc.php';

/* used to check the execution time */
Analyzer::start();

/**
 * Initializing the project.
 */
$Project = Project::get();


/* error handling functions */
require_once __DIR__ . '/scripts/error_handling.inc.php';
require_once __DIR__ . '/utils/vutils.inc.php';



/***
 * turning off error_reporting only works from here
 * why ?
 */
// error_reporting(0);



/**************************
 * REQUEST
 **************************/
$Request = Request::generate_http_request();
$Resource = $Request->associatedResource;

$QueryString = $Request->QueryString;
$qs = $Request->QueryString;


/**************************
 * DATABASE
 **************************/
Credential::$search_in = [__DIR__, PROJECT_LOCATION];
if ($Project->db_credentials_search_paths !== null) {
    Credential::$search_in = array_unique(array_merge(
        Credential::$search_in, $Project->db_credentials_search_paths
    ));
}

$Database = null;
if ($Resource->Config->needs_database) {
    $Database = Database::get_from_handler($Resource->Config->database);
}



/**************************
 * SESSION
 **************************/
$Session = new Session();

if (!isset($Session->User)) {
    $userClassname = $Session->get_user_classname();

    if ($userClassname !== 'vcms\\User' && !is_subclass_of($userClassname, 'vcms\\User')) {
        throw new \Exception('The custom user class needs to be a subclass of vcms\\User');
    }
    $Session->User = new $userClassname();
}
$User = $Session->User;


/**************************
 * CUSTOM BOOTSTRAP
 **************************/
if (file_exists(PROJECT_LOCATION . '/includes/bootstrap.php')) {
    include PROJECT_LOCATION . '/includes/bootstrap.php';
}



/**************************
 * RESPONSE
 **************************/
try {
    $Request->request(); // process the response
    header('content-type: ' . $Request->response['mimetype']);
    echo($Request->response['content']);
}
catch (\PDOException $e) { // PDOException are important to catch
    throw $e;
    trigger_error($e->getMessage(), E_USER_ERROR);
}