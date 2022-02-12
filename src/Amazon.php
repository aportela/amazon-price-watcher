<?php

    namespace AmazonPriceWatcher;

    class Amazon {

        public $originalURL;

        public function __construct (string $url = "") {
            $this->originalURL = $url;
        }

        public function getASIN() {
            preg_match('/(?:dp|o|gp|-|dp\/product|gp\/product)\/(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(?:X|[0-9]))/', $originalURL, $asin_arr);
            if (count($asin_arr) > 1) {
                return($asin_arr[1]);
            } else {
                throw new \InvalidArgumentException("Invalid url: " . $originalURL);
            }
        }
    }
?>