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
 * Les namespaces permettent de ranger des éléments dans un même espace.
 * le namespace par défaut est 'namespace'.
 * on accède au namespace en absolue ou en relative, par défaut depuis le namespace
 * global (e.g. \vdegenne\.. ou vdegenne\.. depuis namespace).
 * On peut alors charger un objet spécifique de la manière suivante :
 * use \vdegenne\myobject [as myobject_newname];
 */
namespace vdegenne;


/**
 *
 * Représente le site web en tant qu'objet, et permet ainsi de faciliter
 * la manipulation de l'environnement.
 * L'objet Website peut aussi être vu comme le projet en lui-même. Cependant
 * les variables paramétriques se trouvent dans l'objet FRAMEWORK.
 *
 * C'est une classe singleton.
 *
 * @author Degenne Valentin
 */
class Website {

    /**
     * Le chemin vers l'arborescence des pages du projet
     * (depuis la racine du projet).
     *
     * @var string
     * @static
     */
    static $PAGES_PATH = 'pages';

    /**
     * Le nom du fichier de contenu des pages du projet
     *
     * @var string
     * @static
     */
    static $PAGE_CONTENT_NAME = 'content.php';





    /**
     * L'objet Singleton représentant le site-web.
     *
     * @var \vdegenne\Website
     * @static
     */
    private static $Website;

    /**
     * Le nom du site-web.
     *
     * @var string
     */
    private $name = '';

    /** @var string */
    private $domainName;

    /**
     * @var \vdegenne\Request
     */
    private $Request;

    /**
     * Dans le cas d'un site intermédiaire (e.g. un blog sur le même site)
     * _urlPath représente l'url vers le "sous-site".
     * Utilisée avec la fonction make_url().
     *
     * @var string
     */
    private $urlPath = '';

    /**
     * Url de l'image favico du site-web.
     * Sans le http://
     *
     * @var string
     */
    private $_favicoUrl = null;

    /**
     * Url de l'image favico de taille 128.
     * Sans le http://
     *
     * @var string
     */
    private $_favico128 = null;

    /**
     * Publisher du site.
     *
     * @var string
     */
    private $_publisher = null;

    /**
     * Les feuilles de style associées au site-web.
     *
     * @var StyleSheet[]
     */
    private $StyleSheets = array();




    public static function redirect_subdomain_lang ($supportedLang,
                                                    $domain,
                                                    $setCookie = true
    ) {
        $location = '';

        // on vérifie que le cookie 'lang' est défini
        if (isset($_COOKIE['lang'])) {
            $location = $_COOKIE['lang'];
        }
        else {

            $location = Visitor::get_prefered_language($supportedLang);

            if (is_null($location)) {
                $location = $supportedLang[0];
            }

            if ($setCookie === true) {
                setcookie(
                    'lang', $location,
                    time() + 10 * 24 * 60 * 60,
                    null, null, false /* l'option secure ne semble pas fonctionner
                                à ce moment du développement (26/02/2015) */
                );
            }
        }


        $location = $location . '.' . $domain;
        header('Location:   http://' . $location);
    }


    /**
     * Get représente la première fonction appelée et permet d'instancier un
     * Singleton représentant le site-web qu'on consulte.
     *
     * Une liste de paramètres doit-être fournie la première fois qu'on appelle
     * cette fonction, dont voici la liste :
     *      - name (String) : nom du site
     *      - requested_page (String) : chemin demandé après le domaine.
     *
     * @return \vdegenne\Website
     */
    static public function get () {
//
//        if (func_num_args() == 0) {
//            if (is_null(Website::$Website)) {
//                throw new \ErrorException('l\'objet n\'a pas été instancié.');
//            }
//            else {
//                return Website::$Website;
//            }
//        }
//        else {
//            if (!is_null(Website::$Website)) {
//                return Website::$Website;
//            }
//
//            $args = func_get_args();
        if (is_null(Website::$Website)) {
            Website::$Website = new Website(
            // request :
//                $args[0]
            );
        }

        return Website::$Website;

    }

