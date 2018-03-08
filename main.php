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
	if (!isset($_POST['getFav'])) {
		$favList = $connection->get("favorites/list", ["count" => "100"]);
	} else {
		$favList = $connection->get("favorites/list", ["count" => "100", "max_id" => $_POST['getFav']]);
	}
} catch (\RuntimeException $e) {
	// いいね取得に失敗したらメッセージを表示させる
	$error = "いいねの取得に失敗しました。\nしばらくしてからやり直してください";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-57438486-3"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'UA-57438486-3');
	</script>
	<meta charset="utf-8">
	<title>imgFavtter</title>
	<link rel="stylesheet" href="./css/style.css">
	<link rel="stylesheet" href="./css/lightbox.min.css">
	<link rel="stylesheet" href="./css/main.css">
	<link rel="stylesheet" href="./css/jquery.fancybox.min.css">
	<script src="./js/jquery-3.3.1.min.js"></script>
	<script src="./js/lightbox.min.js"></script>
	<script src="./js/masonry.pkgd.min.js"></script>
	<script src="./js/imagesloaded.pkgd.min.js"></script>
	<script src="./js/loadingoverlay.min.js"></script>
	<script src="./js/jquery.fancybox.min.js"></script>
</head>
<body>
	<header>
		<span id="title">imgFavtter</span>
		<span class="topLink"><a href="https://twitter.com/intent/tweet?text=いいねしてきた画像を一覧表示!%20imgFavtter%0a&url=https://imgFavtter.mnao305.com" onClick="window.open(encodeURI(decodeURI(this.href)), 'tweetwindow', 'width=650, height=470, personalbar=0, toolbar=0, scrollbars=1, sizable=1'); return false;" rel="nofollow"><button>ツイート!</button></a></span>
		<span class="topLink"><a href="./logout.php"><button>ログアウト</button></a></span>
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
				?>
					<div class="item">
						<a href="<?= $media->video_info->variants[1]->url ?>" data-fancybox="<?= h($fav->id_str) ?>" data-caption="<?= h($fav->text) ?> By <?= h($fav->user->name) ?><br><a href='https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>' target='_blank'>Twitterで元ツイートを見る→</a>">
						<img  class="item_content" src="<?= $media->media_url_https ?>">
						<img src="./images/play.png" class="playBtn">
						</a>
						<a href="https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>" target="_blank"><p>Twitterで元ツイートを見る→</p></a>
					</div>
				<?php
					continue;
				}
				$imgUrl = $media->media_url_https;
		?>
				<div class="item">
					<a href="<?= $imgUrl ?>" data-fancybox="<?= h($fav->id_str) ?>" data-caption="<?= h($fav->text) ?> By <?= h($fav->user->name) ?><br><a href='https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>' target='_blank'>Twitterで元ツイートを見る→</a>">
					<img class="item_content" src="<?= $imgUrl ?>">
					</a>
					<a href="https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>" target="_blank"><p>Twitterで元ツイートを見る→</p></a>
				</div>
		<?php
			}
		}
		?>
	</div>
	<form action="main.php" name="nextFav" method="post">
		<input type="hidden" name="getFav" value="">
		<div id="buttonWrap"><button class="button" onclick="getFavBtn();">もっと前のいいねを見る-></button></div>
	</form>
	<footer>
		当サイトはベータ版です。
		何か問題がありましたら<a href="https://twitter.com/mnao_305" target="_blank">Twitter</a>か<a href="https://github.com/mnao305/imgFavtter/issues" target="_blank">GitHub</a>まで。
		<div id="copyright">© 2018 imgFavtter.</div>
	</footer>
	<script>
		// 画像拡大表示
		$('[data-fancybox]').fancybox();
		// くるくる表示
		$.LoadingOverlay("show");

		var $container = $('.grid');
		// 画像がすべて読み込めたら。。。
		$container.imagesLoaded(function(){
			// 画像をタイル状に表示
			$container.masonry({
				itemSelector: '.item',
				columnWidth: 300
			});
			// 隠していたフッターを表示させる
			$("footer").css("display", "block");
			// くるくるを消す
			$.LoadingOverlay("hide");
		});

		function getFavBtn() {
			document.nextFav.getFav.value="<?= $fav->id_str ?>";
			document.nextFav.submit();
		}
	</script>
</body>
</html>
<?php

function favDisplay() {
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
			?>
				<div class="item">
					<a href="<?= $media->video_info->variants[1]->url ?>" data-fancybox="<?= h($fav->id_str) ?>" data-caption="<?= h($fav->text) ?> By <?= h($fav->user->name) ?><br><a href='https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>' target='_blank'>Twitterで元ツイートを見る→</a>">
					<img  class="item_content" src="<?= $media->media_url_https ?>">
					<img src="./images/play.png" class="playBtn">
					</a>
					<a href="https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>" target="_blank"><p>Twitterで元ツイートを見る→</p></a>
				</div>
			<?php
				continue;
			}
			$imgUrl = $media->media_url_https;
	?>
			<div class="item">
				<a href="<?= $imgUrl ?>" data-fancybox="<?= h($fav->id_str) ?>" data-caption="<?= h($fav->text) ?> By <?= h($fav->user->name) ?><br><a href='https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>' target='_blank'>Twitterで元ツイートを見る→</a>">
				<img class="item_content" src="<?= $imgUrl ?>">
				</a>
				<a href="https://twitter.com/<?= $fav->user->screen_name ?>/status/<?= $fav->id_str ?>" target="_blank"><p>Twitterで元ツイートを見る→</p></a>
			</div>
	<?php
		}
	}
}

function h($str) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}