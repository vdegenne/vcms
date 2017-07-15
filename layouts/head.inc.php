<?php
  if ($Page->metadatas->fulltitle === null) {
    if ($Page->metadatas->title !== null) {
      $Page->metadatas->fulltitle = $Page->metadatas->title . ' - ';
    }
    $Page->metadatas->fulltitle .= $Request->Website->name;
  }
?>
<meta charset="utf-8">
<title><?=$Page->metadatas->fulltitle?></title>
<meta name="description" content="<?= $Page->metadatas->description ?>">
<meta name="keywords" content="<?= $Page->metadatas->keywords ?>">
<?php if ($Page->metadatas->canonical !== null): ?>
  <meta rel="canonical" href="<?= mkurl($Page->metadatas->canonical) ?>">
<?php endif; ?>
<?php
if ($Page->robots !== null) {
  printf('<meta name="robots" value="%s">' . NL,
         $Page->robots
  );
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<?php

/* Stylesheets */

foreach ($_Stylesheets as $Stylesheet) :

  if ($Stylesheet->is_inline())
    printf('<style>%s</style>', file_get_contents($Stylesheet->get_localPath()));
  else
    printf('<link rel="stylesheet" href="%s">' . NL, $Stylesheet->get_url());

endforeach;



/**
 * Website's scripts (before specific page's scripts)
 */
foreach ($Website->get_Scripts() as $Script) {

  $Script->is_inline()
  ? printf('<script>%s</script>' . NL, file_get_contents($Script->get_localPath()))
  : printf('<script src="%s"></script>' . NL, $Script->get_url());

}



/* Page's specific scripts */

$inline = '';
foreach ($Page->get_Scripts() as $Script) {
  $Script->is_inline()
  ? $inline .= file_get_contents($Script->get_localPath()) . NL
  : printf('<script src="%s"></script>' . NL, $Script->get_url());

}
!empty($inline) && printf('<script>%s</script>' . NL, $inline);


/**
 * hreflang link
 */
if (isset($options['availableLanguages'])) {

  foreach ($options['availableLanguages'] as $lang) {
    printf('<link rel="alternate" hreflang="%s" href="%s" />' . NL,
           $lang,
           mkurl($lang . (REL_URI ? '/' . REL_URI : ''))
    );
  }
}
?>
