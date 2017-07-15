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
 * Représente une page
 * @author Valentin
 */
class Page {


    /**
     * Tableau associatif permettant de gérer un automatisme de redirection
     * dans le cas où la page demandée n'a pas été trouvée.
     * Seul les valeurs 'activated' et 'page' doivent-être changées :
     *  - activated : permet d'activer le rollback (default : true)
     *  - page : la page à charger par le rollback (default : 'pages\notfound')
     * Exemple d'utilisation (dans le fichier incluant),
     * <code>Page::$NOT_FOUND_ROLLBACK['activated'] = true;</code>
     * <code>Page::$NOT_FOUND_ROLLBACK['page'] = 'pages\notfound';</code>
     * @var array
     */
    static $NOT_FOUND_ROLLBACK
        = [
            'activated' => true,
            'page' => 'notfound'
        ];

    /**
     * Variable interne utilisée dans la gestion du rollback NOT_FOUND
     * @var boolean
     */
    private $NOT_FOUND_ROLLBACK_FAILED = false;


    const PREPROCESSOR_FILENAME = 'preprocess.php';

    /**
     * L'extension des pages scripts
     * @var string
     */
    static $SCRIPTS_EXTENSION = 'script.php';

    /**
     * Le nom des fichiers metadatas des pages présent dans le même répertoire
     * que leur contenu.
     * @var string
     */
    static $METADATA_FILENAME = '.metadatas';

    /**
     * @var Website le site-web associé à la page
     */
    private $Website;

    /**
     * MetaDonnées de la page.
     * @var PageMetadatas
     */
    private $Metadatas;

    /**
     * L'uri telle qu'elle a été demandé par l'utilisateur (url dans la barre
     * d'adresse).
     * @var string
     */
    private $url;

    /**
     * Le chemin absolue vers le contenu de la page.
     * @var string
     */
    var $localUri;

    /**
     * le chemin absolue vers le répertoire où est contenu la page demandée.
     * @var str
     */
    private $dirname;

    /**
     * Chemin absolu vers le header de la page.
     * @var string
     */
    private $headerLocation;

    /**
     * Chemin absolu vers le footer de la page.
     * @var string
     */
    private $footerLocation;

    /**
     * Le breadcrumb, soit l'uri cassée.
     * @var array
     */
    private $breadcrumbs;

    /**
     * Variable permettant de savoir (automatisée) si la page physique existe.
     * @var boolean
     */
    private $exists;

    /**
     * Le type de la page (vue ou script). type est une valeur bornée aux valeurs
     * de l'énumération \vdegenne\PageType (voir en fin de classe)
     * @var int
     */
    private $type;

    /**
     * Variable fonctionnelle qui détermine si la page courante est un script ou
     * non
     * @var boolean
     */
    var $is_script;

    /** @var bool */
    private $needAuthentification = false;

    /**
     * Liste (string) des fichiers javascript associés à la page.
     * Les scripts se trouvent dans le répertoire 'js' à la racine du site.
     * @var str[]
     */
    private $scripts = [];





    /**
     * Constructeur d'une page.
     * Une page possède un chemin (raw uri), un breadcrumb (exploded uri),
     * à tout moment la méthode update() permet de mettre à jour les informations
     * de la page en fonction de l'uri.
     * @param \vdegenne\Website $Website
     * @param string            $url
     */
    public function __construct ($url, $Website) {

        $this->url = $url;
        $this->Website = $Website;


        // met à jour les informations de la page, en fonction de l'url
        $this->update();
    }

    /**
     * Permet de mettre à jour certaines informations de l'objet Page en fonction
     * de l'uri fournie dans le constructeur ou au biais de la méthode set_uri()
     */
    public function update () {

        // on enlève les slashes de fin
        $this->url = rtrim($this->url, '/');

        // création du breadcrumb
        $this->breadcrumbs = $this::make_breadcrumbs($this->url);




        // mise à jour du type de la page (vue ou script ou preprocess)
        $this->type = $this::is_script($this->url)
            ? PageType::Script
            : PageType::Vue;
        $this->is_script = ($this->type === PageType::Script) ? true : false;


        // création de l'uri locale
        $this->localUri = $this::make_local_uri($this->url, $this->type);
        $this->dirname = dirname($this->localUri);


        // vérification de l'existence de la page
        $this->exists = (file_exists($this->localUri) ? true : false);


        $this->load_scripts();

//        /** Mise à jour de la location relative du contenu de la page */
//        $this->relativeLocation =

        /**
         * NOT_FOUND_ROLLBACK_FAILED empêche la boucle infinie
         */
        if (!$this->exists && Page::$NOT_FOUND_ROLLBACK['activated']
            && !$this->NOT_FOUND_ROLLBACK_FAILED
        ) {
            $this->not_found_rollback();
        }
    }

