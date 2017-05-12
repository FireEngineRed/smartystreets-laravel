<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| API Keys
	|--------------------------------------------------------------------------
	|
	*/
	'authId' 	=> 'raw ID here',
	'authToken'	=> 'raw token here',


	/*
	|--------------------------------------------------------------------------
	| Endpoint
	|--------------------------------------------------------------------------
	|
	*/
	'endpoint' => 'https://api.smartystreets.com',
	
	/*
	|--------------------------------------------------------------------------
	| cURL Failure Callback
	| 
	| If you don't get back an HTTP 200, you can use this to handle the failure
	| https://smartystreets.com/docs/address#http-response-status
	|--------------------------------------------------------------------------
	|
	*/
	'failureCallback' => null,

	/*
	|--------------------------------------------------------------------------
	| Optional HTTP request headers
	| https://smartystreets.com/docs/address#http-request-headers
	|--------------------------------------------------------------------------
	|
	*/
	'optionalRequestHeaders' => [
    	//Regardless if the address actually exists, format it as if it did. For example: "523 Walnut St" when only 524 and 520 Walnut exist.
    	'X-Standardize-Only' => false,
    	//Very aggressive match finding; may include results that aren't really valid.
    	'X-Include-Invalid' => false,
	],
);
