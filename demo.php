<?php

require_once(__DIR__."/vendor/autoload.php");

$up = new \FlSouto\HtUpload("anexo",__DIR__."/data/");
$up->accept(['application/pdf'], "Formato não suporttado");
$up->label("Escolha um arquivo");

echo $up;