    public function not_found_rollback () {

        // mise à jour de l'url par celle définie dans les paramètres du rollback
        $this->url = Page::$NOT_FOUND_ROLLBACK['page'];

        /*
         * Si la page locale n'existe pas
         */
        if (!file_exists(
            Page::make_local_uri($this->url, Page::return_type($this->url))
        )
        ) {
            $this->NOT_FOUND_ROLLBACK_FAILED = true;
        }

        $this->update();
    }










    public function load_scripts () {

        if (file_exists($this->dirname . DS . '.scripts')) {

            $scripts = json_decode(file_get_contents($this->dirname . DS . '.scripts'), true );
            foreach ($scripts as $script) {
                array_push(
                    $this->scripts,
                    sprintf(
                        'http://%s/%s',
                        ($script['subdomain']) ? $this->Website->get_subdomain() : $this->Website->get_domain(),
                        $script['name']
                    )
                );
            }

        }
    }



    public function load_metadatas () {


        if (file_exists($this->dirname . DS . Page::$METADATA_FILENAME)) {
            $jsonRaw = file_get_contents(
                $this->dirname . DS . Page::$METADATA_FILENAME
            );

            $this->Metadatas = Object::cast(
                json_decode($jsonRaw),
                'vdegenne\PageMetadatas'
            );

        }
    }


    /*
     * Les inclusions et génération dans l'ordre de génération dans le script principal
     */
    public function include_preprocessor () {

        // La page de pré-process se trouve dans le même répertoire que la page de contenu
        $preprocessorFile = $this->make_local_uri($this->url, PageType::PreProcessor);

        if (file_exists($preprocessorFile)) {
            include $preprocessorFile;
        }
    }

    public function generate_html_head () {

        /*
         * Le head est généré depuis un objet DOMDocument (xml)
         */
        $htmlHead = new \DOMDocument();
        $htmlHead->formatOutput = true;

        $head = $htmlHead->createElement('head');



        $charset = $htmlHead->createElement('meta');
        $charset->setAttribute('charset', 'utf-8');
        $head->appendChild($charset);


        /*================================================================================
         * pour IE
         *===============================================================================*/
        $xuaCompatible = $htmlHead->createElement('meta');
        $xuaCompatible->setAttribute('http-equiv', 'X-UA-Compatible');
        $xuaCompatible->setAttribute('content', 'IE=edge');
        $head->appendChild($xuaCompatible);



        /** Pour gérer l'affichage sur des supports mobiles */
        $viewport = $htmlHead->createElement('meta');
        $viewport->setAttribute('name', 'viewport');
        $viewport->setAttribute(
            'content',
            'width=device-width, initial-scale=1.0,user-scalable=no'
        );
        $head->appendChild($viewport);




        /*================================================================================
         * Métadonnées
         *===============================================================================*/
        $title = $htmlHead->createElement('title');

        $description = $htmlHead->createElement('meta');
        $description->setAttribute('name', 'description');

        $keywords = $htmlHead->createElement('meta');
        $keywords->setAttribute('name', 'keywords');

        if (!is_null($this->Metadatas)) {

            $titleMetadata = $this->Metadatas->get_title();
            if (!empty($titleMetadata)) {
                $title->nodeValue = $this->Metadatas->get_title() . ' | ';
            }
            $title->nodeValue .= $this->Website->get_name();


            $description->setAttribute(
                'content', $this->Metadatas->get_description()
            );
            $keywords->setAttribute(
                'content', $this->Metadatas->get_keywords()
            );



            /*
             * Est-ce qu'il y a une url canonique ?
             */
            $canonical = $this->Metadatas->get_canonical();
            if (!is_null($canonical)) {
                $canonicalEl = $htmlHead->createElement('link');
                $canonicalEl->setAttribute('rel', 'canonical');
                $canonicalEl->setAttribute(
                    'href',
                    $this->Website->make_url($canonical, false)
                );

                $head->appendChild($canonicalEl);
            }

        }
        else {
            throw (
            new \ErrorException(
                'Les méta-données de la page n\'ont pas été définies'
            )
            );
        }

        $head->appendChild($title);
        $head->appendChild($description);
        $head->appendChild($keywords);

        if (!is_null($this->Website->get_favicoUrl())) {
            $favico = $htmlHead->createElement('link');
            $favico->setAttribute('rel', 'shortcut icon');
            $favico->setAttribute(
                'href', $this->Website->get_favicoUrl()
            );

            $head->appendChild($favico);
        }

        if (!is_null($this->Website->get_favico128())) {
            $favico = $htmlHead->createElement('meta');
            $favico->setAttribute('itemprop', 'image');
            $favico->setAttribute(
                'content', $this->Website->get_favico128()
            );

            $head->appendChild($favico);
        }

        if (!is_null($this->Website->get_publisher())) {
            $publisher = $htmlHead->createElement('link');
            $publisher->setAttribute('rel', 'publisher');
            $publisher->setAttribute(
                'href',
                $this->Website->get_publisher()
            );

            $head->appendChild($publisher);
        }


        /*================================================================================
         * STYLESHEETS
         *===============================================================================*/

        $StyleSheets = $this->Website->get_StyleSheets();
        if (count($StyleSheets) > 0) {

            foreach ($StyleSheets as $StyleSheet) {
                $styleSheetTag = $htmlHead->createElement('link');
                $styleSheetTag->setAttribute('rel', 'stylesheet');
                $styleSheetTag->setAttribute(
                    'href',
                    $StyleSheet->get_path()
                );

                $head->appendChild($styleSheetTag);
            }

        }



        /*================================================================================
         * SCRIPTS
         *===============================================================================*/

        foreach ($this->scripts as $script) {
            $scriptTag = $htmlHead->createElement('script');
            $scriptTag->setAttribute('src', "$script");
            $head->appendChild($scriptTag);
        }




        $htmlHead->appendChild($head);

        return $htmlHead->saveHTML();
    }

