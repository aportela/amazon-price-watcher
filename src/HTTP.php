<?php

    namespace AmazonPriceWatcher;

    class HTTP {

        public static function GET(string $url, array $params = [], string $userAgent = "") {
            if (function_exists('curl_version')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $referer = parse_url($url,  PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST);
                curl_setopt ($ch, CURLOPT_REFERER, $referer);
                curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt ($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_ENCODING , 'gzip,deflate,br');
                if (empty($userAgent)) {
                    curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
                } else {
                    curl_setopt ($ch, CURLOPT_USERAGENT, $userAgent);
                }
                $content = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpcode == 200) {
                    return($content);
                } else {
                    throw new \Exception("HTTP response code: " . $httpcode);
                }
            } else {
                return(file_get_contents($url));
            }
        }
    }
?>