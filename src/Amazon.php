<?php

    namespace AmazonPriceWatcher;

    class Amazon {

        public $originalURL;
        public $asin;
        public $productName;
        public $productStock;
        public $productPrice;
        public $imageURL;
        public $chartURL;

        public function __construct (string $url = "") {
            $missingExtensions = array_diff(["dom", "libxml"], get_loaded_extensions());
            if (count($missingExtensions) == 0) {
                $this->originalURL = $url;
                $this->asin = $this->getASIN();
                $this->chartURL = sprintf("https://charts.camelcamelcamel.com/es/%s/amazon.png?force=1&zero=0&w=855&h=513&desired=false&legend=1&ilt=1&tp=all&fo=0&lang=es_ES", $this->asin);
            } else {
                throw new \Exception("missing required php extension/s: ", implode(", ", $missingExtensions));
            }
        }

        public function getASIN() {
            preg_match('/(?:dp|o|gp|-|dp\/product|gp\/product)\/(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(?:X|[0-9]))/', $this->originalURL, $asin_arr);
            if (count($asin_arr) > 1) {
                return($asin_arr[1]);
            } else {
                throw new \InvalidArgumentException("Invalid url: " . $this->originalURL);
            }
        }

        public function scrap() {
            $html = \AmazonPriceWatcher\HTTP::GET($this->originalURL, [], "");
            if (! empty($html)) {
                @($domd = new \DOMDocument())->loadHTML($html);
                $xp=new \DOMXPath($domd);
                //$product["camelCamelCamelImageURL"] = "https://charts.camelcamelcamel.com/es/" . $product["ASIN"] . "/amazon.png?force=1&zero=0&w=855&h=513&desired=false&legend=1&ilt=1&tp=all&fo=0&lang=es_ES";
                $this->productName = trim($domd->getElementById("productTitle")->textContent);
                $this->productStock = trim($domd->getElementById("availability")->textContent);
                $prodInfo = $xp->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' a-price ')]");
                foreach($prodInfo as $info){
                    $this->productPrice = trim($info->getElementsByTagName("span")->item(1)->textContent);
                    if (! empty($this->productPrice)) {
                        break;
                    }
                }
                $this->imageURL = trim($domd->getElementById("landingImage")->getAttribute('src'));
            } else {
                // TODO
            }
        }
    }
?>