    public function include_header () {

        // on vérifie si il n'y a pas un header défini manuellement
        $headerLocation = $this->headerLocation;

        if (is_null($headerLocation)) {
            // sinon on le défini automatiquement
            $headerLocation
                = \FRAMEWORK::$LAYOUTS_PATH . DS
                . \FRAMEWORK::$PROJECT_RELATIVE_PATH . DS
                . \FRAMEWORK::$PROJECT_NAME . '.header.php';
        }

        if (!file_exists($headerLocation)) {
            throw new \ErrorException('No header is set.');
        }

        // inclusion du header
        include $headerLocation;
    }


    /**
     * Permet d'inclure le contenu d'une page. Elle est donc généralement appelée
     * dans le script principal (e.g. index.php) pour inclure le contenu.
     * A noter que si la page demandée est une vue, une page utilisateur donc,
     * la fonction change le répertoire de travail pour permettre de faire des
     * appels relatifs depuis le répertoire de la page.
     */
    public function include_content () {

        if ($this->exists) {

            /*
             * peut être à changer en fonction de si le script a besoin d'être
             * exécuté en parallèle du dossier de la page
             */
            if ($this->type === PageType::Vue || $this->type === PageType::Script) {
                chdir(dirname($this->localUri));
            }

            include $this->localUri;


            // peut être à changer, voir un peu plus haut
            chdir(\FRAMEWORK::$PROJECT_PATH);

        }
        else {
            // exception
            echo 'la page demandée n\'existe pas';
        }
    }


    public function include_footer () {

        $footerLocation = $this->footerLocation;
        if (is_null($footerLocation)) {
            $footerLocation
                = \FRAMEWORK::$LAYOUTS_PATH . DS
                . \FRAMEWORK::$PROJECT_RELATIVE_PATH . DS
                . \FRAMEWORK::$PROJECT_NAME . '.footer.php';
        }

        if (!file_exists($footerLocation)) {
            throw new \ErrorException('No header is set.');
        }

        include $footerLocation;
    }










    /**
     * @param Website $Website
     */
    public function link_to_website (Website $Website) {
        $this->Website = $Website;
    }



    /**
     * @param bool $need
     */
    public function need_authentification ($need = false) {
        $this->needAuthentification = $need;
    }

    /**
     * @return bool
     */
    public function is_authentification_required () {
        return $this->needAuthentification;
    }

    /* SETTERS AND GETTERS */

    public function get_breadcrumb ($index = null) {

        // on renvoie tout si aucun index
        if (is_null($index)) {
            return $this->breadcrumbs;
        }
        // sinon on renvoi la particule associé à l'index
        if ($index < 0 || $index >= count($this->breadcrumbs)) {
            return null;
        }

        return $this->breadcrumbs[$index];

    }

    /**
     * Permet de changer les méta-données de la page actuelle. Les méta-données
     * sont de manière générale chargées automatiquement à l'appel de la fonction
     * load_metadatas() depuis le fichier json ".metadatas" présent dans le
     * répertoire de la page.
     * Fonction particulièrement utile lorsque les données de la page proviennent
     * d'une source externe (e.g. base de données).
     * Il convient alors de créer un objet \vdegenne\PageMetadatas.
     * @param PageMetadatas $pageMetadatas
     */
    public function set_metadatas (PageMetadatas $pageMetadatas) {
        $this->Metadatas = $pageMetadatas;
    }

