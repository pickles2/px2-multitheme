px2-multitheme
==============

*px2-multitheme* は、複数のテーマを同時に管理する機能を [Pickles2](http://pickles2.pxt.jp/) に追加します。


## 導入方法 - Setup

Pickles 2 をセットアップします。

`composer.json` に、`"pickles2/px2-multitheme": "dev-master"` を追加します。

```
{
    "require": {
        "pickles2/px2-multitheme": "dev-master"
    }
}
```

保存したら、 `composer update` を実行して、パッケージをインストールしてください。

次に、`px-files/config.php` に設定を記述します。 デフォルトのテーマを削除して、`px2-multitheme` に変更します。

```
	$conf->funcs->processor->html = [
		// テーマ
		// 'theme'=>'pickles2\themes\pickles\theme::exec' , //←削除
		'theme'=>'tomk79\pickles2\multitheme\theme::exec' ,

	];

```

## オプション - Options

### パラメータ名 - param_theme_switch

テーマ切り替えスイッチとして使用するGETパラメータ名を設定します。デフォルトは `THEME` です。

### クッキー名 -  cookie_theme_switch

切り替えたテーマ名を記憶するクッキー名を設定します。デフォルトは `THEME` です。

### テーマコレクションディレクトリ - path_theme_collection

テーマ格納ディレクトリのパスを設定します。
相対パスの起点は、`.px_execute.php` が置かれているパスです。

初期値は `./px-files/themes/` です。

px2-multitheme はこのディレクトリの他にも、vendor ディレクトリにロードされたパッケージの一覧を検索し、theme が実装されたパッケージを選択候補に加えます。

### bowl名(コンテンツエリア名)を格納する属性名 - attr_bowl_name_by

Pickles2DesktopTool のGUI編集機能に対応する設定です。Pickles2DesktopTool は、ここに設定した属性の値からbowl名を取得し、GUI編集画面の構成するように振る舞います。
デフォルトは `data-contents-area` です。

### デフォルトのテーマID - default_theme_id

デフォルトで適用するテーマのIDです。初期値は `default` です。

テーマコレクションディレクトリに定義されたテーマを指定する場合は `theme_id` などの様にディレクトリ名を、composerパッケージからテーマを指定する場合は `vendorname/packagename` のように、スラッシュで区切られたパッケージ名を設定します。


### オプションの実装例

```
	$conf->funcs->processor->html = [
		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id'=>'pickles2'
		]).')' ,

	];

```


### テーマパッケージの開発

`/theme/default.html` に、デフォルトのレイアウトをセットしてください。 `/theme/` 以下の構成は、テーマコレクションと同じです。


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>


## 開発者向け情報 - for Developer

### テスト - Test

```
$ php ./vendor/phpunit/phpunit/phpunit
```
