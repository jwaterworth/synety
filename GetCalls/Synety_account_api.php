<?php

/**
 * This is the ACCOUNT level class, used for API calls for a specific account
 *
 * @author SYNETY
 */
class Synety_account_api {
    
    const API_BASE = "https://api.synety.com/";
    private $connection_headers;
    private $connection_params;
    
    public function __construct() {
        
        require_once("config.php");
        
        // connection headers needed for each API call
        $this->connection_headers = array(
            "username: " . USERNAME,
            "password: " . PASSWORD,
            "licensekey: " . LICENSE
        );
        
        // connection parameters used in API URLs and JSON
        $this->connection_params = array(
           "username" => USERNAME
        );
        
    }
    
    /**
     * Function to handle all of the requests with cURL
     *
     * @param string $api_url
     * @param array $p_curl_options
     * @return json | @return int
     */
    
    private function handle_request($api_url, $p_curl_options = NULL) {
        
        try {
        
            $ch = curl_init($api_url);

            // base curl options
            $curl_options = array(
                CURLOPT_HTTPHEADER => $this->connection_headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
            );
            
            // add extra curl options for POSTs, PUTs and DELETEs
            if ( isset($p_curl_options) ) {
                $curl_options = $curl_options + $p_curl_options;
            }
            
            curl_setopt_array($ch, $curl_options);

            $api_response = curl_exec($ch);
            $api_response_info = curl_getinfo($ch);

            // throw an exception for http codes other than 200
            if ( $api_response_info["http_code"] != 200 ) {
                throw new Exception("API Error", $api_response_info["http_code"]);
            }
            
            curl_close($ch);

            // testing purposes only. respond with http code if method is close call
            if ( preg_match("/CloseCall/", $api_url) ) {
                return $api_response_info["http_code"];
            }
            
            // if DELETE, respond with http code
            if ( isset($curl_options[10036]) && $curl_options[10036] == "DELETE" ) {
                return $api_response_info["http_code"];
            }
            
            // otherwise, respond with JSON
            else {
                return $api_response;
            }
        
        }
        
        catch (Exception $e) {
            echo "<p>" . $e->getMessage() . " with error code: " . $e->getCode() . "</p>"; 
            
            if ( curl_error($ch) != "" ) {
                echo "Curl error: " . curl_error($ch);
            }
            
        }
        
    }
    
    /**
     * Get account's information
     * 
     * @return json 
     */
    
    public function get_account_info() {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"];

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Make a call between two numbers, associated with the account
     * 
     * @param string $cli
     * @param string $cld
     * @return json 
     */
    
    public function make_call($cli, $cld) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls";
            
        $call_body = array("CLD" => $cld, "CLI" => $cli, "AccountID" => $this->connection_params["username"]); 
        $json_body = json_encode($call_body);
        
        $curl_options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json_body
        );
        
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Get a single call record out
     * 
     * @param int $call_id
     * @return json 
     */
    
    public function get_single_call($call_id) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls/$call_id";
        
        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Get a collection of calls out, offset and limited
     * Additional filters: Recorded, AccountNumber, Number, Direction, Contact, Category, Note
     * 
     * @param int $offset
     * @param int $limit
     * @param string $from
     * @param string $to
     * @return json 
     */
    
    public function get_calls($offset = NULL, $limit = NULL, $from = NULL, $to = NULL) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls?offset=$offset&limit=$limit&from=$from&to=$to";

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Get a collection of failed calls out
     * 
     * @param int $offset
     * @param int $limit
     * @param string $from
     * @param string $to
     * @return json 
     */
    
    public function get_failed_calls($offset = NULL, $limit = NULL, $from = NULL, $to = NULL) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/FailedCalls?offset=$offset&limit=$limit&from=$from&to=$to";

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Update a call record, must first get the call out and give it back to the API with updated values
     * 
     * @param int $call_id
     * @param string $call_note
     * @param int $category_id
     * @return json 
     */
    
    public function update_call($call_id, $call_note, $category_id) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls/$call_id";
        
        // first request, get call info
        $response = $this->handle_request($api_url);
        
