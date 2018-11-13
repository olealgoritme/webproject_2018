<?php

$KEY = "AIzaSyAPGWz50dpdXlR3D-HQKMApVZXa-ojqlr0";
$SEARCH = "nearbysearch";
$RADIUS = "1000";
$BASE_URL = "https://maps.googleapis.com/maps/api/place";
$RESULT_TYPE = "json";
$SEARCH_TYPE = "resturant|zoo|bar|cafe|museum|night_club|gym";
$RANK_BY = "prominence";

function searchLatLong($BASE_URL, $RESULT_TYPE, $LAT, $LONG, $RADIUS, $SEARCH_TYPE, $KEY, $RANK_BY) {
    $url = "$BASE_URL/nearbysearch/$RESULT_TYPE?location=$LAT,$LONG&radius=$RADIUS&type=$SEARCH_TYPE&key=$KEY&region=NO&language=NO&rankby=$RANK_BY";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);
    var_dump($response_a);
}

function getLatLongFromName($place) {
    global $KEY;
    urlencode($place);
    $url = "https://maps.google.com/maps/api/geocode/json?address=$place&sensor=false&key=$KEY";
    //echo $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);
    //var_dump($response);

    $LAT = $response_a->results[0]->geometry->location->lat;
    $LONG = $response_a->results[0]->geometry->location->lng;

    global $KEY, $SEARCH, $RADIUS, $BASE_URL, $RESULT_TYPE, $SEARCH_TYPE, $RANK_BY;
    searchLatLong($BASE_URL, $RESULT_TYPE, $LAT, $LONG, $RADIUS, $SEARCH_TYPE, $KEY, $RANK_BY);
}

function parseFile($filename) {
    try {
        $file = new SplFileObject($filename);
    } catch (LogicException $exception) {
        die('SplFileObject : '.$exception->getMessage());
    }
    while ($file->valid()) {
        $place = $file->fgets();
        $place = rtrim($place);
        getLatLongFromName($place);
    }

    $file = null;
}

$file = "data.txt";
parseFile($file);

?>