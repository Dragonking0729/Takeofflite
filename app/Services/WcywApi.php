<?php
/**
 * Class to get price data from wireandcableyourway
 *
 * Example usage
 *
 * <?php
 *
 * $api = new \WcywApi();
 * $price = $api->fetchProductPriceFromSku($sku);
 *
 * @category   Pricing
 * @package    WcywApi
 * @version    Release: 2022.05.02
 */

namespace App\Services;

class WcywApi {

	protected $response_contents;
	protected $response_decoded;
	protected $response_info;

	public function __construct(){

	}

	/**
     * Set last request contents
     *
     * @param string	$value the contents
     * @return null
     */
	public function setResponseContents(String $value){
		$this->response_contents = $value;
	}
	/**
     * Get last request contents
     *
     * @return string	the contents
     */
	public function getResponseContents(){
		return $this->response_contents;
	}

	/**
     * Set last request contents
     *
     * @param string	$value the contents
     * @return null
     */
	public function setResponseDecoded(Array $value){
		$this->response_decoded = $value;
	}
	/**
     * Get last request contents
     *
     * @return string	the contents
     */
	public function getResponseDecoded(){
		return $this->response_decoded;
	}

	/**
     * Set last request contents
     *
     * @param string	$value the contents
     * @return null
     */
	public function setResponseInfo(Array $value){
		$this->response_info = $value;
	}
	/**
     * Get last request contents
     *
     * @return string	the contents
     */
	public function getResponseInfo(){
		return $this->response_info;
	}

	/**
     * Fetch product's price using SKU
     *
     * @param string	$sku the SKU to get data with
     * @return null		if no product is found
     * @return float	the product price
     */
	public function fetchProductPriceFromSku($sku){
		$method = 'GET';
		$url = "https://eucs27.ksearchnet.com/cloud-search/n-search/search";
		$params = [
			'ticket' => 'klevu-163062142406613725',
			'term' => $sku,
			'paginationStartsFrom' => '0',
			'sortPrice' => 'false',
			'ipAddress' => 'undefined',
			'analyticsApiKey' => '',
			'showOutOfStockProducts' => 'true',
			'klevuFetchPopularTerms' => 'false',
			'klevu_priceInterval' => '500',
			'fetchMinMaxPrice' => 'true',
			'klevu_multiSelectFilters' => 'true',
			'noOfResults' => '1',
			'klevuSort' => 'rel',
			'enableFilters' => 'false',
			'layoutVersion' => '2.0',
			'autoComplete' => 'false',
			'autoCompleteFilters' => 'category',
			'filterResults' => '',
			'visibility' => 'search',
			'klevu_filterLimit' => '50',
			'sv' => '2316',
			'lsqt' => '',
			'responseType' => 'json',
			'klevu_loginCustomerGroup' => '',
		];
		$url = $url.'?'.http_build_query($params);
		$data = null;
		$headers = [
			'accept: */*',
			'accept-encoding: gzip, deflate, br',
			'accept-language: en-US,en;q=0.9,da;q=0.8',
			'origin: https://www.wireandcableyourway.com',
			'referer: https://www.wireandcableyourway.com/',
			'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36',
		];
		$options = [
			CURLOPT_TIMEOUT => 8,
		];
		$this->sendRequestAndStoreResponse($method, $url, $data, $headers, $options);
		$decoded = $this->getResponseDecoded();
		$price = null;
		if(isset($decoded['result'][0]['salePrice']))
			$price = (float)$decoded['result'][0]['salePrice'];
		return $price;
	}

	/**
     * Send request using cURL
     *
     * @param string	$method request method with. e.g. GET, POST
     * @param string	$url request url
     * @param string	$data request data
     * @param string	$headers request headers
     * @param string	$options request cURL options
     * @return array	array containing contents, decoded(if is JSON), and info
     */
	public function sendRequest($method, $url, $data=array(), $headers=array(), $options=array()){
		$default_options = array(
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
			CURLOPT_REFERER => 'http://google.com',
			CURLOPT_VERBOSE => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => 0,
			CURLOPT_POST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_CONNECTTIMEOUT => 100,
			CURLOPT_TIMEOUT => 100,
			CURLOPT_ENCODING => 'GZIP',
			CURLOPT_COOKIEJAR => FALSE,
			CURLOPT_COOKIEFILE => FALSE,
		);
		$options = array_replace($default_options, $options);
		$options = array_replace($options, array(CURLOPT_URL => $url));
		if($method=='GET')
			$options = array_replace($options, array(CURLOPT_CUSTOMREQUEST => 'GET'));
		if($method=='POST')
			$options = array_replace($options, array(CURLOPT_POST => true));
		if(!empty($data))
			$options = array_replace($options, array(CURLOPT_POSTFIELDS => $data));
		if(!empty($headers))
			$options = array_replace($options, array(CURLOPT_HTTPHEADER => $headers));
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$response = [];
		$response['contents'] = curl_exec($ch);
		$response['decoded'] = $response['contents'];
		if($response['decoded'][0] === '{')
			$response['decoded'] = json_decode($response['contents'], true);
		if(!is_array($response['decoded']))
			$response['decoded'] = [];
		$response['info'] = curl_getinfo($ch);
		curl_close($ch);
		return $response;
	}

	/**
     * Send request using cURL and store response
     *
     * @param string	$method request method with. e.g. GET, POST
     * @param string	$url request url
     * @param string	$data request data
     * @param string	$headers request headers
     * @param string	$options request cURL options
     * @return array	array containing contents, decoded(if is JSON), and info
     */
	public function sendRequestAndStoreResponse($method, $url, $data=array(), $headers=array(), $options=null){
		$response = $this->sendRequest($method, $url, $data, $headers, $options);
		$this->setResponseContents($response['contents']);
		$this->setResponseDecoded($response['decoded']);
		$this->setResponseInfo($response['info']);
		return $response;
	}

}
