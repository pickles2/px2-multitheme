

*px2-multitheme* は、プロジェクト内に複数のテーマを共存し、パラメーターから切り替える方法を提供します。

<!-- autoindex -->


## px2-multitheme が提供するテーマセットのパスを設定する

```
	$conf->funcs->processor->html = [
		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./vendor/tomk79/px2-multitheme/px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id'=>'default'
		]).')' ,

	];

```

## namespace

`tomk79\pickles2\multitheme` を宣言します。
