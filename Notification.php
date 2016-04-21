<?php
/*
Yuya Oguchi
4/8/2016

Steam market data collection:
It takes cvs file which is populated by SteamPriceAPI.php
it checks for couple trend of that data such as
sudden drop in price,
sudden rise in price,
weekly update of all iteams
longterm rise,
longterm milestone(price rise of all items combined),
and Profitable items at the moment comared to original bought price
*/

//#!/usr/bin/env php
error_reporting(E_ALL);

// set the default timezone to use. Available since PHP 5.1
date_default_timezone_set('America/Los_Angeles');
//echo "-----------------------\n";
//echo date("D Y-m-d H:i:s") . "\n\n";
$file = fopen("/Users/YuyaOguchi/Desktop/CSGOMarketData/DataOverTime.csv","r");
$row = 0;
$rows = array();
$names = array();

//get all lines of data
//row 1 = names
//col 1...14 price data
//col 15...end amount sold on market
while (($data=fgetcsv($file)) !== FALSE) {
    $row++;
    if ($row==1) $names = $data;
    else $rows[] = $data;
}
$lastRow = count($rows)-1;
fclose($file);

//Define variables\
$tax = .85;
$droprate = .85;
$dropcomparedate = 1;
$riserate = 1.15;
$risecomparedate = 1;
//
$amount = array(0,1,2,1,3,3,2,1,5,7,2,1,2,2);
$amountSpentOrg = 53.74;



function pushbullet($title, $msg) {
    exec("pushbullet '$title' '$msg'");
}

// check for quick price drop
function quickdrop($droprate,$dropcomparedate){ //droprate in decimal eg. .9
    global $names, $rows, $lastRow, $tax, $amount;
    for ($col=1; $col<14; $col++) {
        $new = $rows[$lastRow][$col];
        $old = $rows[$lastRow-$dropcomparedate][$col];
        if ($new < $droprate*$old) {
            $msg="\"$names[$col]\"x".$amount[$col]." dropped by ".round(($new/$old),2).". ($".round($new,2).")(Org x".round(($new/$rows[0][$col]),2).")!";
            pushbullet("CSGO $names[$col] Dropped!", $msg);
        }
    }
}

// check for quick price rise
function quickrise($riserate,$risecomparedate){ //riserate in decimal eg. 1.2
    global $names, $rows, $lastRow, $tax,$amount;
    for ($col=1; $col<14; $col++) {
        $new = $rows[$lastRow][$col];
        $old = $rows[$lastRow-$risecomparedate][$col];
        if ($new > $riserate*$old) {
            $msg="\"$names[$col]\"x".$amount[$col]." rose by".round(($new/$old),2)." ($".round($new,2).")(Org x".round($new/$rows[0][$col],2).")!";
            pushbullet("CSGO $names[$col] Rose!", $msg);
        }
    }
}

//check for milestone in each item price.
function longtermrise(){ //riserate in decimal eg. 1.2
    global $names, $rows, $lastRow, $tax;
    for ($col=1; $col<14; $col++) {
        $new = $rows[$lastRow][$col];
        $old = $rows[0][$col];
        if (($new*$tax > 10*$old) && ($rows[$lastRow-1][$col]*$tax < 10*$old) ) {
            $msg="\"$names[$col]\" rose since the beginning (Org x".round(($new*$tax/$old),2)." at a price of ".round($new*$tax,2).")!";
            pushbullet("CSGO Skin Notification! :)", $msg);
        }else if (($new*$tax > 5*$old) && ($rows[$lastRow-1][$col]*$tax < 5*$old) ) {
            $msg="\"$names[$col]\" rose since the beginning (Org x".round(($new*$tax/$old),2)." at a price of ".round($new*$tax,2).")!";
            pushbullet("CSGO Skin Notification! :)", $msg);
        }else if (($new*$tax > 2*$old) && ($rows[$lastRow-1][$col]*$tax < 2*$old) ) {
            $msg="\"$names[$col]\" rose since the beginning (Org x".round(($new*$tax/$old),2)." at a price of ".round($new*$tax,2).")!";
            pushbullet("CSGO Skin Notification! :)", $msg);
        }
    }
}

