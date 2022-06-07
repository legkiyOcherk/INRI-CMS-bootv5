<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
$output = '';
$output .= AllFunction::OneSeoFormAdmin( 'SEO Настройки', DB_PFX.'seo', $admin);
#<div class="col-xs-12 col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 col-lg-10 col-md-offset-2 ">
$output .= '
<div class="col-xs-12">
  <pre>
  *h1* - является переменной которая содержит заголовок
  
  Пример:
  *h1* - Купить в Екатеринбурге
  </pre>
</div>';

$admin->setContent($output);
echo $admin->showAdmin('content');