<?php
/**
 * Copyright (C) 2015 Degenne Valentin
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * Creation date : 02/03/2015 (13:34)
 */

namespace vcms;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;


class FileSystem {

    /**
     * @param string $path
     * @param null $ext
     * @return array|bool
     */
    public static function get_files ($path = '.', $ext = null)
    {

        if (!is_dir($path)) {
            return false;
        }

        if (is_array($ext)) {
            $ext = '{' . implode(',', $ext) . '}';
        }

        $glob = "$path/*" . (($ext) ? ".$ext" : '');
        $files = [];
        foreach (glob($glob, GLOB_BRACE) as $file) {
            if (is_dir($file)) continue;
            $files[] = basename($file);
        }

        return $files;
    }


    static function getDirectories ($base = '.',
                                    $recursive = false,
                                    $fullnames = false,
                                    string $subpath = ''): array
    {

        if (!is_dir($base)) {
            return false;
        }

        if (empty($subpath)) {
            $subpath = $base;
        }

        $directories = [];
        foreach (glob("$subpath/*") as $file) {
            if (is_dir($file)) {
                if ($fullnames && $base !== '.') {
                    $directories[$file] = null;
                } else {
                    $directories[basename($file)] = null;
                }
            }


            if ($recursive) {
                foreach ($directories as $dir) {
                    $directories
                        = array_merge($directories,
                        self::getDirectories($base, true, $fullnames, $fullnames ? $dir : $subpath . DS . $dir));
                }
            }
        }


        return array_keys($directories);
    }


    /**
     * @http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
     */
    static function rmdir_recursive ($dirpath)
    {

        $it = new RecursiveDirectoryIterator($dirpath, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dirpath);
    }




    /**
     * @param string $path chemin vers le fichier
     * @param string $opts options
     *
     * @return string
     */
    static function mb_pathinfo ($path, $opts = '')
    {

        $separator = " qq ";
        $path = preg_replace("/[^ ]/u", $separator . "\$0" . $separator, $path);
        if ($opts == "") $pathinfo = pathinfo($path);
        else $pathinfo = pathinfo($path, $opts);

        if (is_array($pathinfo)) {
            $pathinfo2 = $pathinfo;
            foreach ($pathinfo2 as $key => $val) {
                $pathinfo[$key] = str_replace($separator, "", $val);
            }
        } else if (is_string($pathinfo)) $pathinfo = str_replace($separator, "", $pathinfo);

        return $pathinfo;

    }


    /**
     * @param string $URI
     * @param string $delim
     * @return string
     */
    public static function one_folder_up ($URI, $delim = '/')
    {
        $chunks = explode($delim, rtrim($URI, $delim));
        array_pop($chunks);
        return implode($delim, $chunks);
    }


    static function is_absolute_path ($path)
    {
        return preg_match('@^(\/|[a-zA-Z]{1}:(\/|\\\){1,2})[^\/|\\\].@', $path);
    }


    public static function format_to_valid_name ($name)
    {

        $name = strtr($name, ['Ê' => 'E', 'É' => 'E', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'è' => 'e', 'Ä' => 'A', 'Â' => 'A', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'Ï' => 'I', 'Î' => 'I', 'î' => 'i', 'ï' => 'i', 'Ô' => 'O', 'Ö' => 'O', 'Ò' => 'O', 'ö' => 'o', 'ô' => 'o']);
        $name = str_replace(' ', '_', $name);
        $name = strtolower($name);
        return $name;
    }
} 