<?php
error_reporting(0);
require 'config.php';
$kode = 0;

echo "
__        __       _                                  ___    ____  _   _
\ \      / /  ___ | |  ___   ___   _ __ ___    ___   / _ \  / ___|| | | |
 \ \ /\ / /  / _ \| | / __| / _ \ | '_ ` _ \  / _ \ | | | || |  _ | | | |
  \ V  V /  |  __/| || (__ | (_) || | | | | ||  __/ | |_| || |_| || |_| |
   \_/\_/    \___||_| \___| \___/ |_| |_| |_| \___|  \___/  \____| \___/
  First Comment Tool

";
echo "[?] What do you want to do?\n\n0. Login\n1. Run the tool\n2. Edit comment lists\n3. Edit target page\n";
echo "\n";
echo "[=] Answer : ";
$kode = trim(fgets(STDIN));
echo "\n";
if($kode == 1){
    if ($cookieData = explode('|', file_get_contents($cookieFile))) {
        $username = $cookieData[0]; // Useragent Instagram
        $cookies = $cookieData[1]; // Cookie Instagram
        $useragent = $cookieData[2]; // Useragent Instagram
        if(!$username){
            echo "[i] Oops you are not logged in yet, please re-run the script\n";
            $kode = 0;
        }
    }
}else if($kode == 2){
    echo "[i] Input your comment lists separated by a | if you want random comments\n";
    echo "[?] Comments : ";
    $text = trim(fgets(STDIN));
    if($text){
        saveCookie($komentar, $text);
        echo "\n";
        echo "[i] Comment lists have been updated, please re-run the script\n";
    }else{
        echo "\n";
        echo "[i] Comment lists it's empty, please re-run the script\n";
    }
}else if($kode == 3){
    echo "[i] Input separate targets with an | mark if you want multiple targets\n";
    echo "[?] Targets : ";
    $text = trim(fgets(STDIN));
    if($text){
        saveCookie($targetFile, $text);
        echo "\n";
        echo "[i] Target lists have been updated, please re-run the script\n";
    }else{
        echo "\n";
        echo "[i] Target lists it's empty, please re-run the script\n";
    }
}else if($kode == 4){
    echo "[i] Masukan menit timeline\n";
    echo "[?] Menit : ";
    $text = trim(fgets(STDIN));
    if($text){
        saveCookie($menit_ke, $text);
        echo "\n";
        echo "[i] Menit telah diperbarui\n";
    }else{
        echo "\n";
        echo "[i] Menit kosong\n";
    }
}else{

    echo "[?] Instagram username : ";
    $username = trim(fgets(STDIN));

    echo "[?] Instagram password : ";
    $password = trim(fgets(STDIN));

    $login = json_decode(instagram_logins($username, $password));
    if ($login->status != false) {
        $cookies = $login->cookies;
        $useragent = $login->useragent;
        echo "[i] Your account is already logged in the system, please re-run the script\n";
        saveCookie($cookieFile, "$username|$cookies|$useragent");
    }else{
        $kode = 2;
        if ($username) {
            if ($login->msg == 'Cekpoint') {
                echo "[i] Getting chekpoint\n";
                $kode = 3;
                echo "\n";
                echo "[+] Choose verification method\n\n0. Phone number\n1. Email\n";
                echo "\n";
                echo "[?] Input method : ";
                $metode = trim(fgets(STDIN));
                $data = 'choice='.$metode;
                $cekpoint = cekpoint($_SESSION['c_url'], $data, $_SESSION['c_token'], $_SESSION['c_cookie'], $_SESSION['c_ua']);
                if (strpos($cekpoint, 'status": "ok"') !== false) {
                    echo "[i] Verification code sent\n";
                }else{
                    echo "[i] Verification code sent\n";
                }
            }else{
                echo "[i] ".$login->msg."\n";
            }
        } else {
            echo "[i] ".$login->msg."\n";
        }
    }

    if($kode == 3){
        echo "\n";
        echo "[?] Input verification code : ";
        $otp = trim(fgets(STDIN));
        $data = 'security_code='.$otp;
        $cekpoint = cekpoint($_SESSION['c_url'], $data, $_SESSION['c_token'], $_SESSION['c_cookie'], $_SESSION['c_ua']);
        if (strpos($cekpoint, 'status": "ok"') !== false) {
            echo "[i] Verification code valid\n";
            preg_match_all('%ookie: (.*?);%', $cekpoint, $d);
            $cookiesx = '';
            for ($o = 0; $o < count($d[0]); $o++) {
                $cookiesx .= $d[1][$o] . ";";
            }
            preg_match_all('/ds\_user\_id\=(.*?)\;/', $cookiesx, $id);
            $a = request(1, $_SESSION['c_ua'], 'users/'.$id[1][0].'/info/', $cookiesx);
            $a = json_decode($a[1]);
            if($a->status == 'ok') {
                $userid = $id[1][0];
                $username = $a->user->username;
                $cookies = $cookiesx;
                $useragent = $_SESSION['c_ua'];
                echo "[i] Your account is already logged in the system, please re-run the script\n";
                saveCookie($cookieFile, "$username|$cookies|$useragent");
            }else{
                echo "[i] Verification failed please try again\n";
            }
        }else{
            echo "[i] Verification code invalid\n";
        }
    }
}

if($kode == 1){
    echo "\n";
    echo "[+] Welcome $username \n";
    echo "[?] What version do you want to use??\n\n1. Timeline feed\n2. Target page\n\n";
    echo "[=] Answer : ";
    $first_koment = trim(fgets(STDIN));
    echo "\n";

    echo "[+] Bot it's running....\n";sleep(1);
    echo "[+] Date ".date('Y-m-d H:i:s')."\n";sleep(2);

    while(true) {
        if($first_koment == 0 || $first_koment == 1){
            echo "\n[+] Currently opening the latest timeline (".date('Y-m-d H:i:s').")\n";
            $data['bloks_versioning_id'] = 'a4b4b8345a67599efe117ad96b8a9cb357bb51ac3ee00c3a48be37ce10f2bb4c';
            $send = json_decode(request(1, $useragent, 'feed/timeline/', $cookies, json_encode($data))[1], 1);
            //print_r($send); die();
            $timeline = $send['items'];
            $komens = file_get_contents($komentar);
            $logs = file_get_contents($saveFile);
            $komen = explode("|", $komens);
            $menit = file_get_contents($menit_ke);
            //print_r($komen); die();
            //$komen = array($komen)[0];
            $i=0;
            while($i <= count($timeline)){
                if ($code = $timeline[$i]['code']) {
                    $rand = rand(0, count($komen)-1);
                    $text = $komen[$rand];
                    $media_id = $timeline[$i]['id'];
                    $id = $timeline[$i]['user']['pk'];
                    $comment_count = $timeline[$i]['comment_count'];
                    $username = $timeline[$i]['user']['username'];

                    $hack_timestamp_post = strtotime('+ ' . $menit . ' minutes', $timeline[$i]['taken_at']);
                    $now_timestamp = time();
                    $date_post = date('Y-m-d H:i:s', $hack_timestamp_post);
                    $now_date_post = date('Y-m-d H:i:s', $now_timestamp);

                    $result = "$media_id|$code|$id|$username|$text|$comment_count";
                    if (strpos($logs, $media_id) !== false) {
                        $status = 'SUCCESS';
                        $msg = '[ALREADY COMMENTED]';
                    }else if($now_timestamp > $hack_timestamp_post){
                        $status = 'EXPIRED';
                        $msg = '[MORE THAN '.$menit.' MINUTE]';
                    }else{
                        if ($first_koment) {
                            $status = '1st';
                            if ($comment_count === 0) {
                                sleep($delay);
                                $comment = json_decode(request(1, $useragent, 'media/' . $media_id . '/comment/', $cookies, generateSignature(json_encode(['comment_text' => $text])))[1],1);
                            }else{
                                $status = 'FAILED, the post already have '.$comment_count.' comments';
                            }
                        }else{
                            $status = 'run';
                            sleep($delay);
                            $comment = json_decode(request(1, $useragent, 'media/' . $media_id . '/comment/', $cookies, generateSignature(json_encode(['comment_text' => $text])))[1],1);
                        }
                        if($comment['status'] == 'ok'){
                            saveData($saveFile, $result);
                            $msg = '[SUCCESS]';
                        }else if($comment['message']){
                            $msg = '['.$comment['message'].']';
                            saveData($saveFile, $result);
                        }else{
                            $msg = '[SKIP]';
                        }
                    }
                    echo "[-] $username -> $status : $text $msg\n";
                }
                $i++;
                sleep(0); ob_flush(); flush();
            }
        }
        if($first_koment == 2 || $first_koment == 3){
            $targets = file_get_contents($targetFile); $target = explode("|", $targets);
            $komens = file_get_contents($komentar); $komen = explode("|", $komens);
            $logs = file_get_contents($saveFile);
            foreach ($target as $user) {
                echo "\n[+] Currently opening the latest $user timeline feed (".date('Y-m-d H:i:s').")\n";
                $userId = json_decode(request(1, $useragent, 'users/'.$user.'/usernameinfo/', $cookies)[1], 1)['user']['pk'];
                $send = json_decode(request(1, $useragent, 'feed/user/'.$userId, $cookies)[1], 1);
                $timeline = $send['items'];

                if ($code = $timeline[0]['code']) {
                    $rand = rand(0, count($komen)-1);
                    $text = $komen[$rand];
                    $media_id = $timeline[0]['id'];
                    $id = $timeline[0]['user']['pk'];
                    $comment_count = $timeline[0]['comment_count'];
                    $username = $timeline[0]['user']['username'];
                    $result = "$media_id|$code|$id|$username|$text|$comment_count";
                    if (strpos($logs, $media_id) !== false) {
                        $status = 'ALREADY';
                        $msg = '[ALREADY COMMENTED]';
                    } else {
                        if ($first_koment == 2) {
                            $status = '1st';
                            if ($comment_count === 0) {
                                sleep($delay);
                                $comment = json_decode(request(1, $useragent, 'media/' . $media_id . '/comment/', $cookies, generateSignature(json_encode(['comment_text' => $text])))[1], 1);
                            }else{
                                $status = 'FAILED, the posts already have '.$comment_count.' comments';
                            }
                        } else {
                            $status = 'run';
                            sleep($delay);
                            $comment = json_decode(request(1, $useragent, 'media/' . $media_id . '/comment/', $cookies, generateSignature(json_encode(['comment_text' => $text])))[1], 1);
                        }
                        if ($comment['status'] == 'ok') {
                            saveData($saveFile, $result);
                            $msg = '[SUCESS]';
                        }else if($comment['message']){
                            $msg = '['.$comment['message'].']';
                            saveData($saveFile, $result);
                        }else{
                            $msg = '[SKIP]';
                        }
                    }
                    echo "[-] $username -> $status : $text $msg\n";
                }
                sleep(0);
                ob_flush();
                flush();
            }
        }
        sleep(1); ob_flush(); flush();
    }
}
