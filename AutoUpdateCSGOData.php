#!/usr/bin/env php
<?php
/*
Yuya Oguchi
4/8/2016
Steam market data collection
This automate the process of collecting the data and write it to csv file
*/
error_reporting(E_ALL);
include 'SteamPriceAPI.php';
// set the default timezone to use. Available since PHP 5.1
date_default_timezone_set('America/Los_Angeles');
echo "-----------------------\n";
echo date("D Y-m-d H:i:s") . "\n\n";
GetSaleData();
$file = fopen("/Users/YuyaOguchi/Desktop/CSGOMarketData/DataOverTime.csv","a");
$list = array_merge(array(date("md")),$pricelist,$amountlist);
//var_dump($list);
fputcsv($file,$list);
fclose($file);
echo "DONE\n";

include 'Notification.php';
?>
