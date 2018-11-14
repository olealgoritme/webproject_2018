<?php

$KEY = "AIzaSyAPGWz50dpdXlR3D-HQKMApVZXa-ojqlr0";
$SEARCH = "nearbysearch";
$RADIUS = "1000";
$BASE_URL = "https://maps.googleapis.com/maps/api/place";
$RESULT_TYPE = "json";
$SEARCH_TYPE = array(
                "resturant",
                "cafe",
                "night_club",
                "bar",
                "museum",
                "gym",);
$RANK_BY = "prominence";

function searchLatLong($place, $lat, $long, $search_type) {

    global $KEY, $SEARCH, $RADIUS, $BASE_URL, $RESULT_TYPE, $RANK_BY;
    
    $url = "$BASE_URL/nearbysearch/$RESULT_TYPE?location=$lat,$long&radius=$RADIUS&type=$search_type&key=$KEY&region=no&language=no&rankby=$RANK_BY";
    //echo "URL: " .  $url;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);
    $hasResults = (sizeof($response_a->results) > 0); 

    if ($hasResults) {
        $place_text = "-----------------------------------------------------------------------------------------------------------\n"; 
        $place_text .= "Place: " . mb_strtoupper(str_replace("\"", "", urldecode($place)), 'UTF-8') . " (" . $search_type . ")\n";
        $place_text .= "-----------------------------------------------------------------------------------------------------------\n"; 
        writeFile($place_text);
    }

    foreach ($response_a->results as &$value) {
        
        $continue = true;

        $name = $value->name;
        $vicinity = $value->vicinity;
        $rating = $value->rating;
        $types = $value->types;

        if($name == "Oslo") $continue = false;

        if($continue) {
            $result = "Name: " . $name;
            $result .= " | Address: " . $vicinity;
            if (!empty($rating)) $result .= " | Rating: " . $rating;
            $result .= " | Types: [ " . implode(", ", $types) . " ]";
            $result .= "\n";
            echo $result;
            writeFile($result);
        }
    }

    if($hasResults) {
        $place_text = "-----------------------------------------------------------------------------------------------------------\n\n\n";
        writeFile($place_text);
    }
}

function getLatLongFromName($place) {
    
    global $SEARCH_TYPE, $KEY;
    
    $url = "https://maps.google.com/maps/api/geocode/json?address=$place&sensor=false&key=$KEY";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $response_a = json_decode($response);

    if(!empty($response_a->results[0])) {

        $lat = $response_a->results[0]->geometry->location->lat;
        $long = $response_a->results[0]->geometry->location->lng;

        if(!empty($lat) && !empty($long)) {
            foreach ($SEARCH_TYPE as &$search_type) {
                searchLatLong($place, $lat, $long, $search_type);
            }
        }
    }
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
        $place = urlencode($place);
        getLatLongFromName($place);
    }

    $file = null;
}

$dataFile = "fulldata.txt";
parseFile($dataFile);


function writeFile($text) {
    $file = "saved.txt";
    file_put_contents($file, $text, FILE_APPEND);
}

?>