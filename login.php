<?php
session_start();

require_once './consts.php';
require_once './vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

$requestToken = $connection->oauth('oauth/request_token', array('oauth_callback' => CALLBACK_URL));

// セッションにトークンを入れる
$_SESSION['oauthToken'] = $requestToken['oauth_token'];
$_SESSION['oauthTokenSecret'] = $requestToken['oauth_token_secret'];

$url = $connection->url('oauth/authenticate', array('oauth_token' => $requestToken['oauth_token']));

// Twitterの認証画面に飛ぶ
header('location:' . $url);