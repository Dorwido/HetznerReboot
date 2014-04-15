<?php
/**
 * Created by PhpStorm.
 * User: tsanders
 * Date: 26.03.14
 * Time: 17:12
 */


require_once('config.php');
$GLOBALS['filepath'] = dirname(__FILE__)."/serverstatus/";
for ($i=0;$i<count($config['servers']);$i++){
    $alivechecker = new AliveChecker($config['standard'],$config['servers'][$i]);

}

spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.class.php';
});




?>