<?php
/**
 * Created by PhpStorm.
 * User: tsanders
 * Date: 26.03.14
 * Time: 17:12
 */
//url for the robot api
$config['standard']['roboturl']='https://robot-ws.your-server.de';
//login for the robot
$config['standard']['robotlogin']='';
//pasword for the robot
$config['standard']['robotpassword']='';
//how to check the server "http"
$config['standard']['checktype']='http';
//how many checks have to fail before a reboot is send
$config['standard']['fails']=3;
//how many checks per run
$config['standard']['checks']=1;
//how long to wait for the next check if more than 1 in second
$config['standard']['rechecktime']=5;
//reboot type "man","hw","sw" / manual, hardware, software
$config['standard']['reboottype']='hw';
//how long to wait after a reboot was triggered to continue checking the server in seconds
$config['standard']['rebootwaittime']=360;
//how long to wait before triggering a new reboot on fail after one was triggered
$config['standard']['nextrebootwaittime']=3600;
$i=0;
//url for http check
$config['servers'][$i]['checkdata']='';
//ip of the server
$config['servers'][$i]['serverip']='';
//optinoal if not standard
/*
$config['servers'][$i]['roboturl']='';
$config['servers'][$i]['robotlogin']='';
$config['servers'][$i]['robotpassword']='';
$config['servers']['checktype']='http';
$config['servers'][$i]['fails']='';
$config['servers'][$i]['checks']=1;
$config['servers'][$i]['rechecktime']=5;
$config['servers'][$i]['reboottype']='';
$config['servers'][$i]['rebootwaittime']=;
$config['servers'][$i]['nextrebootwaittime']=;
*/
?>