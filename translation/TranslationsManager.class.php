<?php
namespace vcms\translation;


use vcms\database\Database;
use vcms\database\EntityManager;
use vcms\resources\VResource;

class TranslationsManager extends EntityManager {

    /**
     * @param string|null $tablename
     * @param string|null $objectname
     * @param Database|null $Database
     * @return TranslationsManager
     * @throws \Exception
     */
    static function get (string $tablename = null, string $objectname = null, Database $Database = null) : EntityManager
    {
        /** @var VResource $Resource */
        /* should change to global Project ? */
        global $Resource, $Database;

        if (!$Resource->Config->translations_schema) {
            throw new \Exception('"Need a translation schema for translation support."');
        }

        $translationTable = $Resource->Config->translations_schema . '.translations';

        return parent::get($translationTable, 'vcms\translation\Translation', $Database);
    }

    function get_page_translations (string $pagename, string $lang) : array
    {
        $sql = "
SELECT tr_id, translation
FROM $this->schema.pages p
  NATURAL INNER JOIN $this->schema.pages_translations pt
  NATURAL LEFT JOIN $this->schema.translations t
WHERE t.lang=:lang AND p.name=:page;
";
        return $this->get_statement($sql, ['lang' => $lang, 'page' => $pagename])->fetchAll();
    }
}