    /**
     * Permet de mettre à jour l'uri de la page.
     * La fonction update() devrait-être appelée après tout appel à cette fonction
     * pour mettre à jour les informations de l'objet. La fonction n'est pas
     * appelée automatiquement pour des raisons de liberté.
     * @param string $url
     * @param bool   $update
     */
    public function set_url ($url, $update = false) {
        $this->url = $url;

        if ($update) {
            $this->update();
        }
    }

    public function get_url () {
        return $this->url;
    }

    /**
     * Permet de retourner le type de l'uri (vue ou script). Cette fonction ne
     * doit pas être confondue avec get_type(). get_type() permet de retourner le
     * type de la page représenté par l'objet lui-même. return_type() est un outil
     * statique qui peut être utilisé par les classes externes pour connaitre le
     * type d'une URI dans les limites des spécifications de la framework.
     * Pour plus de détails sur la structure des ressources, voir la fonction :
     *  make_local_uri()
     * voir : \vdegenne\PageType
     * @param string $uri l'uri a testée.
     * @return int entier représentant le type de l'uri (voir \vdegenne\PageType)
     */
    static public function return_type ($uri) {
        return Page::is_script($uri) ? PageType::Script : PageType::Vue;
    }

    public function get_type () {
        return $this->type;
    }


    /** @return string */
    public function get_location () {
        return $this->localUri;
    }

    /**
     * @return string
     */
    public function get_headerLocation () {
        return $this->headerLocation;
    }

    /**
     * @param string $headerLocation
     */
    public function set_headerLocation ($headerLocation) {
        $this->headerLocation = $headerLocation;
    }

    public function set_header_filename ($filename) {

        $this->headerLocation
            = \FRAMEWORK::$LAYOUTS_PATH . DS .
            \FRAMEWORK::$PROJECT_RELATIVE_PATH . DS .
            $filename;
    }

    public function does_exist () { return $this->exists; }

    public function has_preprocessor () {
        if (file_exists($this->dirname . DS . Page::PREPROCESSOR_FILENAME)) {
            return true;
        }
        else
            return false;
    }

    public function get_preprocessor_location () {
        return $this->dirname . DS . Page::PREPROCESSOR_FILENAME;
    }


    public function add_script ($script) {
        array_push($this->scripts, $script);
    }









    /**
     * Permet de connaitre si l'uri est sémantiquement un script.
     * @param string $URI l'uri à tester
     * @return bool true si la page est un script
     */
    static private function is_script ($URI) {
        if (strpos($URI, \FRAMEWORK::$SCRIPTS_EXTENSION) !== false) {
            return true;
        }
        else {
            return false;
        }
    }






    /**
     * Permet de créer le chemin absolu vers une page en fonction d'une URI
     * classique de type HTTP et d'un type de page qui sont actuellement
     * au nombre de 2 :
     *  - une vue (page dans l'arborescence du site)
     *  - un script (en dehors de l'arborescence du site pour des raisons de
     *      sécurité.
     * @param string $uri      une URI classique de type http GET (sans l'host)
     * @param int    $pageType type de page
     * @return string chemin absolu vers la page
     */
    static public function make_local_uri ($uri, $pageType = PageType::Vue) {

        /*
         * Mise à jour de _local_uri. Ce dernier représente le chemin absolu
         * vers la page demandée. Il existe deux types de page, une page de vue
         * et une page script, c'est à dire, appelée pour effectuer un traitement
         * spécifique dans l'application.
         */
        switch ($pageType) {
        case PageType::Vue:
            return \FRAMEWORK::$PROJECT_PATH . DS . Website::$PAGES_PATH . DS
            . $uri . DS . Website::$PAGE_CONTENT_NAME;
            break;
        case PageType::Script:
            return \FRAMEWORK::$SCRIPTS_LOCATION_ROOT . DS
            . \FRAMEWORK::$PROJECT_NAME . DS . $uri;
            break;
        case PageType::PreProcessor:
            return \FRAMEWORK::$PROJECT_PATH . DS . Website::$PAGES_PATH . DS
            . $uri . DS . 'preprocess.php';
            break;
        default:
            return null;
            break;
        }
    }



    /**
     * Permet en fournissant une uri (string) de retourner un tableau représentant
     * les différentes valeurs du chemin. Utile pour créer un breadcrumb.
     * La fonction est statique car elle représente un outil.
     * @param string $url
     * @return array un tableau avec les différents valeurs des dossiers du chemin.
     */
    static public function make_breadcrumbs ($url) { return explode('/', trim($url, '/')); }

}

/**
 * Une page peut posséder un type (vue ou script). Cette classe énumérative
 * permet de fournir des constantes utiles à la lisibilité et l'écriture de la
 * classe Page.
 * @package vdegenne
 */
class PageType {

    const Vue = 0;
    const Script = 1;
    const PreProcessor = 2;

}