<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class BlsApi
{

    public function __construct()
    {
        if (!class_exists('Symfony\Component\DomCrawler\Crawler'))
            throw new \Exception('Required dependency \'\Symfony\Component\DomCrawler\Crawler\' not found');
    }

    /**
     * Fetch wage estimate by hourly 90% from url
     *
     * @param string	$url the url to get data from
     * @return null		if no value found
     * @return float	the hourly 90% value
     * @throws Exception if multiple elements are found or value is not a number
     */
    public function fetchWageEstimateHourly90(String $url)
    {
        $contents = $this->sendRequest('GET', $url)['contents'];
        if (!substr_count($contents, 'Occupational Employment and Wages'))
            throw new \Exception('Unexpected page contents');
        $dom = new \Symfony\Component\DomCrawler\Crawler($contents);
        $field = $dom->filterXpath('//th[normalize-space() = "90%"]/../../tr/td[normalize-space() = "Hourly Wage"]/../td[last()]');
        if (count($field) < 1)
            return null;
        if (count($field) > 1)
            throw new \Exception('Too many matching elements');
        $text = $field->text();
        if (substr_count($text, '$') !== 1)
            throw new \Exception('Unexpected field type');
        $text = trim(trim(trim($text), '$'));
        if (!is_numeric($text))
            throw new \Exception('Unexpected field type');
        $value = (float)$text;
        return $value;
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
        $response['decoded'] = $response['contents'];
        if ($response['decoded'][0] === '{')
            $response['decoded'] = json_decode($response['contents'], true);
        $response['info'] = curl_getinfo($ch);
        curl_close($ch);
        return $response;
    }
}
