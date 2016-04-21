<?php
/*
Yuya Oguchi
4/8/2016
Steam market data collection
Data retrieval asks for steam market item json
and uses current lowest selling price and volume available.
It then populates cvs file.
*/
$pricelist = array();
//var_dump($pricelist);
$amountlist = array();
//var_dump($amountlist);

function DataRetrieval($Name, $URL){
    global $pricelist, $amountlist;
    $json = file_get_contents($URL);
    $Obj = (json_decode($json));
    echo $Name . ": ";
    if ($Obj != NULL){
        echo "Success\n";
        echo "Price: " . $Obj->lowest_price . ", Amount sold: " . $Obj->volume . "\n" . "\n";
        array_push($pricelist,str_replace("$","",$Obj->lowest_price));
        array_push($amountlist,$Obj->volume);
    }else{
        echo "Failed!\n";
        exit();
    }
    sleep(2);
};

function DataRetrieval2($Name, $id) {
    global $pricelist, $amountlist;
    $json = file_get_contents("http://steamcommunity.com/market/itemordershistogram?country=US&language=english&currency=1&item_nameid=".$id."&two_factor=0");
    $Obj = (json_decode($json));
    echo $Name . ": ";
    if ($Obj != NULL){
        echo "Success\n";
        preg_match_all("/<span.*?>(.*?)<\/span>/", $Obj->sell_order_summary, $matches);
        $values = $matches[1];
        $amount = $values[0];
        $price = str_replace("$","",$values[1]);
        array_push($amountlist,$amount);
        array_push($pricelist,$price);
    } else {
        echo "Failed!\n";
        exit();
    }
    sleep(2);
}

function GetSaleData() {
    //"Astralis (Holo)"
    DataRetrieval2("Astralis (Holo)", 144424224);

    //"Cloud9 (Holo)"
    DataRetrieval2("Cloud9 (Holo)",144424315);

    //"Faze (Holo)"
    DataRetrieval2("Faze (Holo)",144424250);

    //"Fnatic (Holo)"
    DataRetrieval2("Fnatic (Holo)",144424255);

    //"Splyce (Holo)"
    DataRetrieval2("Splyce (Holo)",144424293);

    //"Team Liq. (Holo)"
    DataRetrieval2("Team Liq. (Holo)",144424251);

    //"Virtus Pro (Holo)"
    DataRetrieval2("Virtus Pro (Holo)",144424225);

    //"Astralis"
    DataRetrieval2("Astralis",144424348);

    //"Faze"
    DataRetrieval2("Faze",144424188);

    //"Splyce"
    DataRetrieval2("Splyce",144424214);

    //"Envyus"
    DataRetrieval2("Envyus",144424216);

    //"Challenger MLG"
    DataRetrieval2("Challenger MLG",144424230);

    //"Legend MLG"
    DataRetrieval2("Legend MLG",144424220);
}

?>
