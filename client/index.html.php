<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $siteName ?></title>
  <link rel="shortcut icon" href="<?php echo $this->url( $icon ) ?>">
  <link rel="apple-touch-icon" href="<?php echo $this->url( $touchIcon ) ?>">
<?php $assets->render() ?>
</head>
<body>
  <div id="application"></div>
  <script type="text/javascript">WebIssues.loadApplication(<?php echo $options ?>);</script>
</body>
</html>
