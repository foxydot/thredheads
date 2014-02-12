<?php
/*
 *  Plugin Name: Wp-e-Commerce Fedex Shipping Module
 *  Plugin URI: http://getshopped.org/extend/premium-upgrades/premium-upgrades/fedex-shipping-module/
 *  Description: A Fedex Shipping Module for Wp-e-commerce
 *  Version: 1.5.5
 *  Author: Greg Gullet and Instinct 
 *  Author URI: http://www.getshopped.org/
*/

define('FEDEX_FILE_PATH', dirname(__FILE__));

class fedex {
    var $internal_name, $name;
    private $service_wsdl = "";
    private $proxy_host = "";
    private $proxy_port = "";
    private $proxy_user = "";
    private $proxy_pass = "";

    /**
     * PHP5 contructor used to setup the fedex shipping module
     * @access private
     *
     * @since 1.0
     * @return Boolean true on completion
     */
    function fedex () {
        $this->internal_name = "fedex";
        $this->name="Fedex";
        $this->is_external=true;
        $this->requires_curl=true;
        $this->requires_weight=true;
        $this->needs_zipcode=true;
        $this->_setServiceWSDL();
        $this->logFile = 'log-'.time().'.txt';
        //$this->setProxy($host, $port, $user, $pass); // Set this to enable a proxy, host os only required var
        return true;
    }

    /**
     * Gets the id currently unused 
     * @access private
     *
     * @since 1.0 
     * @return fedex id
     */
    function getId() {
        //return $this->fedex_id;
    }


    /**
     * Sets the id currently unused 
     * @access private
     *
     * @since 1.0 
     * @return Boolean true on completion
     */
    function setId($id) {
        //$fedex_id = $id;
        //return true;
    }

    /**
     * Gets the Proxy information set by contructor
     * @access private
     *
     * @since 1.0
     * @return array proxy settings
     */
    function getProxy(){
        return array("host"=>$this->proxy_host,"port"=>$this->proxy_port,
                     "user"=>$this->proxy_user,"pass"=>$this->proxy_pass);
    }


    /**
     * Sets the proxy details
     * @access private
     *
     * @since 1.0 
     * @return Boolean true on completion
     */
    function setProxy($host, $port="", $user="", $pass=""){
        // This module requires cURL. you are able to supply proxy information.
        // For now just hard code input in the call in the constructor (fedex()) function
        // in the call that is commented out. will add a ui in the settings area later.
        $this->proxy_host = $host;
        $this->proxy_port = $port;
        $this->proxy_user = $user;
        $this->proxy_pass = $pass;
        return true;
    }


    /**
     * Gets the shipping modules name
     * @access private
     *
     * @since 1.0 
     * @return modules name
     */
    function getName(){
        return $this->name;
    }

    /**
     * Gets the internal shipping modules name
     * @access private
     *
     * @since 1.0 
     * @return modules internal name
     */
    function getInternalName(){
        return $this->internal_name;
    }
    
    /**
     * Sets the service WSDL dependant on settings configured on 
     * settings>shipping whether test or products environment is used
     * @access private
     *
     * @since 1.0 
     * @return none
     */
    function _setServiceWSDL(){
        global $wpdb;
        $wpsc_fedex_settings = get_option("wpsc_fedex_settings");
        $wpsc_fedex_environment = (array_key_exists('environment', (array)$wpsc_fedex_settings))? $wpsc_fedex_settings["environment"] : "0";

        if ($wpsc_fedex_environment == "1"){
            $this->service_wsdl = "library/Test_RateService_v7.wsdl";
        }else{
            $this->service_wsdl = "library/Prod_RateService_v7.wsdl";
        }
    }

