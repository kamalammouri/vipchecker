<?php

function randPass($length, $strength=8) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength >= 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength >= 2) {
        $vowels .= "AEUY";
    }
    if ($strength >= 4) {
        $consonants .= '0123456789';
    }
    if ($strength >= 8) {
        $consonants .= '-=+.';
    }

    $password = '';
    $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
    return $password;   
}

function generateRandom($length = 10) {

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $specials = '=+-_.';

    $charactersLength = strlen($characters);

    $randomString = '';

    // Removed one from length to maintain desired length

    // for special character addition

    for ($i = 0; $i < $length - 1; $i++) {

        $randomString .= $characters[rand(0, $charactersLength - 1)];

    }

    // Add the special character:

    $randomString .= $specials[rand(0, strlen($specials) - 1)];

    // Shuffle the returned string so the special is not always at the end

    return str_shuffle($randomString);

}

function genCodes($number){
    $code_no = array();
    while(count($code_no)<=$number){
        $codeX = randPass(14);
        // $codeX = generateRandom(14);

        if(preg_match('/[^A-Za-z0-9]/', $codeX)){
            if(getLineWithString($codeX) == -1){
                array_push($code_no,'S'.$codeX);
                $file = fopen('codes.txt', 'a+');
                fwrite($file, 'S'.$codeX.PHP_EOL);
                fclose($file);
            }
        }
    }
}

function getLineWithString($str) {
    $lines = file('codes.txt');  
    if($lines !== ''){
        foreach ($lines as $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }
    }
    return -1;
}

function api($code){
    // $url = 'http://45.91.82.31/';
    $url = 'http://194.124.216.122/';
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

     //try params for fast curl
    // curl_setopt($curl_connection, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    // curl_setopt($curl_connection, CURLOPT_TCP_FASTOPEN, true); 

    //set data to be posted
    curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

    //perform our request
    $response = curl_exec($curl_connection);
    //close the connection
    curl_close($curl_connection);
    $start = stripos($response, "document.getElementById('prompt').innerHTML");
    $end = stripos($response, "</body>");
    $body = substr($response,$start+46,$end-$start);
    
    if(!stripos($body,'Error,Card_NO does not exist') && !stripos($body,'Error,Invalid Card_NO')){
        $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $code.':'.$body);
        fclose($myfile);
    }

    return ['code'=>$code,'status'=>$body];
}


function execute($number=0){
    $file = file('codes.txt');
    // foreach($file as $line){
    //     print_r(api($line));
    //     echo '<br>';
    // }
    for($i=0;$i<$number;$i++) {
        print_r(api($file[$i]));
        echo '<br>';
    }

    $fileContents = file('newfile.txt');
    if($number != 0) $setNum = $number;
    if($number == 0) $setNum = count($file);
    $newNumber = $fileContents[0] + $setNum;
    // Remove first line
    array_shift($fileContents);
    // Add the new line to the beginning
    array_unshift($fileContents, $newNumber);
    // Write the file back
    $newContent = implode("\n", $fileContents);
    $fp = fopen('newfile.txt', "w+");   // w+ means create new or replace the old file-content
    fputs($fp, $newContent);
    fclose($fp);
}

execute();