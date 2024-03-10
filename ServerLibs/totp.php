<?php
include("phpqrcode.php");

function generateTOTP($secret, $timeSlice = null, $digits = 6)
{
    if ($timeSlice === null) {
        $timeSlice = floor(time() / 30);
    }

    $secretKey = base32Decode($secret);

    $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);
    $hash = hash_hmac('sha1', $binaryTime, $secretKey, true);

    $offset = ord($hash[19]) & 0xF;
    $otp = (
        ((ord($hash[$offset + 0]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    ) % pow(10, $digits);

    return str_pad($otp, $digits, '0', STR_PAD_LEFT);
}

function base32Decode($base32)
{
    $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32CharsFlipped = array_flip(str_split($base32Chars));

    $output = '';
    $v = 0;
    $vBits = 0;

    for ($i = 0, $j = strlen($base32); $i < $j; $i++) {
        $v <<= 5;
        $v += $base32CharsFlipped[$base32[$i]];
        $vBits += 5;

        if ($vBits >= 8) {
            $vBits -= 8;
            $output .= chr(($v & (0xFF << $vBits)) >> $vBits);
        }
    }

    return $output;
}

function generateSecretKey($length = 16)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';

    for ($i = 0; $i < $length; $i++) {
        $secret .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $secret;
}
//if (isset($_POST['delete']) && "./totp/".is_file($_POST['delete'] . ".png")) {
//    unlink("./totp/".$_POST['delete'].".png");
//}

function removeDir($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    // Open the directory
    $handle = opendir($dir);

    // Loop through the directory
    while (false !== ($item = readdir($handle))) {
        if ($item != "." && $item != "..") {
            // If the item is a directory, recursively call removeDir()
            if (is_dir($dir . '/' . $item)) {
                removeDir($dir . '/' . $item);
            } else {
                // If the item is a file, delete it
                unlink($dir . '/' . $item);
            }
        }
    }

    // Close the directory handle
    closedir($handle);

    // Remove the directory itself
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

function generateQrSecretKey(){

$secretKey = generateSecretKey();
$qrOtp = "otpauth://totp/loginApi:tester?secret=$secretKey&issuer=tester";

$rand = rand();
$tempFile = tempnam(sys_get_temp_dir(), "QrSecret_$rand");
QRcode::png($qrOtp, $tempFile);
return("QrSecret_$rand");
}

function getSecret($imagePath){
    if (file_exists($imagePath)) {
        header('Content-Type: image/png');

        readfile($imagePath);
        exit;
    }
}

?>