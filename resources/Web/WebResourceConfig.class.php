<?php
namespace vcms\resources;


class WebResourceConfig extends VResourceConfig
{
    public $mimetype = 'text/html';
    public $metadatas;

    /**
     * Inlines are scripts or css's (or htmls),
     * that you want to include in your page.
     * The framework takes the content of the
     * file pointed by the relative uri (from the
     * public directory) and paste it in the head
     * element of the structure.
     * @var array
     */
    public $inlines;

    function check_required (array $required=[])
    {
        $required=array_merge($required, ['metadatas']);
        parent::check_required($required);
    }


}