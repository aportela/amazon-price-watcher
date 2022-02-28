<?php
    require ("../vendor/autoload.php");

    $products = \AmazonPriceWatcher\AmazonPriceWatcherItem::search();
    if (count($products) > 0) {
        echo "Refreshing products..." . PHP_EOL;
        foreach($products as $product) {
            echo "\t[I] Product: " . $product->name;
            $item = new \AmazonPriceWatcher\AmazonPriceWatcherItem();
            $item->id = $product->id;
            $item->get();
            $item->scrap();
            if (! empty($item->name)) {
                $item->update();
                echo " OK!" . PHP_EOL;
            } else {
                echo " ERROR!" . PHP_EOL;
            }
            sleep(1);
        }
    } else {
        echo "No products found" . PHP_EOL;
    }
?>