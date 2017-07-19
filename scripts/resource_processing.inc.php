<?php

/* Processing switch */
switch ($Resource->type) {
    case \vcms\resources\ResourceType::WEB:
        ob_start();
        include_once $Resource->structureFilepath;
        $Resource->content = ob_get_contents();
        ob_end_clean();
        break;


    case \vcms\resources\ResourceType::REST:
        ob_start();
        include_once $Resource->contentFilepath;
        $Resource->content = ob_get_contents();
        ob_end_clean();
        break;
}