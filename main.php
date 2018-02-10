<?php

session_start();

require_once './consts.php';
require_once './vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

//セッションに入れてた配列
$accessToken = $_SESSION['accessToken'];

//OAuthトークンとシークレットも使ってTwitterOAuth をインスタンス化
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $accessToken['oauth_token'], $accessToken['oauth_token_secret']);

// いいね履歴を入手
$favList = $connection->get("favorites/list", ["count" => "200"]);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>imgFavtter</title>
	<link rel="stylesheet" href="./css/style.css">
	<script src="./js/jquery-3.3.1.min.js"></script>
	<script src="./js/masonry.pkgd.min.js"></script>
	<script src="./js/imagesloaded.pkgd.min.js"></script>
</head>
<body>
	<header>
		<span id="title">imgFavtter</span>
		<span id="topLink"><a href="./index.html"><button>トップに戻る</button></a></span>
	</header>

	<div class="grid">
		<?php
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
				$imgUrl = $media->media_url;
		?>
				<div class="item">
					<img class="item_content" src="<?= $imgUrl ?>">
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
				columnWidth: 250
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