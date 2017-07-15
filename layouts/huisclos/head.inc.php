<meta charset="utf-8">
<title><?php $Page->metadatas->title && print "{$Page->metadatas->title} - " . $Website->name ?></title>
<meta itemprop="description" name="description" content="<?= $Page->metadatas->description ?>">
<meta name="keywords" content="<?= $Page->metadatas->keywords ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<?php

/* Stylesheets */
if (isset($_Stylesheets)) {
  foreach ($_Stylesheets as $Stylesheet) {
    if ($Stylesheet->is_inline())
      printf('<style>%s</style>', file_get_contents($Stylesheet->get_localPath()));
    else
      printf('<link rel="stylesheet" href="%s">', $Stylesheet->get_url());
  }
}



/* Website's scripts (before specific page's scripts) */

foreach ($Website->get_Scripts() as $Script) :

  $Script->is_inline()
  ? printf('<script>%s</script>' . NL, file_get_contents($Script->get_localPath()))
  : printf('<script src="%s"></script>' . NL, $Script->get_url());

endforeach;


/* Page's specific scripts */

$inline = '';
foreach ($Page->get_Scripts() as $Script) {
  $Script->is_inline()
  ? $inline .= file_get_contents($Script->get_localPath()) . NL
  : printf('<script src="%s"></script>' . NL, $Script->get_url());

}
!empty($inline) && printf('<script>%s</script>' . NL, $inline);


?>
<link rel="shortcut icon" href="/img/huisclos-logo-32.png">