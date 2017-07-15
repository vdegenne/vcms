<?php

/*
 * Copyright (C) 2015 Valentin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Le fichier "bootstrap.php" a pour but de fournir des variables utiles pour
 * les sites-web, des variables sur les informations des chemins et éventu-
 * ellement sur le serveur.
 *
 * Il est donc importé en en-tête du fichier content.php principal du site-web
 * concerné, et modifié en conséquences selon les paramètres de la plateforme
 * qui l'héberge.
 */

use vdegenne\FileSystem;
use vdegenne\Projet;
use vdegenne\Request;
use vdegenne\Website;

require_once 'FileSystem.class.php';


define('DS', DIRECTORY_SEPARATOR);



/**
 * __autoload
 *
 * Quand "bootstrap.php" est inclu dans une page web, il devient possible de
 * charger n'importe quels objets (classes) de la framework sans devoir spécifier
 * le chemin complet vers le dossier des includes.
 * Il en va de même avec les classes spécifiques du projet présentes dans le même
 * dossier des includes de la framework.
 *
 *
 * @param string $classPath l'identifiant de la classe à importer.
 */
function __autoload ($classPath) {

    // ici on gère l'éventuel namespace.
    $className = explode('\\', $classPath);
    $className = $className[count($className) - 1];

    // variable utilisée pour repérer les doubles inclusions.
    $classExists = false;

    // inclusion de la classe de la framework
    if (file_exists(
        FRAMEWORK::$INCLUDES_PATH . DS . "$className.class.php"
    )) {
        include FRAMEWORK::$INCLUDES_PATH . DS . "$className.class.php";
        $classExists = true;
    }


    $projectIncludesPath = FRAMEWORK::$INCLUDES_PATH . DS . FRAMEWORK::$PROJECT_NAME;
    /*
     * tentative d'inclusion de la classe spécifique au projet.
     * Il peut arriver que le dossier des includes du projet possède des
     * sous-dossiers, on doit alors faire en sorte de cherche récursivement dans
     * les sous-dossiers.
     */

    if (_autoload_subdirs($className, $projectIncludesPath)) {

        if ($classExists) {
            file_put_contents(
                'log_warnings.txt',
                'double inclusion de classes entre la framework et le projet ' .
                FRAMEWORK::$PROJECT_NAME . '.'
            );
        }
    }
}

/**
 * Permet de chercher et d'inclure une classe présente dans l'arborescence depuis
 * $dir.
 *
 * @param string $className le nom de la classe, sans le namespace donc.
 * @param string $dir       Le chemin absolu vers le dossier dans lequel on souhaite
 *                          rechercher
 *
 * @return bool true si la classe a été trouvée.
 * @throws ErrorException
 */
function _autoload_subdirs ($className, $dir) {

    if (file_exists($dir . DS . "$className.class.php")) {
        include $dir . DS . "$className.class.php";

        return true;
    }
    else {

        // on récupère les dossiers du dossier actuel.
        $dirs = FileSystem::get_directories($dir);

        foreach ($dirs as $d) {
            $found = _autoload_subdirs($className, $dir . DS . $d);

            if ($found) {
                return true;
            }
        }
    }

    return false;
}


/**
 * Classe représentant les configurations de la framework.
 * Elle peut être utilisée pour changer des paramètres spécifiques au projet.
 *
 * @author Degenne Valentin
 */
class FRAMEWORK {

    static $SUPER_ROOT = 'C:/localweb';
    static $INCLUDES_FOLDER_NAME = 'includes';
    static $INCLUDES_PATH;
    static $SCRIPTS_LOCATION_ROOT;
    static $SCRIPTS_EXTENSION = 'script.php';
    static $PROJECT_PATH;
    static $PROJECT_RELATIVE_PATH;
    static $PROJECT_NAME;

    static $LAYOUTS_FOLDER_NAME = 'layouts';
    static $LAYOUTS_PATH;

}

FRAMEWORK::$INCLUDES_PATH
    = FRAMEWORK::$SUPER_ROOT . DS . FRAMEWORK::$INCLUDES_FOLDER_NAME;
FRAMEWORK::$SCRIPTS_LOCATION_ROOT = FRAMEWORK::$SUPER_ROOT . DS . 'scripts';
FRAMEWORK::$PROJECT_PATH = $_SERVER['DOCUMENT_ROOT'];
FRAMEWORK::$PROJECT_RELATIVE_PATH
    = substr(FRAMEWORK::$PROJECT_PATH, strlen(FRAMEWORK::$SUPER_ROOT) + 1);
FRAMEWORK::$PROJECT_NAME = basename($_SERVER['DOCUMENT_ROOT']);
FRAMEWORK::$LAYOUTS_PATH
    = FRAMEWORK::$INCLUDES_PATH . DS . FRAMEWORK::$LAYOUTS_FOLDER_NAME;








$WEBSITE = Website::get();
$WEBSITE->set_domainName($_SERVER['SERVER_NAME']);


$REQUEST = Request::get();
$QUERY_STRING = $REQUEST->get_QueryString();


if (!$QUERY_STRING->has('url')) {
    exit('l\'argument \'requested_page\' n\'a pas été défini.');
}

$REQUEST->set_url(
    $QUERY_STRING->get('requested_page')
);


$QUERY_STRING->del_argument('requested_page');

$WEBSITE->set_request($REQUEST);

$PAGE = $REQUEST->make_page($WEBSITE);
$PAGE->load_metadatas();

