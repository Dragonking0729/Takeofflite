<?php

/**
 * Class to get price data from Grainger
 *
 * Example usage
 *
 * <?php
 *
 * $api = new \GraingerApi();
 * $price = $api->fetchProductPriceFromSku($sku);
 *
 * @category   Pricing
 * @package    GraingerApi
 * @version    Release: 2022.05.02
 */

namespace App\Services;

class GraingerApi
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
		$url = "https://www.grainger.com/api/v1/product/$sku";
		$data = null;
		$headers = [
			'ClientId: ANDROID',
			'User-Agent: grainger_android/8.2 Mobile',
			'Host: www.grainger.com',
			'Connection: Keep-Alive',
			'Accept-Encoding: gzip',
		];
		$options = [
			CURLOPT_TIMEOUT => 8,
		];
		$this->sendRequestAndStoreResponse($method, $url, $data, $headers, $options);
		$decoded = $this->getResponseDecoded();
		$price = null;
		if (isset($decoded['priceView']['sell']['price']))
			$price = (float)$decoded['priceView']['sell']['price'];
		return $price;
	}

	/**
	 * Fetch product's price using search term
	 *
	 * @param string	$term the search term to get data with
	 * @return null		if no product is found
	 * @return float	the product price
	 */
	public function fetchProductPriceFromSearchTerm($term)
	{
		$price = $this->fetchProductPriceFromSku($term);
		if (!is_null($price))
			return $price;
		$products = $this->fetchProductsFromSearchTerm($term);
		$price = null;
		if (isset($products[0]['code'])) {
			if ($term == $products[0]['manufacturerModelNumber'] && $term == $products[0]['code']) {
				$product_code = $products[0]['code'];
				$price = $this->fetchProductPriceFromSku($product_code);
			}
		}
		return $price;
	}

	/**
	 * Fetch product from search term
	 *
	 * @param string	$term the term to search with
	 * @return null		if no products are found
	 * @return float	the products
	 */
	public function fetchProductsFromSearchTerm($term)
	{
		$method = 'GET';
		$term = rawurlencode($term);
		$url = "https://www.grainger.com/api/v1/search?searchQuery=$term";
		$data = null;
		$headers = [
			'ClientId: ANDROID',
			'User-Agent: grainger_android/8.2 Mobile',
			'Host: www.grainger.com',
			'Connection: Keep-Alive',
			'Accept-Encoding: gzip',
		];
		$options = [
			CURLOPT_TIMEOUT => 8,
		];
		$this->sendRequestAndStoreResponse($method, $url, $data, $headers, $options);
		$decoded = $this->getResponseDecoded();
		$products = [];
		if (isset($decoded['searchResultData']['products']))
			$products = $decoded['searchResultData']['products'];
		return $products;
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
