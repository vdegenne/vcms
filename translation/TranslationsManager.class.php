<?php
namespace vcms\translation;


use vcms\database\EntityManager;

class TranslationsManager extends EntityManager {

    const TABLE = 'vcms.translation';
    const OBJECT = 'vcms\translation\Translation';
}