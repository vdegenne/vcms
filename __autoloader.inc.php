<?php
/**
 * Unlike the __autoloaders_strict, this autoload is used to include all
 * files that matches the given class name from the given directory path.
 * The script navigates through all the branchs from INCLUDES_PATH of the
 * framework. This autoload could have some downsides as it could include
 * more than one file having the same class name.
 * However, it is still possible to specify the name of the namespace with the
 * 'use' keyword as to target a specific class of the framework.
 *
 * This script was made as to produce an easy mechanism of class autoload,
 * as it allows to create subdirectories to extend the framework and to
 * organise classes in modules (directories) when the framework became
 * expanded.
 */

use vcms\FileSystem;
require_once __DIR__ . "/FileSystem.class.php";
require_once __DIR__ . '/AutoLoader.class.php';

//function search_class ($className, $path)
//{
//
//    if (file_exists("$path/$className.class.php")) {
//        include_once "$path/$className.class.php";
//    }
//
//    $directories = FileSystem::get_directories($path);
//
//    foreach ($directories as $directory) {
//        search_class($className, "$path/$directory");
//
//    }
//}
//
//
//function vcms_autoload ($classPath) {
//
//    global $Project;
//
//    $namespaces = explode('\\', $classPath);
//    $className = array_pop($namespaces);
//
//    foreach ($Project->get_include_dirpaths() as $path) {
//        search_class($className, $path);
//    }
//}

/**
 * register the autoload function
 * http://php.net/manual/en/function.spl-autoload-register.php
 */
spl_autoload_register('vcms\AutoLoader::vcms_autoload');
