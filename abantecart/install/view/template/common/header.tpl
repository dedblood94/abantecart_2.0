<?php use abc\ABC;
echo '<?xml version="1.0" encoding="'.ABC::env('APP_CHARSET').'"?>'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>AbanteCart - Installation</title>
<base href="<?php echo $base; ?>" />
<link rel="stylesheet" type="text/css" href="view/css/stylesheet.css" />

<script type="text/javascript"
        src="<?php echo $ssl ? 'https' : 'http'?>://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript">
if (typeof jQuery == 'undefined') {
   var include = '<script type="text/javascript" src="view/js/jquery-1.11.0.min.js">';
   document.write(include);
}
</script>

<script type="text/javascript" src="<?php $this->templateResource('js/bootstrap.min.js');?>"></script>

</head>
<body>
<div class="container" id="container">
  <header><img src="<?php echo $this->templateResource('images/logo.png'); ?>" alt="AbanteCart" title="AbanteCart" /></header>
  <div class="container-fluid">