<?php

    namespace AmazonPriceWatcher;

    class AmazonPriceWatcherItem {

        public $id;
        public $url;
        public $asin;
        public $name;
        public $currentStock;
        public $currentPrice;
        public $previousPrice;
        public $minPrice;
        public $maxPrice;
        public $currency;
        public $imageURL;
        public $chartURL;
        public $affiliate;
        public $lastUpdate;

        // https://stackoverflow.com/a/15875555
        private static function uuid() {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        private static function getASIN(string $url) {
            preg_match('/(?:dp|o|gp|-|dp\/product|gp\/product)\/(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(?:X|[0-9]))/', $url, $asin_arr);
            if (count($asin_arr) > 1) {
                return($asin_arr[1]);
            } else {
                throw new \InvalidArgumentException("Invalid url: " . $this->originalURL);
            }
        }

        private static function getChartURL(string $asin) {
            return(sprintf("https://charts.camelcamelcamel.com/es/%s/amazon.png?force=1&zero=0&w=855&h=513&desired=false&legend=1&ilt=1&tp=all&fo=0&lang=es_ES", $asin));
        }

        public function setFromURL(string $url) {
            $missingExtensions = array_diff(["dom", "libxml"], get_loaded_extensions());
            if (count($missingExtensions) == 0) {
                $this->url = $url;
                $parsedURL = parse_url($this->url);
                // TODO: get cleaned URL without AFFILIATES
                //$this->cleanedURL = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $parsedURL['path'];
                if (isset($parsedURL['query'])) {
                    parse_str($parsedURL['query'], $output);
                    $this->affiliate = $output['tag'] ?? null;
                }
                $this->asin = \AmazonPriceWatcher\AmazonPriceWatcherItem::getASIN($this->url);
                if (! empty($this->asin)) {
                    $this->chartURL = \AmazonPriceWatcher\AmazonPriceWatcherItem::getChartURL($this->asin);
                }
            } else {
                throw new \Exception("missing required php extension/s: ", implode(", ", $missingExtensions));
            }
        }

        public function scrap() {
            $html = \AmazonPriceWatcher\HTTP::GET($this->url, [], "");
            if (! empty($html)) {
                @($domd = new \DOMDocument())->loadHTML($html);
                $xp=new \DOMXPath($domd);
                //$product["camelCamelCamelImageURL"] = "https://charts.camelcamelcamel.com/es/" . $product["ASIN"] . "/amazon.png?force=1&zero=0&w=855&h=513&desired=false&legend=1&ilt=1&tp=all&fo=0&lang=es_ES";
                $this->name = trim($domd->getElementById("productTitle") ? $domd->getElementById("productTitle")->textContent: null);
                $this->currentStock = trim($domd->getElementById("availability") ? $domd->getElementById("availability")->textContent: null);
                $nodes = $xp->query('//input[@id="attach-base-product-price"]');
                if (count($nodes) == 1) {
                    $this->currentPrice = floatval($nodes->item(0)->getAttribute('value'));
                }

                $nodes = $xp->query('//input[@id="attach-base-product-currency-symbol"]');
                if (count($nodes) == 1) {
                    $this->currency = $nodes->item(0)->getAttribute('value');
                }

                if (empty($this->currentPrice) || empty($this->currency)) {
                    $nodes = $xp->query("//*[contains(concat(' ', normalize-space(@class), ' '), 'twister-plus-buying-options-price-data')]");
                    if (count($nodes) > 0) {
                        if (! empty($nodes->item(0)->nodeValue)) {
                            $json = json_decode($nodes->item(0)->nodeValue);
                            if ($json != NULL) {
                                $this->currentPrice = floatval($json[0]->priceAmount);
                                $this->currency = $json[0]->currencySymbol;
                            }
                        }
                    }
                }

                /*
                if (empty($this->productPrice)) {
                    $prodInfo = $xp->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' a-price-whole ')]");
                    foreach($prodInfo as $info){
                        $this->productPrice = floatval(trim($info->textContent));
                        //$this->productPrice = trim($info->getElementsByTagName("span")->item(0)->textContent);
                        if (! empty($this->productPrice)) {
                            break;
                        }
                    }
                }

                if (empty($this->productCurrency)) {
                    $prodInfo = $xp->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' a-price-symbol ')]");
                    foreach($prodInfo as $info){
                        $this->productCurrency = trim($info->getElementsByTagName("span")->item(0)->textContent);
                        if (! empty($this->productCurrency)) {
                            break;
                        }
                    }
                }
                */
                $this->imageURL = trim($domd->getElementById("landingImage") ? $domd->getElementById("landingImage")->getAttribute('src'): null);
                if (empty($this->name)) {
                    throw new \Exception("No scrap");
                }
            } else {
                // TODO
                die("NO HTML");
            }
        }

        public function add() {
            $results = array();
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                INSERT INTO PRODUCT
                    (ID, URL, ASIN, NAME, IMAGE_URL, CURRENT_PRICE, PREVIOUS_PRICE, MIN_PRICE, MAX_PRICE, CURRENCY, CURRENT_STOCK, LAST_SCRAP)
                VALUES
                    (:ID, :URL, :ASIN, :NAME, :IMAGE_URL, :CURRENT_PRICE, :PREVIOUS_PRICE, :MIN_PRICE, :MAX_PRICE, :CURRENCY, :CURRENT_STOCK, CURRENT_TIMESTAMP)
            ";
            if (empty($this->id)) {
                $this->id = \AmazonPriceWatcher\AmazonPriceWatcherItem::uuid();
            }
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(":ID", $this->id, \PDO::PARAM_STR);
            $stmt->bindValue(":URL", $this->url, \PDO::PARAM_STR);
            $stmt->bindValue(":ASIN", $this->asin, \PDO::PARAM_STR);
            $stmt->bindValue(":NAME", $this->name, \PDO::PARAM_STR);
            $stmt->bindValue(":IMAGE_URL", $this->imageURL, \PDO::PARAM_STR);
            $stmt->bindValue(":CURRENT_PRICE", $this->currentPrice, \PDO::PARAM_STR);
            $stmt->bindValue(":PREVIOUS_PRICE", $this->currentPrice, \PDO::PARAM_STR);
            $stmt->bindValue(":MIN_PRICE", $this->currentPrice, \PDO::PARAM_STR);
            $stmt->bindValue(":MAX_PRICE", $this->currentPrice, \PDO::PARAM_STR);
            $stmt->bindValue(":CURRENCY", $this->currency, \PDO::PARAM_STR);
            $stmt->bindValue(":CURRENT_STOCK", $this->currentStock, \PDO::PARAM_STR);
            $stmt->execute();
        }

        public function update() {
            $results = array();
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                UPDATE PRODUCT
                SET
                    PREVIOUS_PRICE = CURRENT_PRICE,
                    CURRENT_PRICE = :CURRENT_PRICE,
                    MIN_PRICE = CASE WHEN MIN_PRICE > :CURRENT_PRICE THEN CURRENT_PRICE ELSE MIN_PRICE END,
                    MAX_PRICE = CASE WHEN MAX_PRICE < :CURRENT_PRICE THEN CURRENT_PRICE ELSE MAX_PRICE END,
                    CURRENT_STOCK = :CURRENT_STOCK,
                    LAST_SCRAP = CURRENT_TIMESTAMP
                WHERE ID = :ID
            ";
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(":ID", $this->id, \PDO::PARAM_STR);
            $stmt->bindValue(":CURRENT_PRICE", $this->currentPrice, \PDO::PARAM_STR);
            $stmt->bindValue(":CURRENT_STOCK", $this->currentStock, \PDO::PARAM_STR);
            $stmt->execute();
        }

        public static function delete($id) {
            $results = array();
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                DELETE FROM PRODUCT
                WHERE ID = :ID
            ";
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(":ID", $id, \PDO::PARAM_STR);
            $stmt->execute();
        }

        public function get() {
            if (! empty($this->id) || ! empty($this->url)) {
                $dbh = new \PDO
                (
                    sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                    null,
                    null,
                    array(
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    )
                );
                if (! empty($this->id)) {
                    $query = "
                        SELECT
                            ID, URL, ASIN, NAME, IMAGE_URL, CURRENT_PRICE, PREVIOUS_PRICE, MIN_PRICE, MAX_PRICE, CURRENCY, CURRENT_STOCK, LAST_SCRAP
                        FROM PRODUCT
                        WHERE ID = :ID
                    ";
                    $stmt = $dbh->prepare($query);
                    $stmt->bindValue(":ID", $this->id, \PDO::PARAM_STR);
                } else {
                    $query = "
                        SELECT
                            ID, URL, ASIN, NAME, IMAGE_URL, CURRENT_PRICE, PREVIOUS_PRICE, MIN_PRICE, MAX_PRICE, CURRENCY, CURRENT_STOCK, LAST_SCRAP
                        FROM PRODUCT
                        WHERE URL = :URL
                    ";
                    $stmt = $dbh->prepare($query);
                    $stmt->bindValue(":URL", $this->url, \PDO::PARAM_STR);
                }
                $stmt->execute();
                if ($row = $stmt->fetchObject()) {
                    $this->id = $row->ID;
                    $this->url = $row->URL;
                    $this->asin = $row->ASIN;
                    $this->name = $row->NAME;
                    $this->imageURL = $row->IMAGE_URL;
                    if (! empty($this->asin)) {
                        $this->chartURL = \AmazonPriceWatcher\AmazonPriceWatcherItem::getChartURL($this->asin);
                    }
                    $this->currentPrice = floatval($row->CURRENT_PRICE);
                    $this->previousPrice = floatval($row->PREVIOUS_PRICE);
                    $this->minPrice = floatval($row->MIN_PRICE);
                    $this->maxPrice = floatval($row->MAX_PRICE);
                    $this->maxPrice = floatval($row->MAX_PRICE);
                    $this->currency = $row->CURRENCY;
                    $this->currentStock = $row->CURRENT_STOCK;
                    $this->lastUpdate = $row->LAST_SCRAP;
                } else {
                    // TODO
                }
            } else {
                // TODO
            }
        }

        public static function search() {
            $results = array();
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                SELECT
                    ID, URL, ASIN, NAME, IMAGE_URL, CURRENT_PRICE, PREVIOUS_PRICE, MIN_PRICE, MAX_PRICE, CURRENCY, CURRENT_STOCK, LAST_SCRAP
                FROM PRODUCT
                ORDER BY LAST_SCRAP DESC
            ";
            $stmt = $dbh->prepare($query);
            $stmt->execute();
            while ($row = $stmt->fetchObject()) {
                $result = new \stdClass();
                $result->id = $row->ID;
                $result->url = $row->URL;
                $result->asin = $row->ASIN;
                $result->name = $row->NAME;
                $result->imageURL = $row->IMAGE_URL;
                if (! empty($result->asin)) {
                    $result->chartURL = \AmazonPriceWatcher\AmazonPriceWatcherItem::getChartURL($result->asin);
                }
                $result->currentPrice = floatval($row->CURRENT_PRICE);
                $result->previousPrice = floatval($row->PREVIOUS_PRICE);
                $result->minPrice = floatval($row->MIN_PRICE);
                $result->maxPrice = floatval($row->MAX_PRICE);
                $result->maxPrice = floatval($row->MAX_PRICE);
                $result->currency = $row->CURRENCY;
                $result->currentStock = $row->CURRENT_STOCK;
                $result->lastUpdate = $row->LAST_SCRAP;
                $results[] = $result;
            }
            return($results);
        }

        public static function searchGroups() {
            $results = array();
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                SELECT
                    ID, NAME
                FROM GROUPS
                ORDER BY NAME
            ";
            $stmt = $dbh->prepare($query);
            $stmt->execute();
            while ($row = $stmt->fetchObject()) {
                $result = new \stdClass();
                $result->id = $row->ID;
                $result->name = $row->NAME;
                $results[] = $result;
            }
            return($results);
        }

        public static function addGroup($name) {
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                INSERT INTO GROUPS (ID, NAME) VALUES (:ID, :NAME)
            ";
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(":ID", \AmazonPriceWatcher\AmazonPriceWatcherItem::uuid(), \PDO::PARAM_STR);
            $stmt->bindValue(":NAME", $name, \PDO::PARAM_STR);
            $stmt->execute();
        }

        public static function deleteGroup(string $id) {
            $dbh = new \PDO
            (
                sprintf("sqlite:%s", __DIR__ . "/../data/amazon-price-watcher.sqlite3"),
                null,
                null,
                array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
            $query = "
                DELETE FROM GROUPS WHERE ID = :ID
            ";
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(":ID", $id, \PDO::PARAM_STR);
            $stmt->execute();
        }

    }
?>