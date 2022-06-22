<?php

/**
 * Class to get price data from Whitecap
 *
 * Example usage
 *
 * <?php
 *
 * $api = new \WhitecapApi();
 * $price = $api->fetchProductPriceFromSku($sku);
 *
 * @category   Pricing
 * @package    WhitecapApi
 * @version    Release: 2022.03.29
 */

namespace App\Services;

class WhitecapApi
{

	protected $response_contents;
	protected $response_decoded;
	protected $response_info;

	public function __construct()
	{
	}

	/**
	 * Set last request contents
	 *
	 * @param string	$value the contents
	 * @return null
	 */
	public function setResponseContents(String $value)
	{
		$this->response_contents = $value;
	}
	/**
	 * Get last request contents
	 *
	 * @return string	the contents
	 */
	public function getResponseContents()
	{
		return $this->response_contents;
	}

	/**
	 * Set last request contents
	 *
	 * @param string	$value the contents
	 * @return null
	 */
	public function setResponseDecoded(array $value)
	{
		$this->response_decoded = $value;
	}
	/**
	 * Get last request contents
	 *
	 * @return string	the contents
	 */
	public function getResponseDecoded()
	{
		return $this->response_decoded;
	}

	/**
	 * Set last request contents
	 *
	 * @param string	$value the contents
	 * @return null
	 */
	public function setResponseInfo(array $value)
	{
		$this->response_info = $value;
	}
	/**
	 * Get last request contents
	 *
	 * @return string	the contents
	 */
	public function getResponseInfo()
	{
		return $this->response_info;
	}

	/**
	 * Fetch product's price using SKU
	 *
	 * @param string	$sku the SKU to get data with
	 * @return null		if no product is found
	 * @return float	the product price
	 */
	public function fetchProductPriceFromSku($sku)
	{
		$method = 'GET';
		$url = "https://www.whitecap.com/api/search?query=$sku";
		$data = null;
		$headers = [
			'accept: */*',
			'accept-encoding: gzip, deflate, br',
			'accept-language: en',
			'content-type: application/json',
			'referer: https://www.whitecap.com/',
			'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.82 Safari/537.36',
		];
		$options = [
			CURLOPT_TIMEOUT => 8,
		];
		$this->sendRequestAndStoreResponse($method, $url, $data, $headers, $options);
		$decoded = $this->getResponseDecoded();
		$price = null;
		if (!empty($decoded)) {
			if (isset($decoded['Entries'][0]['Variants'][0]['Prices']['FinalPrice']['Value']))
				$price = (float)$decoded['Entries'][0]['Variants'][0]['Prices']['FinalPrice']['Value'];
		}
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
	public function sendRequest($method, $url, $data = array(), $headers = array(), $options = array())
	{
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
		if ($method == 'GET')
			$options = array_replace($options, array(CURLOPT_CUSTOMREQUEST => 'GET'));
		if ($method == 'POST')
			$options = array_replace($options, array(CURLOPT_POST => true));
		if (!empty($data))
			$options = array_replace($options, array(CURLOPT_POSTFIELDS => $data));
		if (!empty($headers))
			$options = array_replace($options, array(CURLOPT_HTTPHEADER => $headers));
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$response = [];
		$response['contents'] = curl_exec($ch);
		$response['decoded'] = [];
		if (@$response['contents'][0] === '{') {
			$decoded = json_decode($response['contents'], true);
			if (is_array($decoded))
				$response['decoded'] = $decoded;
		}
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
	public function sendRequestAndStoreResponse($method, $url, $data = array(), $headers = array(), $options = null)
	{
		$response = $this->sendRequest($method, $url, $data, $headers, $options);
		$this->setResponseContents($response['contents']);
		$this->setResponseDecoded($response['decoded']);
		$this->setResponseInfo($response['info']);
		return $response;
	}
}
