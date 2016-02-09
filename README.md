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

### テーマコレクションディレクトリ - path_theme_collection

テーマ格納ディレクトリのパスを設定します。
相対パスの起点は、`.px_execute.php` が置かれているパスです。

初期値は `./px-files/themes/` です。

### デフォルトのテーマID - default_theme_id

デフォルトで適用するテーマのIDです。初期値は `default` です。

### オプションの実装例

```
	$conf->funcs->processor->html = [
		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'path_theme_collection'=>'./px-files/themes/',
			'default_theme_id'=>'pickles2'
		]).')' ,

	];

```


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
