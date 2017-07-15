<?php

require 'FileSystem.class.php';

use vdegenne\FileSystem;

/**
 * __autoload
 * Quand "bootstrap.php" est inclu dans une page web, il devient possible de
 * charger n'importe quels objets (classes) de la framework sans devoir spécifier
 * le chemin complet vers le dossier des includes.
 * Il en va de même avec les classes spécifiques du projet présentes dans le même
 * dossier des includes de la framework.
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
        INCLUDES_PATH . "/$className.class.php"
    )) {
        include INCLUDES_PATH . "/$className.class.php";
        $classExists = true;
    }


    $projectIncludesPath = INCLUDES_PATH . '/' . PROJECT_NAME;
    /*
    * tentative d'inclusion de la classe spécifique au projet.
    * Il peut arriver que le dossier des includes du projet possède des
    * sous-dossiers, on doit alors faire en sorte de cherche récursivement dans
    * les sous-dossiers.
    */

    if (is_dir($projectIncludesPath) && _autoload_subdirs($className, $projectIncludesPath)) {

        if ($classExists) {
            file_put_contents(
                'log_warnings.txt',
                'double inclusion de classes entre la framework et le projet ' . PROJECT_PATH . '.'
            );
        }
    }
}

/**
 * Permet de chercher et d'inclure une classe présente dans l'arborescence depuis
 * $dir.
 * @param string $className le nom de la classe, sans le namespace donc.
 * @param string $dir       Le chemin absolu vers le dossier dans lequel on souhaite
 *                          rechercher
 * @return bool true si la classe a été trouvée.
 * @throws ErrorException
 */
function _autoload_subdirs ($className, $dir) {

    if (file_exists("$dir/$className.class.php")) {
        include "$dir/$className.class.php";

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