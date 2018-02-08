<?php
session_start();

require_once './consts.php';
require_once './vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

// login.phpでセットしたセッション
$requestToken = [];
$requestToken['oauthToken'] = $_SESSION['oauthToken'];
$requestToken['oauthTokenSecret'] = $_SESSION['oauthTokenSecret'];

// Twitterから返されたトークンとセッション上のものが一致するか？
if (isset($_REQUEST['oauthToken']) && $requestToken['oauthToken'] !== $_REQUEST['oauthToken']) {
	die('Error!!');
}

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $requestToken['oauthToken'], $requestToken['oauthTokenSecret']);

$_SESSION['accessToken'] = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

// セッションIDをリジェネレート
session_regenerate_id();

// メインページにリダイレクト
header('location: ./main.php');