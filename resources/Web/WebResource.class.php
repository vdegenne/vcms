<?php
namespace vcms\resources;


use vcms\resources\Resource;

class WebResource extends Resource {
    const HEAD_FILENAME = 'head.php';
    const BODY_FILENAME = 'body.php';

    /**
     * @var WebResourceConfig
     */
    public $Config;

    public $inlines = [];


    function __construct ($dirpath = null, $Config = null)
    {
        parent::__construct($dirpath, $Config);


        if (isset($this->Config->inlines)) {
            foreach ($this->Config->inlines as $pathToInline) {
                $this->inlines[] = new PlainResource('www/' . $pathToInline);
            }
        }
    }

    function process_response (string $processorFilepath = null, ...$globals)
    {

        parent::process_response();

        foreach ($GLOBALS as $globalname => $globalvalue) {
            global $$globalname;
        }


        $title = $this->metadatas->title;
        $description = @$this->metadatas->description;
        $keywords = @$this->metadatas->keywords;

        $head = $this->dirpath . '/' . self::HEAD_FILENAME;
        $body = $this->dirpath . '/' . self::BODY_FILENAME;

        $inlines = '';
        foreach ($this->inlines as $i) {
            /** @var PlainResource $i */
            $i->process_response();
            $inlines .= $i->Response->content;
        }

        ob_start();
        include 'layouts/structure.php';
        $this->Response->content = ob_get_contents();
        ob_end_clean();
    }

}