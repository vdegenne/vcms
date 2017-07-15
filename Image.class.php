<?php
/**
 * Copyright (C) 2015 Degenne Valentin
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
 *
 * Creation date : 07/04/2015 (14:09)
 */

namespace vdegenne;


class Image {

    /** @var  string */
    private $path;
    /** @var  string */
    private $type;
    /** @var  string */
    private $base64;



    public function __construct ($path = null) {

        if ($path) {

            $this->path = $path;
        }
    }

    public function update () {

        if (file_exists($this->path)) {
            $this->base64 = $this::get_base64_from_image($this->path, $this->type);
        }
    }


    public function create_image_from_base64 () {

        if (!is_null($this->base64) && !is_null($this->type)) {
            file_put_contents($this->path, base64_decode($this->base64));
        }
    }

    public function create_image_from_content ($content) {

        if (!is_null($content) && !is_null($this->type)) {
            file_put_contents($this->path, $content);
        }
    }

    static public function get_base64_from_image ($path, &$type = null) {

        if (file_exists($path)) {

            $baseType = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);


            if (!is_null($type)) {
                $type = $baseType;
            }

            return base64_encode($data);
        }
    }


    static public function get_base64_template_from_image ($path) {

        if (!file_exists($path)) {
            throw new \Exception("L'image n'existe pas");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $mime = null;
        switch ($extension) {
        case 'png':
            $mime = 'image/png';
            break;
        case 'svg+xml':
            $mime = 'image/svg+xml';
            break;
        default:
            return;
        }

        return sprintf(
            'data:%s;base64,%s',
            $mime,
            base64_encode(file_get_contents($path))
        );
    }





    /** @param string */
    public function set_path ($path) {
        $this->path = $path;
    }

    /** @return string */
    public function get_path () {
        return $this->path;
    }

    /** @param string */
    public function set_type ($type) {
        $this->type = $type;
    }

    /** @return string */
    public function get_type () {
        return $this->type;
    }

    /** @param string */
    public function set_base64 ($base64) {
        $this->base64 = $base64;
    }

    /** @return string */
    public function get_base64 () {
        return $this->base64;
    }
    /** @param string */
    public function set_base64_from_template ($data) {

        list($mime, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);

        $this->type = substr($mime, strrpos($mime, '/') + 1);
        $this->base64 = $data;

    }
} 