//check for milestone of all purchases
function longtermmilestone(){
    global $names, $rows, $lastRow, $amount, $amountSpentOrg, $tax;
    $totalCurrentPrice = $totalOriginalPrice = 0;
    for ($col=1; $col<14; $col++) {
        $newprice = $rows[$lastRow][$col]*$amount[$col];
        $oldPrice = $rows[0][$col]*$amount[$col];
        $totalCurrentPrice += $newprice;
        $totalOriginalPrice += $oldPrice;
    }
    if ($totalCurrentPrice*$tax > 10*$amountSpentOrg) {
        $msg= "Total investment rose since the beginning (Org x".round(($totalCurrentPrice*$tax/$totalOriginalPrice),2)." at a price of ".round($totalCurrentPrice*$tax,2).")!";
        pushbullet("CSGO Skin Milestone hit! :)", $msg);
    } else if ($totalCurrentPrice*$tax > 5*$amountSpentOrg) {
        $msg="Total investment rose since the beginning (Org x".round(($totalCurrentPrice*$tax/$totalOriginalPrice),2)." at a price of ".round($totalCurrentPrice*$tax,2).")!";
        pushbullet("CSGO Skin Milestone hit! :)", $msg);
    } else if ($totalCurrentPrice*$tax > 2*$amountSpentOrg) {
        $msg="Total investment rose since the beginning (Org x".round(($totalCurrentPrice*$tax/$totalOriginalPrice),2)." at a price of ".round($totalCurrentPrice*$tax,2).")!";
        pushbullet("CSGO Skin Milestone hit! :)", $msg);
    }
}

function sellableStickers(){
    global $names, $rows, $lastRow, $tax;
    $msg = "Following stickers are surplus: \n";
    for ($col=1; $col<14; $col++) {
        $new = $rows[$lastRow][$col];
        $old = $rows[0][$col];
        if ($new*$tax > $old) {
            $msg = $msg. "\"$names[$col]\" profit $".round($new*$tax-$old,2)." (Org x".round(($new*$tax/$old),2).")\n";
        }
    }
    pushbullet("CSGO Sellable skins with tax! :)", $msg);
}

//weekly update on data
function weeklyupdate(){
    global $names, $rows, $lastRow, $amount, $amountSpentOrg, $tax;
    $totalCurrentPrice = 0;
    $highestDelta=0;
    $highestDeltaColIndex = 0;
    $lowestDelta=100;
    $lowestDeltaColIndex = 0;

    for ($col=1; $col<14; $col++){
        $first = $rows[0][$col];
        $newprice = $rows[$lastRow][$col]*$amount[$col];
        //echo "New:".$newprice."old:".($rows[0][$col]*$amount[$col]). "\n";
        // echo "Latest price: ".$newprice;
        $totalCurrentPrice = $totalCurrentPrice + $newprice;
        if($highestDelta < $newprice/($first*$amount[$col])){
            $highestDelta = $newprice/($first*$amount[$col]);
            $highestDeltaColIndex = $col;
        }
        if($lowestDelta > $newprice/($first*$amount[$col])){
            $lowestDelta = $newprice/($first*$amount[$col]);
            $lowestDeltaColIndex = $col;
        }
    }
    $msg="Total Grossing: \n"
        ."Taxed $" . round($totalCurrentPrice*$tax,2). " (x" . round(($totalCurrentPrice*$tax/$amountSpentOrg),3) . ")\n"
        ."Raw $" . round($totalCurrentPrice,2). " (x" . round(($totalCurrentPrice/$amountSpentOrg),3) . ")\n\n"
        ."Highest rise ". $names[$highestDeltaColIndex]. ": $".$rows[$lastRow][$highestDeltaColIndex]. " (Orgx". round($highestDelta,2).")\n"
        ."Lowest rise:".$names[$lowestDeltaColIndex]. ": $".$rows[$lastRow][$lowestDeltaColIndex]." (Orgx". round($lowestDelta,3).")";
    pushbullet("Weekly Update", $msg);



}

//call functions here
quickdrop($droprate,$dropcomparedate);
quickrise($riserate,$risecomparedate);
longtermrise();
longtermmilestone();
sellableStickers();
//if($row%7 == 0){
    weeklyupdate();
//}

echo "DONE\n"
?>
