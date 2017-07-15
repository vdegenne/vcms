<?php
namespace vdegenne;

use Exception;



class TranslatorManager extends DatabaseManager {


    const TEMP_FILE_PREFIX = ".tempTranslatedPage_";


    /** @var DatabaseManager */
    protected static $DatabaseManager; // singleton

    /**
     * @var string Path to the main pages' directory
     */
    protected $pagesDirpath;



    /**
     * Get the singleton of the Manager
     *
     * @param Database $Database
     * @param string $schema The name of the website's schema in the database
     *
     * @return mixed
     * @throws Exception Needs a schema
     */
//  public static function get (Database $Database, $schema) {
//    return parent::get($Database);
//  }


    const PERSIST_SQL =<<<EOF
        INSERT INTO %schema%.translations ("tagName", "tagId", page, hreflang, trans)
VALUES (:tagname, :tagid, :page, :hreflang, :trans);
EOF;
    
    function persist (Translation $Translation) {

        $placeholders = [
            'tagname' => 'TR' . strtoupper($Translation->page[0]),
            'tagid' => $Translation->tagId,
            'page' => $Translation->page,
            'hreflang' => $Translation->hreflang,
            'trans' => $Translation->trans
        ];

        $this->query(str_replace('%schema%', $this->placeholders['schema'], self::PERSIST_SQL), $placeholders);
    }

    const MERGE_SQL = 'UPDATE %schema%.translations SET "tagName"=:tagname, "tagId"=:tagid, page=:page, hreflang=:hreflang, trans=:trans WHERE id=:id;';
    function merge (Translation $Translation) {
        if ($Translation->id === null) {
            throw new Exception('Trying to merge a Translation with no id');
        }
    
        $placeholders = [
            'tagname' => 'TR'. strtoupper($Translation->page[0]),
            'tagid' => $Translation->tagId,
            'page' => $Translation->page,
            'hreflang' => $Translation->hreflang,
            'trans' => $Translation->trans,
            'id' => $Translation->id
        ];
    
        $this->query(str_replace('%schema%', $this->placeholders['schema'], self::MERGE_SQL), $placeholders);
    }

    function save (Translation $Translation) {
        if (($id = $this->entry_exists($Translation)) !== false) {
            $Translation->id = $id;
            $this->merge($Translation);
        } else {
            $this->persist($Translation);
        }
    }

  
  
    const ENTRY_EXISTS = 'SELECT id FROM %schema%.translations WHERE page=:page AND "tagId"=:tagId AND hreflang=:hreflang;';
    function entry_exists (Translation $Translation) {

        $placeholders = [
            'page' => $Translation->page,
            'tagId' => $Translation->tagId,
            'hreflang' => $Translation->hreflang
        ];

        $statement = $this->query(str_replace('%schema%', $this->placeholders['schema'], self::ENTRY_EXISTS), $placeholders);

        if ($statement->rowCount() > 0) {
            return $statement->fetch()['id'];
        } else {
            return false;
        }
    }

  
  
    const GET_TRANSLATIONS = 'SELECT %select% FROM %schema%.translations_and_tags WHERE %where%;';
    function get_translations ($select = null, $where = null, $extraPlaceholders = null) {

        ($select === null) && $select = '*';
        ($where === null) && $where = 'true';

        $sql = str_replace(['%select%', '%schema%', '%where%'], [$select, $this->placeholders['schema'], $where], self::GET_TRANSLATIONS);

        $placeholders = $this->get_required_placeholders($sql);
        if ($extraPlaceholders !== null) {
            $placeholders = array_merge($placeholders, $extraPlaceholders);
        }

        return $this->query($sql, $placeholders)->fetchAll();
    }


    const GET_PAGE_TRANSLATIONS =<<<EOF
SELECT tag, trans FROM %schema%.translations_and_tags
WHERE (page IS NULL OR page=:page) AND hreflang=:hreflang;
EOF;

    private function get_page_translations ($page) {

        $placeholders = array_merge(
            $this->get_required_placeholders(self::GET_PAGE_TRANSLATIONS),
            ['page' => $page]
        );

        return $this->query(str_replace('%schema%', $this->placeholders['schema'], self::GET_PAGE_TRANSLATIONS), $placeholders)->fetchAll();
    }

    const ALL_TAG_ID = 'SELECT DISTINCT "tagId" FROM %schema%.translations WHERE page=:page AND "tagId" IS NOT NULL ORDER BY "tagId" ASC;';

