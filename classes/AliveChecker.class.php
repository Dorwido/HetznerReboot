<?php
/**
 * Created by PhpStorm.
 * User: tsanders
 * Date: 26.03.14
 * Time: 17:13
 */

class AliveChecker {

    private $what;
    private $serverip;
    private $checks;
    private $rechecktime;
    private $runs=0;
    private $filename;
    private $filepath="serverstatus/";
    private $roboturl;
    private $robotlogin;
    private $robotpassword;
    private $fails;
    private $reboottype;
    private $rebootwaittime;
    private $nextrebootwaittime;
    private $checktype;
    private $serverfails;
    private $serverlastreboot;

    function __construct($standard,$server){

        $this->what = $server['checkdata'];
        $this->serverip = $server['serverip'];
        $this->checks = $server['checks'] ? $server['checks'] : $standard['checks'];
        $this->rechecktime=$server['rechecktime'] ? $server['rechecktime'] : $standard['rechecktime'];
        $this->roboturl = $server['roboturl'] ? $server['roboturl'] : $standard['roboturl'];
        $this->robotlogin = $server['robotlogin'] ? $server['robotlogin'] : $standard['robotlogin'];
        $this->robotpassword = $server['robotpassword'] ? $server['robotpassword'] : $standard['robotpassword'];
        $this->fails = $server['fails'] ? $server['fails'] : $standard['fails'];
        $this->reboottype = $server['reboottype'] ? $server['reboottype'] : $standard['reboottype'];
        $this->rebootwaittime = $server['rebootwaittime'] ? $server['rebootwaittime'] : $standard['rebootwaittime'];
        $this->nextrebootwaittime = $server['nextrebootwaittime'] ? $server['nextrebootwaittime'] : $standard['nextrebootwaittime'];
        $this->checktype = $server['checktype'] ? $server['checktype'] : $standard['checktype'];
        $this->filename = sha1($this->what);

        if(file_exists($GLOBALS['filepath'].$this->filename)){
            $data = file_get_contents($GLOBALS['filepath'].$this->filename);
            $serverdata = explode("|",$data);
            $this->serverfails = $serverdata[0];
            $this->serverlastreboot = $serverdata[1];
            if($this->serverlastreboot>0 and time()-$this->serverlastreboot<$this->rebootwaittime){
                return;
            }
        }else{
            $this->serverfails=0;
            $this->serverlastreboot = 0;
        }



        if($this->checktype=='http'){
            $this->http_check();
        }
    }




    private function http_check(){
        if($this->runs<$this->checks){
            $this->runs++;

            if(get_headers($this->what)===False){
                $this->serverfails++;
            }else{
                $this->serverfails=0;
            }


        }
        if($this->runs!=$this->checks){
            sleep($this->rechecktime);
            $this->http_check();
        }

    }

    private function do_reboot(){


        $robot = new RobotClient($this->roboturl,$this->robotlogin,$this->robotpassword);
        $robot->resetExecute($this->serverip,$this->reboottype);
    }

    function __destruct(){
        if($this->serverfails>0 and $this->serverfails>=$this->fails and (time()-$this->serverlastreboot>$this->nextrebootwaittime)){
            $this->do_reboot();
            $this->serverfails = 0;
            $this->serverlastreboot = time();
        }

        file_put_contents($GLOBALS['filepath'].$this->filename,$this->serverfails."|".$this->serverlastreboot);
    }

} 