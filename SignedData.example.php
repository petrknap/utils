<?php

require_once("./Hash.class.php");
require_once("./SignedData.class.php");

use PetrKnap\Utils\Security\Hash;
use PetrKnap\Utils\Security\SignedData;

header("Content-Type: text/plain; charset=utf-8");

SignedData::$ALLOW_UNTRUSTED_DATA = true; // don't throw exception

#region Get signed data
$sd = new SignedData(0xAF4D0C71, "BWR9Ny17Uf");
$sd->UnsignedData = array(
    "UserID" => 10,
    "SHA256_NewPassword" => Hash::B64SHA256("bwv7ldn3ow"),
    "RequestTime" => 1372444217
);
print_r($sd->SignedData);
unset($sd);
#endregion

print("\n\n");

#region Use signed data
$sd = new SignedData(0xAF4D0C71, "BWR9Ny17Uf");
/** @noinspection SpellCheckingInspection */
$sd->SignedData = "" .
    "cQpj3sgi8Iqy6KwFfzo6/GTrzkIYTozOntzOjY6IlVzZXJJRCI7aToxMDtzOjE4" .
    "OiJTSEEyNTZfTmV3UGFzc3dvcmQiO3M6NDM6InJjMjhsb0x4cnZCYzhRVXRtcGd" .
    "GWVB4WTU5bWNFd3RLZTRMb2hrbWR3UFEiO3M6MTE6IlJlcXVlc3RUaW1lIjtpOj" .
    "EzNzI0NDQyMTc7fQ==";
printf("This data %s trusted.", ($sd->IsTrusted ? "are" : "aren't"));
print("\n\n");
print_r($sd->UnsignedData);
unset($sd);
#endregion

/* Output:

cQpj3sgi8Iqy6KwFfzo6/GTrzkIYTozOntzOjY6IlVzZXJJRCI7aToxMDtzOj...

This data are trusted.

Array
(
    [UserID] => 10
    [SHA256_NewPassword] => rc28loLxrvBc8QUtmpgFYPxY59mcEwtKe4LohkmdwPQ
    [RequestTime] => 1372444217
)

 */