    public function get_all_page_tagids ($page) {
        if ($page === null) {
            return false;
        }

        $placeholders = array_merge(
            $this->get_required_placeholders(self::ALL_TAG_ID),
            ['page' => $page]
        );

        $tagIds = $this->query(
            str_replace('%schema%', $this->placeholders['schema'], self::ALL_TAG_ID),
            $placeholders,
            \PDO::FETCH_NUM)->fetchAll();

        return array_map(function ($t) {
            return $t[0];
        }, $tagIds);
    }


    const ALL_PAGE_NAME = 'SELECT DISTINCT page FROM %schema%.translations WHERE page IS NOT NULL;';
    function get_all_page_names () {
        $pages = $this->query(
            str_replace('%schema%', $this->placeholders['schema'], self::ALL_PAGE_NAME),
            null,
            \PDO::FETCH_NUM)->fetchAll();

        return array_map(function ($p) {
            return $p[0];
        }, $pages);
    }

    /**
     * Translate and Generate the page
     * A template file consists of any type of file with %PLACEHOLDER% inside
     * you can associate a template file to a specific dictionary in the database.
     * every placeholders in the template will then be replaced with a word in the
     * dictionary you associated (column 'page' in the database)
     *
     * @param string $templateFilepath relative path from where it was invoked (index.html)
     * @param string|null $pageName The name of the page as it appears in the database
     *        if null and the base filename has the form page-<name>
     *        the function extracts <name> as the default value of this argument.
     *
     * @return String the filename of the generated file
     */
    public function generate_translated_page ($templateFilepath, $pageName = null) : String {

        if (!file_exists($templateFilepath)) {
            throw new Exception('the template file wasn\'t found');
        }

        if ($pageName === null) {
            $pageName = explode('-', FileSystem::mb_pathinfo($templateFilepath)['filename'])[1];
        }

        $buf = file_get_contents($templateFilepath);

        // fetching the translations from the databse
        $translations = $this->get_page_translations($pageName);

        $required_translations = [
            'tag' => [],
            'trans' => []
        ];
        foreach ($translations as $t) {
            if (strpos($buf, "%{$t['tag']}%")) {
                $required_translations['tag'][] = "%$t[tag]%";
                $required_translations['trans'][] = $t['trans'];
            }
        }

        
        $fileName = dirname($templateFilepath) . '/' . self::TEMP_FILE_PREFIX . "$pageName.inc.php";

        /* creating the translated file */
        file_put_contents($fileName, str_replace(
            $required_translations['tag'],
            $required_translations['trans'],
            $buf
        ));

        return $fileName;
    }


    /**
     * Returns all the tag ids present in the page's file.
     *
     * @param $page Name of the page
     * @return array|bool
     * @throws \Exception
     */
    function get_all_file_tagids ($page) {
        if ($this->pagesDirpath === null) {
            throw new \Exception ('pagesDirpath is not defined');
        }

        if (!file_exists('' . ($filepath = $this->pagesDirpath . DS . $page . '.php')) &&
        !file_exists('' . ($filepath = $this->pagesDirpath . DS . 'page-' . $page . '.php'))
        ) {
            return false;
        }

        $fileContent = file_get_contents($filepath);

        preg_match_all("/class=\".*tr{$page[0]}([0-9]+).*\"?/", $fileContent, $matches);

        return array_map(function ($t) {
            return intval($t);
        }, $matches[1]);
    }


    const CREATE_PAGE = 'INSERT INTO %schema%.translations (page) SELECT DISTINCT :page FROM %schema%.translations WHERE NOT EXISTS (SELECT id FROM %schema%.translations WHERE page=:page) RETURNING *;';
    /**
     * @param $page the name of the page to add in the database`
     * @return bool whether the page was inserted in the database or not (already present or not)
     */
    function create_page ($page) {

        $placeholders = ['page' => $page];

        $returning = $this->query(str_replace('%schema%', $this->placeholders['schema'], self::CREATE_PAGE), $placeholders);

        if ($returning->rowCount() === 0) {
            return false;
        } else
            return true;
    }


    const PURGE_NONEXISTENTS = 'DELETE FROM %schema%.translations WHERE page=:page AND "tagId" IN (:tagids) RETURNING id;';
    function purge_database ($page, Array $tagIds) {
        if (empty($tagIds)) return false;

        list($SQL, $placeholders) = $this->replace_placeholder_list(self::PURGE_NONEXISTENTS, 'tagids', $tagIds);
        $placeholders['page'] = $page;

        return $this->query(
            str_replace('%schema%', $this->placeholders['schema'], $SQL),
            $placeholders)->rowCount() ? true : false;
    }

    /**
     * To IMPLEMENT
     * @param $page
     * @return int
     */
    public function get_optimal_tag_id ($page) {
        if ($page === null) return -1;

        $translations = $this->get_translations($page);
    }
}

