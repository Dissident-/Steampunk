<?php
$root = __DIR__;
$loader = require $root.'/vendor/autoload.php';
$loader->add('', $root.'/classes/');
 
$pixie = new \App\Pixie();
$pixie->bootstrap($root);
$method = $argv[1];
$params = array_slice($argv,2);
 
$cli = new \App\CLI($pixie);
 
//This is to avoid default HTML error rendering
try{
    call_user_func_array(array($cli, $method), $params);
}catch(Exception $e){
    echo 'ERROR: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
}
