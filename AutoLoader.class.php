<?php

namespace vcms;

class AutoLoader {

    static function vcms_autoload ($classPath): array
    {
        self::search($classPath);
        return [];
    }


    static function search (string $classPath, bool $justChecking = false)
    {
        global $Project;

        $namespaces = explode('\\', $classPath);
        $className = array_pop($namespaces);


        $founds = $justChecking ? [] : null;
        foreach ($Project->get_include_dirpaths() as $path) {
            self::search_class($className, $path, $founds);
        }

        return $founds;
    }


    static function search_class ($className, $path, array &$founds = null)
    {
        $filepath = "$path/$className.class.php";

        if (file_exists($filepath)) {
            if ($founds === null) {
                include_once $filepath;
            }
            else {
                $founds[] = $filepath;
            }
        }

        $directories = FileSystem::get_directories($path);

        foreach ($directories as $directory) {
            self::search_class($className, "$path/$directory", $founds);
        }
    }
}