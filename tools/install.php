<?php
    require ("../vendor/autoload.php");

    define('DATABASE_PATH', __DIR__ . '/../data/amazon-price-watcher.sqlite3');

    if (file_exists(DATABASE_PATH)) {
        unlink(DATABASE_PATH);
    }

    $dbh = new \PDO
    (
        sprintf("sqlite:%s", DATABASE_PATH),
        null,
        null,
        array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        )
    );

    $queries = array(
        'CREATE TABLE "GROUPS" ( "ID" TEXT NOT NULL, "NAME" TEXT NOT NULL, PRIMARY KEY("ID") )',
        'CREATE TABLE "PRODUCT" ( "ID" TEXT NOT NULL, "URL" TEXT NOT NULL UNIQUE, "ASIN" TEXT NOT NULL, "NAME" TEXT NOT NULL, "IMAGE_URL" TEXT, "CURRENT_PRICE" REAL DEFAULT 0, "PREVIOUS_PRICE" REAL DEFAULT 0, "MIN_PRICE" REAL DEFAULT 0, "MAX_PRICE" REAL DEFAULT 0, "CURRENCY" TEXT NOT NULL, "CURRENT_STOCK" TEXT, "LAST_SCRAP" TEXT NOT NULL, PRIMARY KEY("ID") )',
        'CREATE TABLE "PRODUCT_GROUP" ( "PRODUCT_ID" TEXT NOT NULL, "GROUP_ID" TEXT NOT NULL, PRIMARY KEY("PRODUCT_ID","GROUP_ID") )'
    );

    foreach($queries as $query) {
        $stmt = $dbh->prepare($query);
        $stmt->execute();
    }

?>