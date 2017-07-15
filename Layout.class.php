<?php

namespace vdegenne;

class Layout {

    const LAYOUTS_DIRNAME = 'layouts';
    const DEFAULT_HEAD_FILENAME = 'head.inc.php';

    private static $layoutsPath;


    /** @var string */
    protected $headPath;

    public function __construct () {

        self::$layoutsPath = INCLUDES_PATH . '/' . Layout::LAYOUTS_DIRNAME;
        $projectLayoutsPath = self::$layoutsPath . '/' . PROJECT_NAME;

        // headPath
        if (is_file($projectLayoutsPath . '/' . Layout::DEFAULT_HEAD_FILENAME)) {
            $this->headPath = $projectLayoutsPath . '/' . Layout::DEFAULT_HEAD_FILENAME;
        }
        else $this->headPath = self::$layoutsPath . '/' . Layout::DEFAULT_HEAD_FILENAME;
    }



    public function path_to_head ($path = null) {
        if (is_null($path)) return $this->headPath;

        if (FileSystem::is_absolute_path($path)) {
            $this->headPath = $path;
        }
        else {
            // Si ce n'est pas un chemin absolu,
            // on prend le répertoire des layouts comme racine
            $this->headPath = self::$layoutsPath . '/' . $path;
        }

    }
}