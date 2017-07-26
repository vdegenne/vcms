<?php
namespace vcms\resources\implementations;


class WEBResource extends Resource
{
    const HEAD_FILENAME = 'head.php';
    const BODY_FILENAME = 'body.php';

    /**
     * @var WEBResourceConfig
     */
    public $Config;

    function process_response (string $processorFilepath = null, ...$globals) {

        foreach ($GLOBALS as $globalname => $globalvalue) {
            global $$globalname;
        }

        parent::process_response();

        $title = $this->metadatas->title;
        $description = @$this->metadatas->description;
        $keywords = @$this->metadatas->keywords;

        $head = $this->dirpath . '/' . self::HEAD_FILENAME;
        $body = $this->dirpath . '/' . self::BODY_FILENAME;

        ob_start();
        include 'layouts/structure.php';
        $this->Response->content = ob_get_contents();
        ob_end_clean();
    }

}