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
                curl_setopt($ch, CURLOPT_COOKIEJAR, './cookie.txt');
                curl_setopt($ch, CURLOPT_COOKIEFILE, './cookie.txt');
                if (empty($userAgent)) {
                    curl_setopt ($ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0');
                } else {
                    curl_setopt ($ch, CURLOPT_USERAGENT, $userAgent);
                }
                $content = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpcode == 200){
                    return($content);
                } else {
                    throw new \Exception($httpcode);
                }
            } else {
                return(file_get_contents($url));
            }
        }
    }
?>