    /**
     * Gets the shipping modules form
     * @access private
     *
     * @since 1.0 
     * @return String XHTML table data 
     */
    function getForm(){
        $wpsc_fedex_settings = get_option("wpsc_fedex_settings");

        $packaging_options = array(
            "YOUR_PACKAGING" =>__("Your Packaging", 'wpsc'),
            "FEDEX_10KG_BOX" =>__("Fedex 10 KG Box", 'wpsc'),
            "FEDEX_25KG_BOX" =>__("Fedex 25 KG Box", 'wpsc'),
            "FEDEX_BOX" => __("Fedex Box", 'wpsc'),
            "FEDEX_ENVELOPE" => __("Fedex Envelope", 'wpsc'),
            "FEDEX_PAK" => __("Fedex Pak", 'wpsc'),
            "FEDEX_TUBE" =>__("Fedex Tube", 'wpsc')
        );

        $output = "<tr>\n\r";
        $output .= "    <td>".__('Packaging', 'wpsc')."</td>\n\r";
        $output .= "    <td>\n\r";
        $output .= "        <select name='wpsc_fedex_settings[container]'>\n\r";
        foreach($packaging_options as $key => $name) {
            $selected = '';
            if($key == $wpsc_fedex_settings['container']) {
                    $selected = "selected='true' ";
            }
            $output .= "    <option value='{$key}' {$selected}>{$name}</option>\n\r";
        }
        $output .= "        </select>\n\r";
        $output .= "    </td>\n\r";
        $output .= "</tr>\n\r";
        $output .= "<tr>\n\r";
        $output .= "    <td>".__('Rate Type', 'wpsc')."</td>\n\r";
        $output .= "    <td>\n\r";
        $output .= "        <select name='wpsc_fedex_settings[rate_type]'>\n\r";
        $list = "selected='selected'";
        $account = "";
        if ($wpsc_fedex_settings['rate_type'] == "ACCOUNT"){
            $account = $list;
            $list = "";
        }
        $output .= "            <option value=\"LIST\" ".$list." >List Rates</option>\n\r";
        $output .= "            <option value=\"ACCOUNT\" ".$account." >Account Rates</option>\n\r";
        $output .= "        </select>\n\r";
        $output .= "        <br />* ".__('List rates are Standard List Rates','wpsc');
        $output .= "        <br />* ".__('Account Rates are Discounted based on your fedex account','wpsc');
        $output .= "    </td>\n\r";
        $output .= "</tr>\n\r";
        $output .= "<tr>\n\r";
        $output .= "    <td>".__('Dropoff Type', 'wpsc')."</td>\n\r";
        $output .= "    <td>\n\r";
        $output .= "        <select name='wpsc_fedex_settings[DropoffType]'>\n\r";
        $drop_types = array("BUSINESS_SERVICE_CENTER" => "Drop at a Center",
                            "DROP_BOX" => "Drop Box",
                            "REGULAR_PICKUP"=> "Regular Pickup",
                            "REQUEST_COURIER"=> "Pickup on Request");
        $sel_drop = "";
        if (empty($wpsc_fedex_settings['DropoffType'])){
            $sel_drop = "BUSINESS_SERVICE_CENTER";
        }else{ $sel_drop = $wpsc_fedex_settings['DropoffType']; }

        foreach(array_keys($drop_types) as $dkey){
            $sel = "";
            if ($sel_drop == $dkey){
                $sel = 'selected="selected"';
            }
            $output .= "            <option value=\"".$dkey."\" ".$sel." >".$drop_types[$dkey]."</option>\n\r";
        }
        $output .= "        </select>\n\r";
        $output .= "    </td>\n\r";
        $output .= "</tr>\n\r";

        $output .= "<tr>\n\r";
        $output .= "    <td>".__('Destination Type', 'wpsc')."</td>\n\r";
        $output .= "    <td>\n\r";

        // Default is Residential
        $checked[0] = "checked='checked'";
        $checked[1] = "";
        if ($wpsc_fedex_settings['residential'] == "0"){
            $checked[0] = "";
            $checked[1] = "checked='checked'";
        }

        $output .= "        <label><input type='radio' {$checked[0]} value='1' name='wpsc_fedex_settings[residential]'/>".__('Residential Address', 'wpsc')."</label><br />\n\r";
        $output .= "        <label><input type='radio' {$checked[1]} value='0' name='wpsc_fedex_settings[residential]'/>".__('Commercial Address', 'wpsc')."</label>\n\r";
        $output .= "    </td>\n\r";
        $output .= "</tr>\n\r";
        $output .= "<tr>\n\r";

        $output .= ("<tr>
                         <td>".__('Developer Key', 'wpsc')." :</td>
                         <td>
                             <input type=\"text\" name='wpsc_fedex_settings[key]' value=\"".base64_decode($wpsc_fedex_settings['key'])."\" />
                         </td>
                     </tr>");
        $output .= ("<tr>
                        <td>".__('Password', 'wpsc')." :</td>
                        <td>
                            <input type=\"password\" name='wpsc_fedex_settings[password]' value=\"".base64_decode($wpsc_fedex_settings['password'])."\" />
                        </td>
                    </tr>");
        $output .= ("<tr>
                        <td>".__('Account #', 'wpsc')." :</td>
                        <td>
                            <input type=\"text\" name='wpsc_fedex_settings[account]' value=\"".base64_decode($wpsc_fedex_settings['account'])."\" />
                        </td>
                    </tr>");
        $output .= ("<tr>
                        <td>".__('Meter #', 'wpsc')." :</td>
                        <td>
                            <input type=\"text\" name='wpsc_fedex_settings[meter]' value=\"".base64_decode($wpsc_fedex_settings['meter'])."\" />
                            <br />
                            ".__('Don\'t have Fedex Credentials ?', 'wpsc')."
                                <a href=\"http://fedex.com/us/developer/index.html\" target=\"_blank\">".__('Click Here','wpsc')."</a>.
                        </td>
                    </tr>");
        $selected_env = $wpsc_fedex_settings['environment'];
        if ($selected_env == "1"){
            $env_test = "checked=\"checked\"";
        }
        $output .= ("
                    <tr>
                        <td><label for=\"fedex_env_test\" >".__('Use Testing Environment', 'wpsc')."</label></td>
                        <td>
                            <input type=\"checkbox\" id=\"fedex_env_test\" name=\"wpsc_fedex_settings[environment]\" value=\"1\" ".$env_test." /><br />
                        </td>
                    </tr>
                    ");
        return $output;
    }

    /**
     * Debug function used to log details to file
     * @access private
     *
     * @since 1.0 
     * @return none
     */
    function logIt($msg){
        // This is a great little function for debugging
        // When the WPSC-Fedex module is instantiated (the first time)
        // It will generate a log file name, just call
        // $this->logIt('message here');
        // to print to the file
        $file = fopen($this->logFile, 'a+');
        fwrite($file, $msg."\n");
    }
    /**
     * Updates the shipping modules options
     * @access private
     *
     * @since 1.0 
     * @return Boolean true on completion
     */
    function submit_form() {
        if ($_POST['wpsc_fedex_settings'] != ''){
            $temp = $_POST['wpsc_fedex_settings'];
            $temp['key'] = base64_encode($temp['key']);
            $temp['password'] = base64_encode($temp['password']);
            $temp['account'] = base64_encode($temp['account']);
            $temp['meter'] = base64_encode($temp['meter']);
            update_option('wpsc_fedex_settings', $temp);
        }
        return true;
    }

    /**
     * Builds the request array to be sent to fedex
     * @access private
     *
     * @since 1.0 
     * @return array of shipping details
     */
    function _buildRequest($data){
        global $wpdb;
        $config = get_option("wpsc_fedex_settings");

        $timestamp = date('c');

        $region_data = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`
                                    WHERE `".WPSC_TABLE_REGION_TAX."`.`id` = '".get_option('base_region')."' ",ARRAY_A);

        $state = (is_array($region_data)) ? $region_data[0]['code'] : "";

        $request['WebAuthenticationDetail'] = array('UserCredential' =>
                                                array('Key' => base64_decode($config['key']), 'Password' => base64_decode($config['password'])));
        $request['ClientDetail'] = array('AccountNumber' => base64_decode($config['account']), 'MeterNumber' => base64_decode($config['meter']));
        $request['TransactionDetail'] = array('CustomerTransactionId' => 'wpsc-fedex-'.session_id());
        $request['Version'] = array('ServiceId' => 'crs', 'Major' => '7', 'Intermediate' => '0', 'Minor' => '0');
        $request['ReturnTransitAndCommit'] = true;
        $request['RequestedShipment']['DropoffType'] = $config['DropoffType'];
        $request['RequestedShipment']['ShipTimestamp'] = date('c');
        // Service Type and Packaging Type are not passed in the request
        $request['RequestedShipment']['Shipper'] = array('Address' => array(
                                                         'StateOrProvinceCode' => $state,
                                                         'PostalCode' => get_option('base_zipcode'),
                                                         'CountryCode' =>get_option('base_country')
                                                         ));
        $request['RequestedShipment']['Recipient'] = array('Address' => array (
                                                       'StateOrProvinceCode' => $data["dest_state"],
                                                       'PostalCode' => $data['dest_zipcode'],
                                                       'CountryCode' => $data['dest_country'],
                                                       'Residential' => $config['residential']
                                                        )
                                                     );
        $request['RequestedShipment']['ShippingChargesPayment'] = array(
                                                                'PaymentType' => 'SENDER',
                                                                'Payor' => array(
                                                                            'AccountNumber' => base64_decode($config['account']),
                                                                            'CountryCode' => get_option('base_country')));
        $request['RequestedShipment']['RateRequestTypes'] = $config['rate_type'];
        //$request['RequestedShipment']['RequestedPackageDetailType'] = 'PACKAGE_SUMMARY';
        // Okay so Fedex only allows individul packages to be 150lbs. with that being the case,
        // We do a little math to smartly balance large weights into multiple packages.
        $weight   = 0;
        $numBoxes = 1;
           if($data['weight'] != 0){
            $numBoxes = ceil($data['weight']/150);
            $weight = ceil($data['weight']/$numBoxes);
        }else{
            $numBoxes = 1;
            $weight = 0.1;
        }
        if ($data["weight"] < 0.1){
            $data["weight"] = 0.1;
        }

        $request['RequestedShipment']['PackageCount'] = $numBoxes;
        //$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
        $request['RequestedShipment']['PackageDetail'] = 'PACKAGE_SUMMARY';
        $request['RequestedShipment']['PackagingType'] = $config["container"];
        $request['RequestedShipment']['TotalWeight'] = array(
            "Units" => "LB",
            "Value" => $data['weight']
        );

        $request['RequestedShipment']['ShippingChargesPayment']["Payment"] = array(
            "PaymentType" => "SENDER"
        );

        return $request;
    }
    /**
     * Sends request to fedex using nusoap
     * @access private
     *
     * @since 1.0 
     * @return array of response or empty array if response is invalid
     */
    function _makeRequest($request, $config){
        if(file_exists(WPSC_FILE_PATH.'/wpsc-includes/nusoap/nusoap.php')){
            require_once(WPSC_FILE_PATH.'/wpsc-includes/nusoap/nusoap.php');
        }else{
            require_once(FEDEX_FILE_PATH.'/nusoap/nusoap.php');
    }
    $proxy = $this->getProxy();

        $path = FEDEX_FILE_PATH."/".$this->service_wsdl;
        try{
            $client = new nusoap_client($path,true,$proxy['host'],
                                                   $proxy['port'],
                                                   $proxy['user'],
                                                   $proxy['pass']);

            $response = $client->call('getRates', array($request));
            if (array_key_exists('debug', $_GET)){
                if ($_GET['debug'] == 'true'){
                    // Uncomment below if you are debugging ONLY!!!! you do not
                    // want to give everyone access to your credentials!!!
                    //echo "<pre>".print_r($client)."</pre><br />";
                    //echo "<pre>".print_r($response)."</pre>";
                }
            }
        }catch(Exception $e){
            if (array_key_exists('debug', $_GET)){
                if ($_GET['debug'] == 'true'){
                    // Uncomment below if you are debugging ONLY!!!! you do not
                    // want to give everyone access to your credentials!!!
                    //echo "<pre>".$e."</pre>";
                }
            }else{
                    return array();
                }
        }
        if(is_array($response)){
            return $response;
        }else{
            return array();
        }
    }

    /**
     * Converts timestamp into human readable format
     * @access private
     *
     * @since 1.0 
     * @return formated date time
     */
    function _parseTime($stamp){
        $big_parts = explode("T", $stamp);
        $parts = explode("-",$big_parts[0]);
        $year = $parts[0];
        $month = $parts[1];
        $day = $parts[2];
        $time = mktime(0, 0, 0, $month  , $day, $year);
        return date('D M j', $time);
    }
    /**
     * Skips weekends and shows time plus the remaining weekend days till delivery
     * @access private
     *
     * @since 1.0 
     * @return string date
     */
    function futureDate($interval){
        //Wed Apr 7
        $date = date("Y-m-d");
        $interval = " +$interval day";
        $final = date("D M j",strtotime(date("Y-m-d", strtotime($date)).$interval));
        $test = explode(" ",$final);
        if ($test[0] == "Sat"){
            return $this->futureDate($interval+2);
        }else if($test[0] == "Sun"){
            return $this->futureDate($interval+1);
        }
        return $final;
    }
    /**
     * Parse fedex response 
     * @access private
     * @param array response from fedex
     * @param string rate type config set by shop owner
     * @since 1.0 
     * @return array of different rates
     */
    function _parseResponse($response, $RateTypeConfig){
        $toReturn = array();

        if (!array_key_exists('RateReplyDetails',$response)){
            return array();
        }

        // This If block should never be needed ... but its here b/c it has happened before
        if (array_key_exists("ServiceType",(array)$response["RateReplyDetails"])){
            $service = $response["RateReplyDetails"];
            // This means Fedex has decided to provide only one service. Dont we feel special.
            $serviceName  = ucwords(strtolower(str_replace('_',' ', $service['ServiceType'])));
            foreach ((array)$service['RatedShipmentDetails'] as $RatedShipmentDetail){
                //print "pulling details now<br />";
                // Pull the detail
                $RateDetail = $RatedShipmentDetail['ShipmentRateDetail'];
                $RateType = $RateDetail['RateType'];
                $currency = $RateDetail['TotalNetCharge'] ['Currency'];
                $amount = $RateDetail['TotalNetCharge']['Amount'];
                if ($RateType == "PAYOR_".$RateTypeConfig){
                    if (!array_key_exists($serviceName,$toReturn)){
                        $toReturn[$serviceName] = array($currency, $amount
                        //, $deliveryDate // Not supported in current base-code . I will be committing changes for this
                        );
                    }
                }
            }
        }else{
            foreach((array)$response["RateReplyDetails"] as $service){
                $serviceName  = ucwords(strtolower(str_replace('_',' ', $service['ServiceType'])));
                $shipmentDetails = (array)$service['RatedShipmentDetails'];
                $estDate = $service['DeliveryTimestamp'];
    //            $deliveryDate = "";
    //            if (!empty($estDate)){
    //                $deliveryDate = $this->_parseTime($estDate);
    //            }else{
    //                $deliveryDate = $this->futureDate(6);
    //            }
                if (array_key_exists('ShipmentRateDetail',$shipmentDetails)){
                    // Pull the detail
                    $RateDetail = $shipmentDetails['ShipmentRateDetail'];
                    $RateType = $RateDetail['RateType'];
                    $currency = $RateDetail['TotalNetCharge'] ['Currency'];
                    $amount = $RateDetail['TotalNetCharge']['Amount'];
                    if ($RateType == "PAYOR_".$RateTypeConfig){
                        if (!array_key_exists($serviceName,$toReturn)){
                            $toReturn[$serviceName] = array($currency, $amount
                            //, $deliveryDate // Not supported in current base-code . I will be committing changes for this
                            );
                        }
                    }
                }else{
                    // Iterate through the shipment details
                    foreach($shipmentDetails as $possible_rate){
                        // Pull the detail
                        $RateDetail = $possible_rate['ShipmentRateDetail'];
                        $RateType = $RateDetail['RateType'];
                        $currency = $RateDetail['TotalNetCharge']['Currency'];
                        $amount = $RateDetail['TotalNetCharge']['Amount'];
                        if ($RateType == "PAYOR_".$RateTypeConfig){
                            if (!array_key_exists($serviceName,$toReturn)){
                                $toReturn[$serviceName] = array($currency, $amount
                                //, $deliveryDate // Not supported in current base-code . I will be committing changes for this
                                );
                            }
                        }
                    }
                }
            } // End foreach
        }// End if more than one service provided 
        return $toReturn;
    }
    
    /**
     * Format services into a wp-e-commerce shipping quote standard
     * @access private
     *
     * @since 1.0
     * @param array services
     * @param boolean currency     
     * @return array of wp-e-commerce formatted shipping quotes
     */
    function _formatTable($services, $currency=false){
        /* The checkout template expects the array to be in a certain
         * format. This function will iterate through the provided
         * services array and format it for use. During the loop
         * we take advantage of the loop and translate the currency
         * if necessary based off of what Fedex tells us they are giving us
         * for currency and what is set for the main currency in the settings
         * area
         */
        $converter = null;
        if ($currency){
            $converter = new CURRENCYCONVERTER();
        }
        $finalTable = array();
        foreach(array_keys($services) as $service){
            if ($currency != false && $currency != $services[$service][0]){
                $temp =$services[$service][1];
                $services[$service][1] = $converter->convert($services[$service][1],
                                                             $currency, $services[$service][0]);
            }
            /*$finalTable[$service] = array('price'=>$services[$service][1],
                                          'time'=>$services[$service][2]);*/
            $finalTable[$service] = $services[$service][1];
        }
        return $finalTable;
    }

    function resetSession($newVars){
        $session = array("delivery_country","wpsc_zipcode","wpsc_state");
        foreach($session as $var){
            $_SESSION[$var] = $newVars[$var];
        }
        $_SESSION['wpsc_shipping_cache_check'] = array();
        $_SESSION['wpsc_shipping_cache'][$this->internal_name] = array();
    }


    function checkSession($POST){
        global $wpdb;
        if (!empty($POST["country"])){
            if (!empty($_SESSION["delivery_country"])){
                if ($POST["country"] != $_SESSION["delivery_country"]){
                    $this->resetSession(array("delivery_country"=>$POST["country"],
                                              "wpsc_zipcode"=>"",
                                              "wpsc_state"  =>"")
                                        );
                }
            }else{
                $_SESSION["delivery_country"] = $POST["country"];
            }
        }
        if (!empty($POST["region"])){
            // Need to get the state from the code given
            $query ="SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`
                                WHERE `".WPSC_TABLE_REGION_TAX."`.`id` = '".$_POST['region']."'";
            $dest_region_data = $wpdb->get_results($query, ARRAY_A);
            $state = (is_array($dest_region_data)) ? $dest_region_data[0]['code'] : "";
        
            if (!empty($_SESSION["wpsc_state"])){
                if ($state != $_SESSION["wpsc_state"]){
                    $this->resetSession(array("delivery_country"=>$_SESSION["delivery_country"],
                                              "wpsc_zipcode"=>"",
                                              "wpsc_state"  =>$state)
                                        );
                }
            }else{
                $_SESSION["wpsc_state"] = $state;
            }
        }
        if (!empty($POST["zipcode"])){
            if (!empty($_SESSION["wpsc_zipcode"])){
                if ($POST["zipcode"] != $_SESSION["wpsc_zipcode"]){
                    $this->resetSession(array("delivery_country"=>$_SESSION["delivery_country"],
                                              "wpsc_zipcode"=>$POST["zipcode"],
                                              "wpsc_state"  =>$_SESSION["wpsc_state"])
                                        );
                }
            }else{
                $_SESSION["wpsc_zipcode"] = $POST["zipcode"];
            }
        }
    }
    
    /**
     * Call made by wp-e-commerce wpsc_cart class for quotes
     * @access private
     *
     * @since 1.0
     * @return array of quotes
     */
    function getQuote(){
        global $wpdb;
        $data = array();
        $config = get_option("wpsc_fedex_settings");
        // ########### SESSION HANDLING ############## \\
        $this->checkSession($_POST);
        //\\#########################################\\//
        $data["dest_country"] = $_SESSION["delivery_country"];
        $data["dest_state"]   = $_SESSION["wpsc_state"];
        $data["dest_zipcode"] = $_SESSION["wpsc_zipcode"];
        $data['weight'] = wpsc_cart_weight_total();
        
        $shipping_cache_check['state'] = $data['dest_state'];
        $shipping_cache_check['zipcode'] = $data['dest_zipcode'];
        $shipping_cache_check['weight'] = $data['weight'];
        
        // If any values are empty return nothing!
        foreach($data as $key=>$val){
            if (empty($val)){
                if ($key == "dest_state" && $data["dest_country"] == "US"){
                    return array();
                }elseif($key == "dest_state"){
                    continue;
                }else{
                    return array();
                }
            }
        }

        if(array_key_exists('debug', $_GET)) {
            if ($_GET['debug'] == 'true'){
                // Uncomment below if you need to debug
                // This is a security risk if you are using your
                // production Info
                //echo('<pre>'.print_r($config,true).'</pre>');
            }
        }
        // Initialize the rate table \\
        $rate_table = array();
        // Check the session, dont want to hit the API more than once
        if(($_SESSION['wpsc_shipping_cache_check'] === $shipping_cache_check) &&
                (
                    ($_SESSION['wpsc_shipping_cache'][$this->internal_name] != null)
                &&
                    (!empty($_SESSION['wpsc_shipping_cache'][$this->internal_name]))
                )
            ){
            $rate_table = $_SESSION['wpsc_shipping_cache'][$this->internal_name];
            return $rate_table;
        } else {
            $wpsc_options = get_option("wpsc_options");
            // Preferred Currency to display
            $currency = "USD";
            $currency_data = $wpdb->get_row("SELECT `code`
                                             FROM `".WPSC_TABLE_CURRENCY_LIST."`
                                             WHERE `isocode`='".$wpsc_options['currency_type']."'
                                             LIMIT 1",ARRAY_A) ;
            if ($currency_data){
                $currency = $currency_data['code'];
            }

            $request = $this->_buildRequest($data);
            $response = $this->_makeRequest($request, $config);
            $quotes =  $this->_parseResponse($response, $config['rate_type']);
            
            $rate_table = $this->_formatTable($quotes, $currency);
        }

        asort($rate_table);
        $_SESSION['wpsc_shipping_cache_check'] = $shipping_cache_check;
        $_SESSION['wpsc_shipping_cache'][$this->internal_name] = $rate_table;
        
        return $rate_table;
    }

    /**
     * Get item shipping currently unused
     * @access private
     *
     * @since 1.0
     * @return none
     */
    function get_item_shipping(&$cartitem){
    }
}

/**
 * instantiates fedex module and adds it to the global wpsc_shipping_modules
 * @access public
 *
 * @since 1.5.2
 * @param array of shipping modules
 * @return array of shipping modules
 */
function fedex_add_module() {
    global $wpsc_shipping_modules,$fedex;
    $fedex = new fedex();
    $wpsc_shipping_modules[$fedex->getInternalName()] = $fedex;
    return $wpsc_shipping_modules;
}
fedex_add_module();
//adds fedex to the list of shipping modules
//add_filter('wpsc_shipping_modules', 'fedex_add_module');
?>
