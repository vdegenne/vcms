<?php
use vcms\resources\implementations\ResourceType;



/* Processing switch */
switch ($Resource->type) {
    case ResourceType::WEB:
        ob_start();
        include_once $Resource->structureFilepath;
        $Resource->content = ob_get_contents();
        ob_end_clean();
        break;


    case ResourceType::REST:
        ob_start();
        include_once $Resource->contentFilepath;
        $Resource->content = ob_get_contents();
        ob_end_clean();
        break;
}