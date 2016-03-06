<?php

require_once('lib/OAuth.php');

$_consumerKey= "";
$_consumerSecret = "";
$_token = "";
$_tokenSecret = "";

function request($api, $params) {
	global $_consumerKey, $_consumerSecret, $_token, $_tokenSecret;

	$unsigned_url = "https://api.yelp.com/v2/" . $api . ($params == null ? "" : ("?" . $params));

	// Token object built using the OAuth library
	$token = new OAuthToken($_token, $_tokenSecret);

	// Consumer object built using the OAuth library
	$consumer = new OAuthConsumer($_consumerKey, $_consumerSecret);

	// Yelp uses HMAC SHA1 encoding
	$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

	$oauthrequest = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $unsigned_url);

	// Sign the request
	$oauthrequest->sign_request($signature_method, $consumer, $token);

	// Get the signed URL
	$signed_url = $oauthrequest->to_url();

	// Send Yelp API Call
	try {
		$ch = curl_init($signed_url);
		if (FALSE === $ch) {
		    throw new Exception('Failed to initialize');
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);

		if (FALSE === $data) {
		    throw new Exception(curl_error($ch), curl_errno($ch));
		}
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (200 != $http_status) {
		    throw new Exception($data, $http_status);
		}

		curl_close($ch);
	} catch(Exception $e) {
		trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
	}

	return $data;
}

if (isset($_REQUEST['q'])) {
        if ($_REQUEST['q'] == 'business') {
                echo request("business/".$_REQUEST['biz_id'], null);
        } elseif ($_REQUEST['q'] == 'search') {
                $url_params = array();
                $url_params['bounds'] = $_REQUEST['bounds'];
                $url_params['category_filter'] = $_REQUEST['category_filter'];
                echo request("search", http_build_query($url_params));
        }
        exit;
}

?>
