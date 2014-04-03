<?php

/**
 * Client class for robot webservice
 * 
 * Documentation: http://wiki.hetzner.de/index.php/Robot_Webservice/en
 * 
 * Copyright (c) 2013 Hetzner Online AG
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class RobotClient extends RobotRestClient
{
  const VERSION = '2013.10';
  
  /**
   * Class constructor
   * 
   * @param $url      Robot webservice url
   * @param $login    Robot login name
   * @param $password Robot password
   * @param $verbose
   */ 
  public function __construct($url, $login, $password, $verbose = false)
  {
    parent::__construct($url, $login, $password, $verbose);
    $this->setHttpHeader('Accept', 'application/json');
    $this->setHttpHeader('User-Agent', 'HetznerRobotClient/' . self::VERSION);
  }
  
  /**
   * Execute HTTP request
   * 
   * @return object Response
   * 
   * @throws RobotClientException
   */
  protected function executeRequest()
  {
    $result = parent::executeRequest();

    if ($result['response'] === false)
    {
      throw new RobotClientException('robot not reachable', 'NOT_REACHABLE');
    }
    
    if (empty($result['response']))
    {
      $response = new StdClass();
    }
    else
    {
      $response = json_decode($result['response']);
    }
    
    if ($response === null)
    {
      throw new RobotClientException('response can not be decoded', 'RESPONSE_DECODE_ERROR');
    }
    
    if ($result['response_code'] >= 400 && $result['response_code'] <= 503)
    {
      throw new RobotClientException($response->error->message, $response->error->code);
    }
    
    return $response;
  }
        
  /**
   * Get failover
   * 
   * @param $ip Failover ip address
   * @param $query additional query string
   * 
   * @return object Failover object
   * 
   * @throws RobotClientException
   */
  public function failoverGet($ip = null, array $query = null)
  { 
    $url = $this->baseUrl . '/failover';
    
    if ($ip)
    {
      $url .= '/' . $ip;
    }
    if ($query)
    {
      $url .= '?' . http_build_query($query);
    }
    
    return $this->get($url);
  }

  /**
   * Get failover by server ip
   * 
   * @param $serverIp Server main ip address
   * 
   * @return object Failover object
   * 
   * @throws RobotClientException
   */
  public function failoverGetByServerIp($serverIp)
  {
    return $this->failoverGet(null, array('server_ip' => $serverIp));
  }

  /**
   * Route failover
   * 
   * @param $failoverIp Failover ip address
   * @param $activeServerIp Target server ip address
   * 
   * @return object Failover object
   * 
   * @throws RobotClientException
   */
  public function failoverRoute($failoverIp, $activeServerIp)
  {    
    $url = $this->baseUrl . '/failover/' . $failoverIp;
    
    return $this->post($url, array('active_server_ip' => $activeServerIp));
  }

  /**
   * Get server reset
   *
   * @param $ip Server main ip
   *
   * @return object Reset object
   *
   * @throws RobotClientException
   */
  public function resetGet($ip = null)
  {
    $url = $this->baseUrl . '/reset';
    if ($ip)
    {
      $url .= '/' . $ip;
    }

    return $this->get($url);
  }

  /**
   * Execute server reset
   *
   * @param $ip Server main ip
   * @param $type Reset type 
   *
   * @return object Reset object
   *
   * @throws RobotClientException
   */
  public function resetExecute($ip, $type)
  {
    $url = $this->baseUrl . '/reset/' . $ip;

    return $this->post($url, array('type' => $type));
  }

  /**
   * Get current boot config
   *
   * @param $ip Server main ip
   *
   * @return object Boot object
   *
   * @throws RobotClientException
   */
  public function bootGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip;

    return $this->get($url);
  }

  /**
   * Get server rescue data
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueGet($ip)
  { 
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->get($url);
  }

  /**
   * Activate rescue system for a server
   *
   * @param $ip Server main ip
   * @param $os Operating system to boot
   * @param $arch Architecture of operating system
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueActivate($ip, $os, $arch)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->post($url, array('os' => $os, 'arch' => $arch));
  }

  /**
   * Deactivate rescue system for a server
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->delete($url);
  }

  /**
   * Get linux data
   *
   * @param $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->get($url);
  }

  /**
   * Activate linux installation
   *
   * @param $ip Server main ip
   * @param $dist Distribution identifier
   * @param $arch Architecture
   * @param $lang Language
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxActivate($ip, $dist, $arch, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->post($url, array(
      'dist' => $dist,
      'arch' => $arch,
      'lang' => $lang
    ));
  }

  /**
   * Deactivate linux installation
   *
   * @param $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->delete($url);
  }

  /**
   * Get vnc data
   *
   * @param $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->get($url);
  }

  /**
   * Activate vnc installation
   *
   * @param $ip Server main ip
   * @param $dist Distribution identifier
   * @param $arch Architecture
   * @param $lang Language
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncActivate($ip, $dist, $arch, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->post($url, array(
      'dist' => $dist, 
      'arch' => $arch, 
      'lang' => $lang
    ));
  }

  /**
   * Deactivate vnc installation
   *
   * @param $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->delete($url);
  }

  /**
   * Get windows data
   *
   * @param $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->get($url);
  }

  /**
   * Activate windows installation
   *
   * @param $ip Server main ip
   * @param $lang Language
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsActivate($ip, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->post($url, array('lang' => $lang));
  }

  /**
   * Deactivate windows installation
   *
   * @param $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->delete($url);
  }

  /**
   * Get cPanel data
   *
   * @param $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->get($url);
  }

  /**
   * Activate cPanel installation
   *
   * @param $ip Server main ip
   * @param $dist Linux distribution
   * @param $arch Architecture
   * @param $lang Language
   * @param $hostname Hostname
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelActivate($ip, $dist, $arch, $lang, $hostname)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->post($url, array(
      'dist'     => $dist,
      'arch'     => $arch,
      'lang'     => $lang,
      'hostname' => $hostname
    ));
  }

  /**
   * Deactivate cPanel installation
   *
   * @param $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->delete($url);
  }
  
  /**
   * Get plesk data
   *
   * @param $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->get($url);
  }

  /**
   * Activate plesk installation
   *
   * @param $ip Server main ip
   * @param $dist Linux distribution
   * @param $arch Architecture
   * @param $lang Language
   * @param $hostname Hostname
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskActivate($ip, $dist, $arch, $lang, $hostname)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->post($url, array(
      'dist'     => $dist,
      'arch'     => $arch,
      'lang'     => $lang,
      'hostname' => $hostname
    ));
  }

  /**
   * Deactivate plesk installation
   *
   * @param $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->delete($url);
  }

  /**
   * Get Wake On Lan data
   *
   * @param $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws RobotClientException
   */
  public function wolGet($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->get($url);
  }
  
  /**
   * Send Wake On Lan packet to server
   *
   * @param $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws RobotClientException
   */
  public function wolSend($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->post($url, array('server_ip' => $ip));
  }

  /**
   * Get rdns entry for ip
   *
   * @param $ip
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsGet($ip)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->get($url);
  }

  /**
   * Create rdns entry for ip
   *
   * @param $ip
   * @param $ptr
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsCreate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->put($url, array('ptr' => $ptr));
  }

  /**
   * Update rdns entry for ip
   *
   * @param $ip
   * @param $ptr
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsUpdate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->post($url, array('ptr' => $ptr));
  }

  /**
   * Delete rdns entry for ip
   *
   * @param $ip
   *
   * @throws RobotClientException
   */
  public function rdnsDelete($ip)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    $this->delete($url);
  }

  /**
   * Get all servers
   *
   * @return array Array of server objects
   *
   * @throws RobotClientException
   */
  public function serverGetAll()
  {
    $url = $this->baseUrl . '/server';

    return $this->get($url);
  }

  /**
   * Get server by main ip
   *
   * @param $ip Server main ip
   *
   * @return object Server object
   *
   * @throws RobotClientException
   */
  public function serverGet($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip;

    return $this->get($url);
  }
  
  /**
   *  Update servername
   * 
   *  @param $ip Server main ip
   *  @param $name Servername
   * 
   *  @return object Server object
   * 
   *  @throws RobotClientException
   */
  public function servernameUpdate($ip, $name)
  {
    $url = $this->baseUrl . '/server/' . $ip;
    
    return $this->post($url, array('server_name' => $name));
  }

  /**
   * Get all single ips
   *
   * @return array Array of ip objects
   *
   * @throws RobotClientException
   */
  public function ipGetAll()
  {
    $url = $this->baseUrl . '/ip';

    return $this->get($url);
  }

  /** 
   * Get all single ips of specific server
   *
   * @param $serverIp Server main ip
   *
   * @return array Array of ip objects
   *
   * @throws RobotClientException
   */
  public function ipGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/ip?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get ip
   *
   * @param $ip
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->get($url);
  }

  /**
   * Enable traffic warnings for single ip
   *
   * @param $ip
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }
  
  /**
   * Disable traffic warnings for single ip
   *
   * @param $ip
   *
   * @return object Ip object
   */
  public function ipDisableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'false'));
  }

  /**
   * Set traffic warning limits for single ip
   *
   * @param $ip
   * @param $hourly  Hourly traffic in megabyte
   * @param $daily   Daily traffic in megabyte
   * @param $monthly Montly traffic in gigabyte
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipSetTrafficWarningLimits($ip, $hourly, $daily, $monthly)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array(
      'traffic_hourly'  => $hourly,
      'traffic_daily'   => $daily,
      'traffic_monthly' => $monthly
    ));
  }

  /**
   * Get all subnets
   *
   * @return array Array of subnet objects
   *
   * @throws RobotClientException
   */
  public function subnetGetAll()
  {
    $url = $this->baseUrl . '/subnet';

    return $this->get($url);
  }

  /**
   * Get all subnets of specific server
   *
   * @param $serverIp Server main ip
   *
   * @return array Array of subnet objects
   *
   * @throws RobotClientException
   */
  public function subnetGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/subnet?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->get($url);
  }
 
  /**
   * Enable traffic warnings for subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }
  
  /**
   * Disable traffic warnings for subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetDisableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'false'));
  }

  /**
   * Set traffic warning limits for subnet
   *
   * @param $ip Net ip
   * @param $hourly  Hourly traffic in megabyte
   * @param $daily   Daily traffic in megabyte
   * @param $monthly Monthly traffic in gigabyte
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetSetTrafficWarningLimits($ip, $hourly, $daily, $monthly)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array(
      'traffic_hourly'  => $hourly,
      'traffic_daily'   => $daily,
      'traffic_monthly' => $monthly
    ));
  }

  /** 
   * Get traffic for single ips
   * 
   * @param $ip   Single ip address or array of ip addresses
   * @param $type Traffic report type
   * @param $from Date from
   * @param $to   Date to
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGetForIp($ip, $type, $from, $to)
  {
    return $this->trafficGet(array(
      'ip'   => $ip,
      'type' => $type,
      'from' => $from,
      'to'   => $to
    ));
  }

  /**
   * Get traffic for subnets
   *
   * @param $subnet Net ip address of array of ip addresses
   * @param $type   Traffic report type
   * @param $from   Date from
   * @param $to     Date to
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGetForSubnet($subnet, $type, $from, $to)
  {
    return $this->trafficGet(array(
      'subnet' => $subnet,
      'type'   => $type,
      'from'   => $from,
      'to'     => $to
    ));
  }

  /**
   * Get traffic for single ips and subnets
   *
   * @param $options Array of options
   *  'ip'     => ip address or array of ip addresses
   *  'subnet' => ip address or array of ip addresses
   *  'type'   => Traffic report type (day, month, year)
   *  'from'   => Date from
   *  'to'     => Date to
   *
   *  Date format:
   *    [YYYY]-[MM] for type year
   *    [YYYY]-[MM]-[DD] for type month
   *    [YYYY]-[MM]-[DD]T[HH] for type day
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGet(array $options)
  {
    $url = $this->baseUrl . '/traffic';

    return $this->post($url, $options); 
  }

  /**
   * Get separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->get($url);
  }
  
  /**
   * Create separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacCreate($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';
    
    return $this->put($url);
  }
  
  /**
   * Delete separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacDelete($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';
    
    return $this->delete($url);
  }

  /**
   * Get the mac address of a ipv6 subnet
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->get($url);
  }

  /**
   * Set the mac address of a ipv6 subnet
   *
   * @param $ip
   * @param $mac
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacSet($ip, $mac)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->put($url, array('mac' => $mac));
  }

  /**
   * Reset the mac address of a ipv6 subnet to the
   * default value (the servers real mac address)
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacReset($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->delete($url);
  }
}