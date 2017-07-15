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
 * Creation date : 25/04/2015 (19:23)
 */

namespace vdegenne;


class Sitemap {

    /** @var  string */
    private $path;
    /** @var array */
    private $urls = array();

    
    public function __construct ($path) {
        $this->path = $path;
    }


    public function add_url ($loc, $lastmod = null, $changefreq = null, $priority = null) {

        $url = array('loc' => $loc);

        ($lastmod) &&  $url['lastmod'] = $lastmod;
        ($changefreq) && $url['changefreq'] = $changefreq;
        ($priority) && $url['priority'] = $priority;


        array_push($this->urls, $url);
    }


    public function outputs () {

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $urlset = $doc->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->urls as $url) {

            // en vérité $url contient un tableau de paramètres de l'url
            $urlParams = $url;
            $url = $doc->createElement('url');

            foreach ($urlParams as $paramKey => $paramValue) {

                $param = $doc->createElement($paramKey);
                $param->nodeValue = $paramValue;

                $url->appendChild($param);
            }

            $urlset->appendChild($url);
        }

        $doc->appendChild($urlset);
        print gzcompress(preg_replace("@\n@", '', $doc->saveXML()), -1, ZLIB_ENCODING_GZIP);
    }
} 