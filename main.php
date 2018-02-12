<?php

session_start();

require_once './consts.php';
require_once './vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

// SESSIONがなければトップに飛ばす
if (!isset($_SESSION['accessToken'])) {
	header('Location: ./index.html');
	exit;
}

//セッションに入れてた配列
$accessToken = $_SESSION['accessToken'];

//OAuthトークンとシークレットも使ってTwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $accessToken['oauth_token'], $accessToken['oauth_token_secret']);
try {
	// いいね履歴を入手
	$favList = $connection->get("favorites/list", ["count" => "200"]);
} catch (\RuntimeException $e) {
	// いいね取得に失敗したらメッセージを表示させる
	$error = "いいねの取得に失敗しました。\nしばらくしてからやり直してください";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>imgFavtter</title>
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/lightbox.min.css">
	<link rel="stylesheet" href="./css/main.css">
	<script src="./js/jquery-3.3.1.min.js"></script>
	<script src="./js/lightbox.min.js"></script>
	<script src="./js/masonry.pkgd.min.js"></script>
	<script src="./js/imagesloaded.pkgd.min.js"></script>
</head>
<body>
	<header>
		<span id="title">imgFavtter</span>
		<span class="topLink"><a href="https://twitter.com/intent/tweet?text=いいねしてきた画像を一覧表示!%20imgFavtter%0a&url=https://imgFavtter.mnao305.com" onClick="window.open(encodeURI(decodeURI(this.href)), 'tweetwindow', 'width=650, height=470, personalbar=0, toolbar=0, scrollbars=1, sizable=1'); return false;" rel="nofollow"><button>ツイート!</button></a></span>
		<span class="topLink"><a href="./index.html"><button>トップに戻る</button></a></span>
	</header>

	<div class="grid">
		<?php
		if (isset($error)) {
			echo $error;
			exit;
		}
		// いいね履歴を一個ずつ抽出
		foreach ($favList as $fav) {
			// 画像以外はスキップする
			if (!isset($fav->extended_entities)) {
				continue;
			}
			$favImgs = $fav->extended_entities->media;
			// 複数画像のためのループ
			foreach ($favImgs as $key => $media) {
				// 動画だったら動画プレイヤーを貼る
				if ($media->type === 'video') {
					// 動画プレイヤー予定地
					continue;
				}
				$imgUrl = $media->media_url_https;
		?>
				<div class="item">
					<a href="<?= $imgUrl ?>"  rel="lightbox" data-lightbox="<?= $fav->id_str ?>"><img class="item_content" src="<?= $imgUrl ?>"></a>
					<a href="https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>" target="_blank"><p>Twitterで元ツイートを見る→</p></a>
				</div>
		<?php
			}
		}
		?>
	</div>
	<script>
		var $container = $('.grid');

		$container.imagesLoaded(function(){
			$container.masonry({
				itemSelector: '.item',
				columnWidth: 300
			});
		});
	</script>
	<footer>
		当サイトはベータ版です。
		何か問題がありましたら<a href="https://twitter.com/mnao_305">こちらまで</a>。
		<div id="copyright">© 2018 imgFavtter.</div>
	</footer>
</body>
</html>