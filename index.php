<?php

require_once(dirname(__FILE__) . '/vendor/autoload.php');

$developers = json_decode(file_get_contents('data/developers.json'), true);
$loader = new Twig_Loader_Filesystem(dirname(__FILE__) . '/template');
$twig = new Twig_Environment($loader);

echo $twig->render('index.html.twig', array('developers' => $developers));