        $call_obj = json_decode($response, true);
        $call_obj["Note"] = $call_note;
        $call_obj["CategoryID"] = $category_id;

        $call_obj = json_encode($call_obj);
        
        $curl_options = array(
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $call_obj
        );
        
        // second request, update
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Get a collection of devices out
     * 
     * @return json 
     */
    
    public function get_devices() {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Devices";

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Add a device to the account
     * 
     * @param string $device_name
     * @param string $device_number
     * @param int $device_pin
     * @return json 
     */
    
    public function add_device($device_name, $device_number, $device_pin) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Devices";

        $device_body = array(
                        "Description" => $device_name, 
                        "Number" => $device_number, 
                        "PIN" => $device_pin, 
                        "AccountID" => $this->connection_params["username"]
        );
            
        $json_body = json_encode($device_body);
        
        $curl_options = array(
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $json_body
        );
        
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Delete one of the account's devices
     * 
     * @param int $device_id
     * @return string 
     */
    
    public function delete_device($device_id) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Devices/$device_id";

        $curl_options = array(
            CURLOPT_CUSTOMREQUEST => "DELETE"
        );
        
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Get a call recording file out
     * 
     * @param int $call_id 
     */
    
    public function get_recording($call_id) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls/$call_id/Recording";
        
        $response = $this->handle_request($api_url);
        
        header("Content-Type: audio/wav");
        header("Pragma: cache");
        header("Content-Disposition: attachment; filename=\"recording_$call_id.wav\"");

        echo $response;
        
    }
    
    /**
     * Get a collection of categories out
     * 
     * @return json 
     */
    
    public function get_categories() {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Categories";

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Get a collection of contacts out
     * 
     * @param int $offset
     * @param int $limit
     * @return json 
     */
    
    public function get_contacts($offset = NULL, $limit = NULL) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Contacts?offset=$offset&limit=$limit";
        
        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Get a single contact out
     * 
     * @param int $contact_id
     * @return json 
     */
    
    public function get_single_contact($contact_id) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Contacts/$contact_id";

        $response = $this->handle_request($api_url);
        return $response;
        
    }
    
    /**
     * Update a contact, must first get contact info out and give it back to the API with updated values
     * 
     * @param int $contact_id
     * @param string $contact_name
     * @param string $contact_number
     * @param string $contact_company
     * @return json 
     */
    
    public function update_contact($contact_id, $contact_name, $contact_number, $contact_company) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Contacts/$contact_id";
        
        // first request, get contact info
        $response = $this->handle_request($api_url);
        
        $contact_obj = json_decode($response, true);
            
        $contact_obj["Name"] = $contact_name;
        $contact_obj["Number"] = $contact_number;
        $contact_obj["Company"] = $contact_company;

        $contact_obj = json_encode($contact_obj);
        
        $curl_options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contact_obj
        );
        
        // second request, update
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Add a contact
     * 
     * @param int $i_customer
     * @param string $contact_name
     * @param string $contact_number
     * @param string $contact_company
     * @return json 
     */
    
    public function add_contact($i_customer, $contact_name, $contact_number, $contact_company) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Contacts";

        $contact_body = array(
            "Name" => $contact_name,
            "Number" => $contact_number,
            "Company" => $contact_company,
            "AccountID" => USERNAME,
            "i_customer" => $i_customer
        );
        
        $contact_json = json_encode($contact_body);
        
        $curl_options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contact_json
        );
        
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
     * Add a collection of contacts
     * 
     * @param json $contacts_json
     * @return json 
     */
    
    public function add_multiple_contacts($contacts_json) {
        
        $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Contacts";
        
        $curl_options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contacts_json
        );
        
        $response = $this->handle_request($api_url, $curl_options);
        return $response;
        
    }
    
    /**
         * Close a call by posting its session id
         * 
         * @param string $password
         * @param string $session_id
         * @return string
         * 
         */
        
        public function close_call($session_id) {
            
            $api_url = self::API_BASE . "Accounts/" . $this->connection_params["username"] . "/Calls/CloseCall?SessionID=" . $session_id;
            
            $curl_options = array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ""
            );
            
            $response = $this->handle_request($api_url, $curl_options);
            return $response;
            
        }
    
}

?>
