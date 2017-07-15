<meta charset="utf-8">
<title><?php $Page->name && print "{$Page->name} - " ?><?= $Website->name ?></title>
<meta name="description" content="<?=$Page->description?>" />
<meta name="viewport" content="width=device-width, initial-scale=1; user-scalable=no">
<?php

/* Stylesheets */

foreach ($_Stylesheets as $Stylesheet) :

    if ($Stylesheet->is_inline())
        printf('<style>%s</style>', file_get_contents($Stylesheet->get_localPath()));
    else
        printf('<link rel="stylesheet" href="%s">', $Stylesheet->get_url());

endforeach;



/* Website's scripts (before specific page's scripts) */

foreach ($Website->get_Scripts() as $Script) :

    $Script->is_inline()
        ? printf('<script>%s</script>'.NL, file_get_contents($Script->get_localPath()))
        : printf('<script src="%s"></script>'.NL, $Script->get_url());

endforeach;



/* Page's specific scripts */

$inline = '';
foreach ($Page->get_Scripts() as $Script) {
    $Script->is_inline()
        ? $inline .= file_get_contents($Script->get_localPath()).NL
        : printf('<script src="%s"></script>'.NL, $Script->get_url());

}
!empty($inline) && printf('<script>%s</script>'.NL, $inline);


?>
<?php print $Emulsion->emulsify() ?>