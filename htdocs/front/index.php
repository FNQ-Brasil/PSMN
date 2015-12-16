<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>MPE</title>
    <meta name="description" content="Descrição">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/assets/stylesheets/style.css">
  </head>
  <?php
    // mpe-brasil || sebrae-mais || mpe-diagnostico
    $type = 'mpe-brasil';
  ?>
  <body id="page<?php echo str_replace('/', '-', str_replace('/front', '', $_SERVER["REQUEST_URI"]) ) == '-' ? '-home' : str_replace('/', '-', str_replace('/front', '/', $_SERVER["REQUEST_URI"]) ); ?>" class="<?php echo $type ?>">

    <?php
      $yield = implode('/', array_filter( array_unique( explode('/', str_replace('/front', '/', $_SERVER["REQUEST_URI"]) )), 'strlen'));
      include( ( $yield == '' ? 'home' : $yield ) . '.php' );
    ?>

    <script data-main="/assets/javascripts/script.js" src="/assets/javascripts/libraries/require/require.js"></script>
</body>
</html>
