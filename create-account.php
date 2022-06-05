<?php
/*
 * This file is a part of the Jelastic API Client PHP.
 * (c) sysastro <sysastro@gmail.com>
 * Fill free for this code to copyright, license and modification.
 * Created base on JElastic API Document : http://apidoc.devapps.jelastic.com/4.7-private/
 * Version : 1.0
 */

/* require jelastic class file */
require "jelastic.php";

/* call Jelastic class */
$jelastic = new Jelastic();
$email = 'emailaccount@gmail.com';
$password = 'passwordaccount';

$paramsSessionAdmin = array(
    'appid' => $jelastic->JcaAppId,
    'login' => $jelastic->apiUsername,
    'password' => $jelastic->apiPassword
);
$sessionAdmin = $jelastic->login($paramsSessionAdmin);
if(isset($sessionAdmin['session']))
{
    $paramsRegAccount = array(
        'appid' => $jelastic->JcaAppId,
        'session' => $sessionAdmin['session'],
        'email' => $email,
        'password' => $password,
        'checkemail' => false,
        'skipsendemail' => true,
        'autoactivate' => false
    );
    $createAccount = $jelastic->createAccount($paramsRegAccount);
    if(isset($createAccount['object']['activationKey']))
    {
        $paramsEnableAccount = array(
            'appid' => $jelastic->signUpAppId,
            'session' => $sessionAdmin['session'],
            'script' => 'activate',
            'activationKey' => $createAccount['object']['activationKey'],
            'password' => $password,
            'group' => $jelastic->group,
            'key' => $createAccount['object']['activationKey']
        );
        $enableAccount = $jelastic->enableAccount($paramsEnableAccount);
        if($enableAccount) {
            $paramsGetUid = array(
                'appid' => $jelastic->JcaAppId,
                'session' => $sessionAdmin['session'],
                'login' => $email
            );
            $getUid = $jelastic->getUid($paramsGetUid);
            if(isset($getUid['uid'])) {
                echo "Create account success.<br>Email : ".$email."<br>Password : ".$password."<br>Uid : ".$getUid['uid'];
            } else {
                echo "Get UID account failed.";
            }
        } else {
            echo "Enable account failed.";
        }
    }
    else
    {
        echo "Create account failed.";
    }
}
else
{
    echo "Login as admin failed.";
}

?>