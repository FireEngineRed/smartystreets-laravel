<?php

namespace FireEngineRed\SmartyStreetsLaravel;

use App;
use Log;
use Illuminate\Support\Facades\Config;

class SmartyStreetsService {

    public $request;
    public $response;
    public $endpoint;
    
    public function __construct() 
    {
        $this->request = array();
        $this->endpoint = Config::get('smartystreets.endpoint');
    }
    
    //only takes one address, and only returns the first candidate (if present)
    public function addressQuickVerify($address) 
    {
        $response = array();
        if($this->validateAddressInputs($address)) {
            $address['candidates'] = 1;
            $this->addressAddToRequest($address);
            $this->addressVerify();
            $candidates = $this->addressGetCandidates(0);
            if(!empty($candidates) && !empty($candidates[0])) {
                $response = $candidates[0];
            }
        }
        return $response;
    }
    
    public function validateAddressInputs($a) {
        if(!empty($a['street']) && !empty($a['city']) && !empty($a['state']))
            return true;
        if(!empty($a['street']) && !empty($a['zipcode']))
            return true;

        return false;
    }

    public function addressAddToRequest($address) 
    {
        if($this->validateAddressInputs($address)) {        
            $inputIndex = count($this->request);
            $this->request[] = $address;
            return $inputIndex;
        }
        return false;
    }

    public function buildAddressVerifyUrl() 
    {
        $path = '/street-address';
        $query = http_build_query( array(
            'auth-id' => Config::get('smartystreets.authId'),
            'auth-token' => Config::get('smartystreets.authToken'),
        ));
        
        return $this->endpoint.$path.'/?'.$query;
    }
    
    public function addressVerify() 
    {
        $url = $this->buildAddressVerifyUrl();
        $jsonRequest = json_encode($this->request);

        $jsonResponse = $this->post($url, $jsonRequest);
        return $this->response = json_decode($jsonResponse, 1);
    }
    
    public function addressGetCandidates($inputIndex) 
    {
        $candidates = array();
        if(!empty($this->response) && is_array($this->response)) {
            foreach($this->response as $k => $candidate) {
                if($candidate['input_index'] == $inputIndex) {
                    $candidates[] = $candidate;
                }
            }
        }
        else {
            Log::warning('Warning: No address candidates returned from SmartyStreets.');
        }
        if(empty($candidates)) {
            Log::warning('Warning: No address candidates found for $inputIndex '.$inputIndex);
        }
        return $candidates;
    }
    
    public function post($url, $postdata) {
        $ch = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $postdata
        );
        curl_setopt_array($ch, $options);
        $json_output = curl_exec($ch);
/*
        //For your debug purposes
        Log::info("SmartyStreets cURL request: ", array($postdata));
        Log::info("SmartyStreets cURL response meta: ",curl_getinfo($ch));
        Log::info("SmartyStreets cURL response: ", array($json_output));
*/
        return trim($json_output);
        /*
Example parsed JSON:
            
Array
(
    [0] => stdClass Object
        (
            [input_index] => 0
            [candidate_index] => 0
            [delivery_line_1] => PO Box 1017
            [last_line] => Havertown PA 19083-0017
            [delivery_point_barcode] => 190830017173
            [components] => stdClass Object
                (
                    [primary_number] => 1017
                    [street_name] => PO Box
                    [city_name] => Havertown
                    [state_abbreviation] => PA
                    [zipcode] => 19083
                    [plus4_code] => 0017
                    [delivery_point] => 17
                    [delivery_point_check_digit] => 3
                )

            [metadata] => stdClass Object
                (
                    [record_type] => P
                    [zip_type] => Standard
                    [county_fips] => 42045
                    [county_name] => Delaware
                    [carrier_route] => B005
                    [congressional_district] => 07
                    [rdi] => Residential
                    [elot_sequence] => 0001
                    [elot_sort] => A
                    [latitude] => 39.97191
                    [longitude] => -75.28701
                    [precision] => Zip6
                    [time_zone] => Eastern
                    [utc_offset] => -5
                    [dst] => 1
                )

            [analysis] => stdClass Object
                (
                    [dpv_match_code] => Y
                    [dpv_footnotes] => AABB
                    [dpv_cmra] => N
                    [dpv_vacant] => N
                    [active] => Y
                    [footnotes] => N#
                )

        )

)
        */
    }
}
