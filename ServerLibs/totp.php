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
if (isset($_POST['delete']) && "./totp/".is_file($_POST['delete'] . ".png")) {
    unlink("./totp/".$_POST['delete'].".png");
}

function generateQrSecretKey(){

$secretKey = generateSecretKey();
//$secretKey = "5UFGIAXBJI5BPUSP";
echo("KEY: $secretKey\n");

$otp = generateTOTP($secretKey);

echo "Current OTP: $otp\n";
$qrOtp = "otpauth://totp/loginApi:tester?secret=$secretKey&issuer=tester";
if (!is_dir("./totp")) {
    mkdir("./totp");
}
$randomName = rand();
QRcode::png($qrOtp,"./totp/$randomName.png");echo("<img src='./totp/$randomName.png'>");
}

?>