<?php
namespace vcms;


class AutoLoader {


    static function searchClass (string $classPath, array &$founds = null)
    {
        $className = substr($classPath, strrpos($classPath, '\\') + 1);

        foreach (INCLUDE_DIRPATHS as $path => $null) {
            self::_searchClassRecursive($className, $path, $founds);
        }
    }



    protected static function _searchClassRecursive (
        string $className,
        string $path,
        array &$founds = null)
    {

        $filepath = "$path/$className.class.php";

        if (file_exists($filepath)) {

            if ($founds !== null) {
                $founds[$filepath] = 0;
            } else {
                require_once $filepath;
            }
        }

        $directories = FileSystem::getDirectories($path);

        foreach ($directories as $directory) {
            self::_searchClassRecursive(
                $className,
                "$path/$directory",
                $founds);
        }
    }
}