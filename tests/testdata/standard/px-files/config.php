<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'Px2-MultiTheme - test - standard'; // サイト名
	$conf->domain = null; // ドメイン
	$conf->path_controot = '/'; // コンテンツルートディレクトリ

	// paths
	$conf->path_top = '/'; // トップページのパス(デフォルト "/")
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->public_cache_dir = '/caches/'; // 公開キャッシュディレクトリ
	$conf->path_files = '{$dirname}/{$filename}_files/'; // リソースディレクトリ(各コンテンツに対して1:1で関連付けられる)のパス
	$conf->contents_manifesto = '/common/contents_manifesto.ignore.php'; // Contents Manifesto のパス


	// directory index
	// `directory_index` は、省略できるファイル名のリストを設定します。
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '775';
	$conf->dir_default_permission = '775';
	$conf->filesystem_encoding = 'UTF-8';
	$conf->output_encoding = 'UTF-8';
	$conf->output_eol_coding = 'lf';
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;
	$conf->allow_pxcommands = 1; // PX Commands のウェブインターフェイスからの実行を許可

	// commands
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';

	// processor
	$conf->paths_proc_type = array(
		// パスのパターン別に処理方法を設定
		//     - ignore = 対象外パス
		//     - direct = 加工せずそのまま出力する(デフォルト)
		//     - その他 = extension 名
		// パターンは先頭から検索され、はじめにマッチした設定を採用する。
		// ワイルドカードとして "*"(アスタリスク) を使用可。
		'/.htaccess' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/px-files/*' => 'ignore' ,
		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'direct' ,
		'*.jpg' => 'direct' ,
		'*.gif' => 'direct' ,
		'*.svg' => 'direct' ,
	);


	// -------- functions --------

	$conf->funcs = new stdClass;

	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		// px2-clover
		tomk79\pickles2\px2clover\register::clover(array(
			"protect_preview" => false, // プレビューに認証を要求するか？
		)),

		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

	];

	// funcs: Before content
	$conf->funcs->before_content = [
		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=publish
		'picklesFramework2\commands\publish::register' ,

	];


	// processor
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = [
		// ページ内目次を自動生成する
		'picklesFramework2\processors\autoindex\autoindex::exec' ,

		// テーマ
		'theme' => \tomk79\pickles2\multitheme\theme::exec(array(
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id'=>'standard',
			'options'=>array(
				'standard2'=>array(
					'sample_param'=>'hoge',
				),
			),
		)),

		// Apache互換のSSIの記述を解決する
		'picklesFramework2\processors\ssi\ssi::exec' ,

		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->css = [
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->js = [
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->md = [
		// Markdown文法を処理する
		'picklesFramework2\processors\md\ext::exec' ,

		// html の処理を追加
		$conf->funcs->processor->html ,
	];

	$conf->funcs->processor->scss = [
		// SCSS文法を処理する
		'picklesFramework2\processors\scss\ext::exec' ,

		// css の処理を追加
		$conf->funcs->processor->css ,
	];


	// funcs: Before output
	$conf->funcs->before_output = [
	];



	// -------- PHP Setting --------

	// [memory_limit]
	// PHPのメモリの使用量の上限を設定します。
	// 正の整数値で上限値(byte)を与えます。
	//     例: 1000000 (1,000,000 bytes)
	//     例: "128K" (128 kilo bytes)
	//     例: "128M" (128 mega bytes)
	// -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	// サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	// @ini_set( 'memory_limit' , -1 );
	@ini_set( 'display_errors' , 'On' );
	@ini_set( 'error_reporting' , 32767 );



	return $conf;
} );
