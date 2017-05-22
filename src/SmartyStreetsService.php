<?php

namespace FireEngineRed\SmartyStreetsLaravel;

use Log;
use Config;

class SmartyStreetsService {

    public $request;
    public $response;
    public $endpoint;
    public $failureCallback;
    protected $optionalRequestHeaders;
    private $associatedIds;
    private $prev_request;
    
    public function __construct() 
    {
        $this->request = [];
        $this->endpoint = Config::get('smartystreets.endpoint');
        
        $this->failureCallback = Config::get('smartystreets.failureCallback');
        $this->optionalRequestHeaders = Config::get('smartystreets.optionalRequestHeaders');
    }
    
    public function setFailureCallback($yourCallback) {
        //can be set dynamically here, or set staticly in the config file. Anything that is_callable().
        $this->failureCallback = $yourCallback;
    }
    
    public function setOptionalRequestHeader($k, $v) {
        $this->optionalRequestHeaders[$k] = $v;
    }
    
    //only takes one address, and only returns the first candidate (if present)
    public function addressQuickVerify($address, $associatedId = 0) 
    {
        $response = [];
        if($this->validateAddressInputs($address)) {
            $address['candidates'] = 1;
            $this->addressAddToRequest($address, $associatedId);
            $this->addressVerify();
            $candidates = $this->addressGetCandidates(0);
            if(!empty($candidates) && !empty($candidates[0])) {
                $response = $candidates[0];
            }
        }
        return $response;
    }
    
    public function validateAddressInputs($a) {
        if(!empty($a['street']) && !empty($a['zipcode']))
            return true;
        if(!empty($a['street']) && !empty($a['city']) && !empty($a['state']))
            return true;

        return false;
    }

    public function addressAddToRequest($address, $associatedId = 0) 
    {
        if($this->validateAddressInputs($address)) {
            foreach($address as $k => $v) {
                //must be proper data types or API says "400 Bad Request (Malformed Payload)"
                if($k == 'candidates') $address[$k] = (int) $v;
                else $address[$k] = (string) $v;
            }
            $inputIndex = count($this->request);
            $this->request[] = $address;
            $this->associatedIds[] = $associatedId;
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

        $rawJsonResponseString = $this->post($url, $jsonRequest);
        return $this->response = json_decode($rawJsonResponseString, 1);
    }
    
    public function addressGetCandidates($inputIndex) 
    {
        $candidates = array();
        if(!empty($this->response) && is_array($this->response)) {
            foreach($this->response as $k => $candidate) {
                if(isset($candidate['input_index']) && $candidate['input_index'] == $inputIndex) {
                    $candidates[] = $candidate;
                }
            }
        }
        if(empty($candidates)) {
            if(is_callable($this->failureCallback)) {
                return call_user_func($this->failureCallback, 'candidates', [
                    $inputIndex, $candidates, $this->associatedIds
                ]);
                /*  //maybe your callback includes something like this:
                    Log::warning('Warning: No address candidates found for $inputIndex', [$inputIndex, $candidates]);
                */
            }
        }
        return $candidates;
    }
    
    public function post($url, $postdata) {
        $ch = curl_init();
        $httpHeaders = ['Content-Type: application/json'];
        foreach($this->optionalRequestHeaders as $k => $v) {
            if($v) {
                $httpHeaders[] = "$k: true";
            }
        }
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_VERBOSE => false,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $postdata
        );
        curl_setopt_array($ch, $options);
        $rawJsonResponseString = curl_exec($ch);
        $rawJsonResponseString = trim($rawJsonResponseString);
        $jsonDecoded = json_decode($rawJsonResponseString, 1);
        $curl_info = curl_getinfo($ch);

        /**
         * save off request in case it's needed when we check
         * for individual candidates in $this->addressGetCandidates()
         */
        $this->prev_request = $this->request;
        $this->request = []; //reset the request for the next pass.

        if($curl_info['http_code'] == '200' && strlen($rawJsonResponseString) && is_array($jsonDecoded)) {
            return $rawJsonResponseString;
        }
        else {
            if(is_callable($this->failureCallback)) {
                return call_user_func($this->failureCallback, 'curl', [
                    $postdata, $curl_info, $rawJsonResponseString, $this->associatedIds
                ]);
                /*  //maybe your callback includes something like this:
                    Log::warning("SmartyStreets cURL failed!", [$postdata, $curl_info, $rawJsonResponseString]);
                */
            }
            return false;
        }
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
