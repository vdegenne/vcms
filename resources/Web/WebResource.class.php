<?php
namespace vcms\resources;


//use vcms\resources\Resource;

class WebResource extends VResource {
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

    function process (string $processorFilepath = null, ...$globals)
    {
        global $Project, $Session;
        $qs = $GLOBALS['QueryString'];
        $Resource = $this;

        $title = $this->Config->metadatas->title;
        if (isset($this->Config->metadatas->description)) {
            $description = @$this->Config->metadatas->description;
        }
        if (isset($this->Config->metadatas->keywords)) {
            $keywords = @$this->metadatas->keywords;
        }


        $head = $this->dirpath . '/' . self::HEAD_FILENAME;
        $body = $this->dirpath . '/' . self::BODY_FILENAME;

        $inlines = '';
        foreach ($this->inlines as $i) {
            /** @var PlainResource $i */
            $i->process();
            $inlines .= $i->content;
        }

        ob_start();
        include 'layouts/structure.php';
        $this->content = ob_get_contents();
        ob_end_clean();
    }

}