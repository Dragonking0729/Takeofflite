<?php

namespace App\Services;

class LowesApi {

	private $storeId = null;
	private $zipCode = null;
	
	public function getStoreId(){
		return $this->storeId;
	}
	public function getZipCode(){
		return $this->zipCode;
	}

	public function setStoreId($storeId){
		$this->storeId = $storeId;
	}
	public function setZipCode($zipCode){
		$this->zipCode = $zipCode;
	}
	public function setLocationFromZipCode($zipCode){
		$this->setZipCode($zipCode);
		$stores = $this->searchStoresByZipcode($zipCode);
		$storeId = $stores[0]['storeNumber'];
		$this->setStoreId($storeId);
	}

	public function fetchProductPrice($itemId=null){
		$url = 'https://lwssvcs.lowes.com/CatalogServices/product/productid/'.$itemId.'/v1_0';
		if(!is_null($this->storeId))
			$url = 'https://lwssvcs.lowes.com/CatalogServices/product/productid/'.$itemId.'/v1_0?storeNumber='.$this->storeId.'&priceFlag=rangeBalance&employee=false';
		$headers = [
			'Connection: Keep-Alive',
			'Authorization: Basic QW5kcm9pZFVzZXI6ZHdpYXA0aHE=',
			'Accept: application/json',
			'searchEngine: fusion',
			'User-Agent: okhttp/3.10.0',
		];
		$contents = $this->send('GET', $url, null, $headers, [
			CURLOPT_TIMEOUT => 10,
		]);
		$json = json_decode($contents, true);
		$price = null;
		if(isset($json['productList'][0]['networkPrice']))
			$price = (float)$json['productList'][0]['networkPrice'];
		if(isset($json['productList'][0]['pricing']['price']['selling']))
			$price = (float)$json['productList'][0]['pricing']['price']['selling'];
		return $price;
	}

	public function searchStoresByZipcode($zipcode=null){
		$url = 'http://lwssvcs.lowes.com/wcs/resources/store/10151/storelocation/v1_0?maxResults=1&query='.$zipcode;
		$headers = [
			'Connection: Keep-Alive',
			'Authorization: Basic QW5kcm9pZFVzZXI6ZHdpYXA0aHE=',
			'Accept: application/json',
			'searchEngine: fusion',
			'User-Agent: okhttp/3.10.0',
		];
		$contents = $this->send('GET', $url, null, $headers, [
			CURLOPT_TIMEOUT => 10,
		]);
		$json = json_decode($contents, true);
		$data = $json['storeLocation'];
		return $data;
	}

	public function send($method, $url, $data=array(), $headers=array(), $options=null){
		$default_options = array(
			CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
			CURLOPT_REFERER=>'http://google.com',
			CURLOPT_VERBOSE => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => 0,
			CURLOPT_POST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_CONNECTTIMEOUT => 100,
			CURLOPT_TIMEOUT => 100,
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
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $response;
	}

}
