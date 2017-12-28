pickles2/px2-multitheme
==============

<table>
  <thead>
    <tr>
      <th></th>
      <th>Linux</th>
      <th>Windows</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>master</th>
      <td align="center">
        <a href="https://travis-ci.org/pickles2/px2-multitheme"><img src="https://secure.travis-ci.org/pickles2/px2-multitheme.svg?branch=master"></a>
      </td>
      <td align="center">
        <a href="https://ci.appveyor.com/project/tomk79/px2-multitheme"><img src="https://ci.appveyor.com/api/projects/status/04h4o82cuavxmkwk/branch/master?svg=true"></a>
      </td>
    </tr>
    <tr>
      <th>develop</th>
      <td align="center">
        <a href="https://travis-ci.org/pickles2/px2-multitheme"><img src="https://secure.travis-ci.org/pickles2/px2-multitheme.svg?branch=develop"></a>
      </td>
      <td align="center">
        <a href="https://ci.appveyor.com/project/tomk79/px2-multitheme"><img src="https://ci.appveyor.com/api/projects/status/04h4o82cuavxmkwk/branch/develop?svg=true"></a>
      </td>
    </tr>
  </tbody>
</table>


*px2-multitheme* は、複数のテーマを同時に管理する機能を [Pickles2](http://pickles2.pxt.jp/) に追加します。


## 導入方法 - Setup

Pickles 2 をセットアップします。

`composer.json` と同階層に移動し、次のコマンドを実行します。

```php
composer require pickles2/px2-multitheme
```

次に、`px-files/config.php` に設定を記述します。

```php
$conf->funcs->processor->html = [
	// テーマ
	'theme'=>'tomk79\pickles2\multitheme\theme::exec' ,
];
```

## コンフィグオプション - Config Options

### テーマ切り替えのパラメータ名 - param_theme_switch

テーマ切り替えスイッチとして使用するGETパラメータ名を設定します。デフォルトは `THEME` です。

### テーマ名を記憶するクッキー名 -  cookie_theme_switch

切り替えたテーマ名を記憶するクッキー名を設定します。デフォルトは `THEME` です。

### レイアウト切り替えのパラメータ名 - param_layout_switch

レイアウト切り替えスイッチとして使用するGETパラメータ名を設定します。デフォルトは `LAYOUT` です。

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

### オプション - options

テーマが個別に定義するオプション値を設定します。
設定できるオプションはテーマによって異なります。詳しくは各テーマのドキュメントを参照してください。


### コンフィグオプションの実装例 - Config Sample

```
$conf->funcs->processor->html = [
	// テーマ
	'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
		'param_theme_switch'=>'THEME',
		'cookie_theme_switch'=>'THEME',
		'param_layout_switch'=>'LAYOUT',
		'path_theme_collection'=>'./px-files/themes/',
		'attr_bowl_name_by'=>'data-contents-area',
		'default_theme_id'=>'pickles2',
		'options'=>array(
			'pickles2'=>array( // テーマ pickles2 に対するオプション
				'sample_param'=>'hoge' // テーマ側からは、 `$theme->get_option('sample_param')` で受け取ることができます。
			)
		)
	]).')'
];

```


## テーマの実装

各テーマは、テーマコレクションディレクトリの直下にディレクトリとして設置します。 例えば、 `sample` という名前のテーマは、 ディレクトリ `<theme_collection_dir>/sample/` の中に実装されます。

テーマディレクトリの直下には、 `(レイアウト名).html` という命名規則で、複数のレイアウトを定義できます。

規定のレイアウトは、 `default.html` (=デフォルト), `popup.html`, `top.html`, `plain.html`, `naked.html` があり、 サイトマップCSV の `layout` 列に名前を指定して選択します。この使い方については、Pickles 2 のドキュメントを参照してください。

### テーマレイアウトで使える主なAPI

#### Pickles 2 の API

Pickles 2 が提供するAPIのうち、テーマの実装でよく利用するAPIには、次のものがあります。 詳しい使い方は、Pickles 2 の [APIドキュメント](http://pickles2.pxt.jp/phpdoc/) を参照してください。

- `$px->href()`
- `$px->mk_link()`
- `$px->conf()`
- `$px->bowl()->pull()`
- `$px->site()->get_current_page_info()`
- `$px->site()->get_children()`
- `$px->site()->is_page_in_breadcrumb()`
- `$px->site()->path_plugin_files()`
- `$px->site()->get_category_top()`

#### px2-multitheme が提供する API

Pickles 2 にある機能の他に、 px2-multitheme の独自のAPIも提供されます。

- `$theme->get_option()`
- `$theme->get_layout()`
- `$theme->get_attr_bowl_name_by()`
- `$theme->files()`
- `$theme->mk_global_menu()`
- `$theme->mk_shoulder_menu()`
- `$theme->mk_sub_menu()`
- `$theme->mk_megafooter_menu()`
- `$theme->mk_breadcrumb()`


### theme_files

テーマフォルダの直下に ディレクトリ `theme_files/` を設置すると、ここにテーマ固有のリソースファイル(画像やCSSなど)を置くことができます。

`theme_files` に置かれたファイルは、 Pickles 2 の公開キャッシュディレクトリ(デフォルトでは `/caches/*`) の中に複製が作られ、ブラウザから参照できるようになります。

テーマからこれらのファイルを呼び出す場合、次のように実装してください。

```php
<p>'theme_files/hoge/fuga.png' を呼び出す</p>
<img src="<?= htmlspecialchars( $theme->files('/hoge/fuga.png') ); ?>" alt="" />
```



## テーマパッケージの公開

テーマは、独立したパッケージとして Packagist などで公開できます。

`/theme/default.html` に、デフォルトのレイアウトをセットしてください。 `/theme/` 以下の構成は、テーマコレクションと同じです。


## 更新履歴 - Change log

### pickles2/px2-multitheme 2.0.5 (20??年??月??日)

- 新しい設定項目 `$param_layout_switch` を追加。 GETパラメータで一時的にレイアウトを切り替えて表示できるようになった。
- `./theme_files/〜〜` という記述でテーマリソースにアクセスできるようになった。 `$theme->files()` が暗黙的に呼ばれ、置き換えられる。

### pickles2/px2-multitheme 2.0.4 (2017年7月28日)

- 異なるテーマで同時アクセスしたときに、リソースのパスが混在する問題を修正。

### pickles2/px2-multitheme 2.0.3 (2017年7月11日)

- テーマテンプレートの実装を助ける目的ではないメソッドを `$theme` から分離して隠蔽した。
- `$theme->files()` を追加。
- `px2package` を参照してテーマを検索するようになった。

### pickles2/px2-multitheme 2.0.2 (2016年7月27日)

- パンくず上にカレントページがある場合に、リンクではなくなるようになった。
- 誤ったテーマIDを選択した場合に、仮のテーマに包んで画面を返すようになった。
- その他、不具合の修正とパフォーマンス向上。

### pickles2/px2-multitheme 2.0.1 (2016年6月30日)

- ローカルナビゲーションの生成ルールを変更： パンくず上にないページの子要素は開かないようにした。

### pickles2/px2-multitheme 2.0.0 (2016年3月4日)

- 初版リリース。


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>


## 開発者向け情報 - for Developer

### テスト - Test

```
$ php ./vendor/phpunit/phpunit/phpunit
```
