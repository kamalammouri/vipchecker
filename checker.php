<?php

function random_strings($length_of_string){
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz=+.';
    return substr(str_shuffle($str_result),0, $length_of_string);
}
function api($code){

    $url = 'http://tpoentrance.cc/';
    $post_data['card_no'] = $code;
    $post_data['submit'] = 'query';
    $post_data['action'] = 'yes';
    $post_data['check_valid'] = 'yes';

    //traverse array and prepare data for posting (key1=value1)
    foreach ( $post_data as $key => $value) {
        $post_items[] = $key . '=' . $value;
    }
    //create the final string to be posted using implode()
    $post_string = implode ('&', $post_items);

    //create cURL connection
    $curl_connection =  curl_init($url);
    //set options
    curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 10000);
    curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, 1 );

    //set data to be posted
    curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

    //perform our request
    $response = curl_exec($curl_connection);
    //close the connection
    curl_close($curl_connection);
    $start = stripos($response, "document.getElementById('prompt').innerHTML");
    $end = stripos($response, "</body>");
    $body = substr($response,$start+46,$end-$start);
    
    if(!stripos($body,'Error,Card_NO does not exist')){
        $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $code.':'.$body);
        fclose($myfile);
    }

    return ['code'=>$code,'status'=>$body];
}


$code_no = array();
for ($i=0;$i<10;$i++){
    array_push($code_no,'I'.random_strings(14));
}

foreach ($code_no as $code){
    print_r(api($code));
    echo '<br>';
}

?>