    /**
     * Constructeur du Singleton Website.
     * Website permet de stocker les informations dans un objet tel que le nom,
     * un objet représentant la page actuellement demandée, etc...
     *
     * attention : http://php.net/manual/en/language.oop5.decon.php#example-204
     *
     * @param string $name nom du site-web.
     * @param string $URI  URI de la page demandée.
     */
    private function __construct () { }


    public function make_url ($path = '', $withQS = true, $QS = null) {

        $url = 'http://' . $this->domainName . '/';

        $url
            .= strlen($this->urlPath)
            ? ($this->urlPath . '/')
            : '';

        $url .= $path;

        if ($withQS) {
            $url
                .= (is_null($QS))
                ? ((empty($this->Request->get_QueryString()->get_arguments()))
                    ? '' : '?' . $this->Request->get_QueryString()->get_string())
                : ((empty($QS))
                    ? ''
                    : '?' . (new QueryString($QS))->get_string());
        }

        return $url;
    }





    /**
     * Permet de définir le nom du site-web
     *
     * @param $name
     */
    public function set_name ($name) {
        $this->name = $name;
    }

    /**
     * Retourne le nom actuel du site-web.
     *
     * @return string
     */
    public function get_name () {
        return $this->name;
    }

    /**
     * @param string $urlPath
     */
    public function set_urlPath ($urlPath) {
        $this->urlPath = $urlPath;
    }

    /**
     * @return string
     */
    public function get_urlPath () {
        return $this->urlPath;
    }







    /**
     * @param Page $page l'objet Page qui écrasera l'objet current_Page
     */
    public function set_requested_page (Page $page) {
        $this->requestedPage = $page;
    }

    /**
     * Permet de récupérer la page demandée.
     * Par défaut la page demandée correspond au chemin désigné lors de la
     * création de l'objet Website. Toutefois il est possible de modifier la page
     * demandée en créant un objet directement depuis la classe Page.
     *
     * @return Page Objet représentant la page demandée.
     */
    public function get_requested_page () { return $this->requestedPage; }

    /**
     * @return string
     */
    public function get_favico128 () {
        return $this->_favico128;
    }

    /**
     * @param string $favico128
     */
    public function set_favico128 ($favico128) {
        $this->_favico128 = $favico128;
    }

    /**
     * @return string
     */
    public function get_favicoUrl () {
        return $this->_favicoUrl;
    }

    /**
     * @param string $favicoUrl
     */
    public function set_favicoUrl ($favicoUrl) {
        $this->_favicoUrl = $favicoUrl;
    }

    /**
     * @return string
     */
    public function get_publisher () {
        return $this->_publisher;
    }

    /**
     * @param string $publisher
     */
    public function set_publisher ($publisher) {
        $this->_publisher = $publisher;
    }

    /** @param StyleSheet $stylesheet */
    public function add_styleSheet (StyleSheet $stylesheet, $subdomain = false) {

        if ($subdomain) {
            $stylesheet->set_path(
                'http://' . $this->get_subdomain() . $stylesheet->get_path()
            );
        }

        array_push($this->StyleSheets, $stylesheet);
    }

    /** @return StyleSheet[] */
    public function get_StyleSheets () {
        return $this->StyleSheets;
    }

    /** @param string $domainName */
    public function set_domainName ($domainName) {
        $this->domainName = $domainName;
    }

    /** @return string */
    public function get_domain () {
        return $this->domainName;
    }

    /** @return string */
    public function get_subdomain () {
        // peut-être devoir changer cette fonction pour permettre de choisir un niveau de profondeur de retour du sous-domaine
        // e.g. www.fr.degenne-valentin.com (2) -> www.degenne-valentin.com
        return substr(
            $this->domainName, strpos($this->domainName, '.') + 1
        );
    }

    /**
     * @param Request $Request
     */
    public function set_request (Request $Request) {
        $this->Request = $Request;
    }

}
