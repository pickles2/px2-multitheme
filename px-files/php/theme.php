<?php
/**
 * Pickles2 Multi Theme CORE class
 */
namespace tomk79\pickles2\multitheme;

/**
 * Pickles2 Multi Theme CORE class
 */
class theme{
	/** Picklesオブジェクト */
	private $px;
	/** テーマディレクトリのパス */
	private $path_theme_dir;
	/** カレントページの情報 */
	private $page;
	/** テーマスイッチ名 */
	private $param_theme_switch = 'THEME';
	/** テーマ名を格納するクッキー名 */
	private $cookie_theme_switch = 'THEME';
	/** レイアウトスイッチ名 */
	private $param_layout_switch = 'LAYOUT';
	/** 選択されるテーマID */
	private $theme_id = 'default';
	/** テーマコレクション */
	private $theme_collection;
	/** テーマのコンフィグオプション */
	private $theme_options;
	/** px2-multithemeの設定情報 */
	private $conf;

	/**
	 * entry method
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public static function exec( $px, $options = null ){
		$theme = new self($px, $options);
		$src = $theme->bind($px);
		$px->bowl()->replace($src, '');
		return true;
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public function __construct($px, $options = null){
		$this->px = $px;
		if( !is_object($options) ){
			$options = new \stdClass();
		}

		$this->conf = new \stdClass();
		$this->conf->path_theme_collection = $this->px->get_path_homedir().'themes'.DIRECTORY_SEPARATOR;
		if( property_exists($options, 'path_theme_collection') && strlen(''.@$options->path_theme_collection) ){
			$this->conf->path_theme_collection = $this->px->fs()->get_realpath($options->path_theme_collection.DIRECTORY_SEPARATOR);
		}
		$this->conf->default_theme_id = 'default';

		if( property_exists($options, 'default_theme_id') && strlen(''.@$options->default_theme_id) ){
			$this->conf->default_theme_id = $options->default_theme_id;
		}
		$this->conf->attr_bowl_name_by = 'data-contents-area';
		if( property_exists($options, 'attr_bowl_name_by') && strlen(''.@$options->attr_bowl_name_by) ){
			$this->conf->attr_bowl_name_by = $options->attr_bowl_name_by;
		}
		if( property_exists($options, 'param_theme_switch') && strlen(''.@$options->param_theme_switch) ){
			$this->param_theme_switch = $options->param_theme_switch;
		}
		if( property_exists($options, 'cookie_theme_switch') && strlen(''.@$options->cookie_theme_switch) ){
			$this->cookie_theme_switch = $options->cookie_theme_switch;
		}
		if( property_exists($options, 'param_layout_switch') && strlen(''.@$options->param_layout_switch) ){
			$this->param_layout_switch = $options->param_layout_switch;
		}

		$this->theme_options = (property_exists($options, 'options') ? $options->options : new \stdClass());
		$this->theme_options = json_decode( json_encode($this->theme_options), true );
		// var_dump($this->theme_options);


		// サイトマップからページ情報を取得
		$this->page = $this->px->site()->get_current_page_info();
		if( @!strlen( ''.$this->page['layout'] ) ){
			$this->page['layout'] = 'default';
		}

		// テーマを選択する
		$this->auto_select_theme();


		// テーマディレクトリを決定する
		$path_composer_root_dir = $this->get_composer_root_dir();
		preg_match('/^([a-zA-Z0-9\_\-\.]*)(?:\/([a-zA-Z0-9\_\-\.]*))?(?:\@([0-9]*?))?$/', $this->theme_id, $matched);
		$theme_id_1 = null;
		if( array_key_exists(1, $matched) ){
			$theme_id_1 = @$matched[1];
		}
		$theme_id_2 = null;
		if( array_key_exists(2, $matched) ){
			$theme_id_2 = @$matched[2];
		}
		$theme_id_num = null;
		if( array_key_exists(3, $matched) ){
			$theme_id_num = @$matched[3];
		}

		if( !strlen(''.$this->theme_id) || $this->theme_id == '@'.$theme_id_num ){
			// 自身の composer.json を探す
			$composer_json = @$this->px->fs()->read_file($path_composer_root_dir.'/composer.json');
			$composer_json = @json_decode($composer_json);
			if( !strlen(''.$this->theme_id) && $composer_json->extra->px2package->type == 'theme' ){
				$this->path_theme_dir = $this->px->fs()->get_realpath( $path_composer_root_dir.'/'.$composer_json->extra->px2package->path );
			}elseif( $this->theme_id == '@'.$theme_id_num && $composer_json->extra->px2package[$theme_id_num]->type == 'theme' ){
				$this->path_theme_dir = $this->px->fs()->get_realpath( $path_composer_root_dir.'/'.$composer_json->extra->px2package[$theme_id_num]->path );
			}
		}
		if( !is_dir(''.$this->path_theme_dir) && $theme_id_1 == $this->theme_id ){
			// テーマコレクションを探す
			$this->path_theme_dir = $this->px->fs()->get_realpath( $this->conf->path_theme_collection.'/'.$this->theme_id.'/' );
		}
		if( !is_dir(''.$this->path_theme_dir) ){
			// vendor内の composer.json を探す
			if( is_dir(''.$path_composer_root_dir.'/vendor/'.$theme_id_1.'/'.$theme_id_2.'/') ){
				$tmp_composer_pkg_root = $path_composer_root_dir.'/vendor/'.$theme_id_1.'/'.$theme_id_2;
				$composer_json = @$this->px->fs()->read_file($tmp_composer_pkg_root.'/composer.json');
				$composer_json = @json_decode($composer_json);
				if( $composer_json->extra->px2package->type == 'theme' ){
					$this->path_theme_dir = $this->px->fs()->get_realpath( $tmp_composer_pkg_root.'/'.$composer_json->extra->px2package->path );
				}elseif( $composer_json->extra->px2package[$theme_id_num]->type == 'theme' ){
					$this->path_theme_dir = $this->px->fs()->get_realpath( $tmp_composer_pkg_root.'/'.$composer_json->extra->px2package[$theme_id_num]->path );
				}
				unset($tmp_composer_pkg_root);
			}
		}
		if( !is_dir(''.$this->path_theme_dir) ){
			// vendor内の themeフォルダ を探す
			$this->path_theme_dir = $this->px->fs()->get_realpath( $path_composer_root_dir.'/vendor/'.$this->theme_id.'/theme/' );
		}
		unset($theme_id_1, $theme_id_2, $theme_id_num);


		// テーマのリソースファイルをキャッシュに複製する
		if( is_dir(''.$this->path_theme_dir.'/theme_files/') ){
			$this->px->fs()->copy_r(
				$this->path_theme_dir.'/theme_files/' ,
				$this->px->realpath_plugin_files('/'.urlencode($this->theme_id).'/')
			);
		}
	} // __construct()

	/**
	 * auto select theme
	 */
	private function auto_select_theme(){
		$this->theme_id = @$this->conf->default_theme_id;
		if( !strlen( ''.$this->theme_id ) ){
			$this->theme_id = 'default';
		}

		if( strlen( ''.@$this->px->req()->get_cookie($this->cookie_theme_switch) ) ){
			$this->theme_id = @$this->px->req()->get_cookie($this->cookie_theme_switch);
		}

		$param_theme_id = $this->px->req()->get_param($this->param_theme_switch);
		if( strlen( ''.$param_theme_id ) && $this->is_valid_theme_id( $param_theme_id ) ){
			// GETパラメータに、有効な THEME が入ってたら

			if( $this->theme_id !== $param_theme_id ){
				// 現在選択中のテーマと別のIDだったら

				if( $this->px->fs()->is_dir( $this->conf->path_theme_collection.'/'.$param_theme_id.'/' ) || $this->px->fs()->is_dir( $this->get_composer_root_dir().'/vendor/'.$param_theme_id.'/theme/' ) ){
					// テーマが実在していたら

					// $plugin_cache_dir = $this->px->realpath_plugin_files('/');
					// $this->px->fs()->rm( $plugin_cache_dir );// ← テーマを切り替える際に、公開キャッシュを一旦削除する
					$this->theme_id = $param_theme_id;
					$this->px->req()->set_cookie( $this->cookie_theme_switch, $this->theme_id );

					if( $this->theme_id == @$this->conf->default_theme_id ){
						$this->px->req()->delete_cookie( $this->cookie_theme_switch );
					}
				}
			}
		}

		return true;
	}

	/**
	 * レイアウトを選択し、ファイルのパスを取得する
	 *
	 * 1. まず、パラメータ LAYOUT が指定されていて、かつレイアウトファイルが存在したら それが最優先。
	 * 2. 次に、ページに layout 列が指定されていて、かつレイアウトファイルが存在したら それを採用。
	 * 3. 次に、固定文字列 'default' でレイアウトファイルを探し、存在したらそれを採用。
	 * 4. どれも該当がなければ、 固定レイアウト './default/default.html' を採用する。
	 *
	 * @return string レイアウトファイルのパス
	 */
	private function find_layout_realpath(){

		// 1. パラメータに指定された LAYOUT を探す
		$param_layout_switch = $this->px->req()->get_param($this->param_layout_switch);
		if( strlen(''.$this->param_layout_switch) && strlen(''.$param_layout_switch) ){
			if( $this->px->fs()->is_file($this->path_theme_dir.$param_layout_switch.'.html') ){
				$this->page['layout'] = $param_layout_switch;
				return $this->px->fs()->get_realpath( $this->path_theme_dir.$this->page['layout'].'.html' );
			}
		}

		// 2. ページに指定された layout を探す
		if( $this->px->fs()->is_file( $this->path_theme_dir.$this->page['layout'].'.html' ) ){
			return $this->px->fs()->get_realpath( $this->path_theme_dir.$this->page['layout'].'.html' );
		}

		// 3. 固定文字列 'default' で探す
		if( $this->px->fs()->is_file( $this->path_theme_dir.'default'.'.html' ) ){
			$this->page['layout'] = 'default';
			return $this->px->fs()->get_realpath( $this->path_theme_dir.$this->page['layout'].'.html' );
		}

		// 4. 固定レイアウトを返す
		return $this->px->fs()->get_realpath( __DIR__.'/default/default.html' );
	}

	/**
	 * bind content to theme
	 *
	 * @param object $px Picklesオブジェクト
	 * @return string テーマを実行した結果のHTMLコード
	 */
	private function bind( $px ){
		$path_theme_layout_file = $this->find_layout_realpath();

		$theme = new template_utility( $px, $this );

		ob_start();
		include( $path_theme_layout_file );
		$tmp_src = ob_get_clean();

		$src = '';

		// `./theme_files/*` で始まるリソースパスを、 `$theme->files()` に通して成立させる処理
		while(1){
			if( !preg_match('/^(.*?)([\"\'])(?:\.\/)?theme_files\/(.*?)\2(.*)$/s', $tmp_src, $matched) ){
				$src .= $tmp_src;
				break;
			}
			$src .= $matched[1];
			$src .= $matched[2];
			$src .= $theme->files('/'.$matched[3]);
			$src .= $matched[2];
			$tmp_src = $matched[4];
		}

		return $src;
	}

	/**
	 * テーマごとのオプションを取得する
	 *
	 * コンフィグオプションに指定されたテーマ別設定の値を取り出します。
	 *
	 * @param string $key 取り出したいオプションのキー
	 * @return mixed テーマのオプション
	 */
	public function get_option($key){
		return @$this->theme_options[$this->theme_id][$key];
	}


	/**
	 * composer のルートディレクトリのパスを取得する
	 *
	 * @return string vendorディレクトリの絶対パス
	 */
	private function get_composer_root_dir(){
		$tmp_composer_root_dir = $this->px->fs()->get_realpath( '.' );
		// var_dump($tmp_composer_root_dir);
		while(1){
			if( $this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/' ) && $this->px->fs()->is_file( $tmp_composer_root_dir.'/composer.json' ) ){
				break;
			}
			if( realpath($tmp_composer_root_dir) == realpath( dirname($tmp_composer_root_dir) ) ){
				$tmp_composer_root_dir = false;
				break;
			}
			$tmp_composer_root_dir = dirname($tmp_composer_root_dir);
			continue;
		}
		// var_dump( $tmp_composer_root_dir );
		return $tmp_composer_root_dir;
	}

	/**
	 * テーマコレクションを作成する
	 *
	 * テーマコレクションディレクトリおよびvendorディレクトリを検索し、
	 * 選択可能なテーマの一覧を生成します。
	 *
	 * @return array テーマコレクション
	 */
	public function mk_theme_collection(){
		$collection = array();

		// vendorディレクトリ内から検索
		$tmp_composer_root_dir = $this->get_composer_root_dir();
		// var_dump( $tmp_composer_root_dir );

		if( $this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/' ) ){
			foreach( $this->px->fs()->ls( $tmp_composer_root_dir.'/vendor/' ) as $vendor_id ){
				if( !$this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/'.$vendor_id ) ){ continue; }
				foreach( $this->px->fs()->ls( $tmp_composer_root_dir.'/vendor/'.$vendor_id ) as $package_id ){

					// themeディレクトリがある場合 (旧方式)
					if( $this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/theme/' ) ){
						$collection[$vendor_id.'/'.$package_id] = [
							'id'=>$vendor_id.'/'.$package_id,
							'path'=>$this->px->fs()->get_realpath( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/theme/' ),
							'type'=>'vendor'
						];
					}

					// px2package に theme が定義されている場合
					if( $this->px->fs()->is_file( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/composer.json' ) ){
						$composer_json = $this->px->fs()->read_file( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/composer.json' );
						$composer_json = @json_decode($composer_json);
						if( @is_array($composer_json->extra->px2package) ){
							foreach( $composer_json->extra->px2package as $package_idx=>$package ){
								if( @$package->type == 'theme' ){
									$collection[$vendor_id.'/'.$package_id.'@'.$package_idx] = [
										'id'=>$vendor_id.'/'.$package_id.'@'.$package_idx,
										'path'=>$this->px->fs()->get_realpath( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/'.@$package->path ),
										'type'=>'vendor'
									];
								}
							}
						}elseif( @$composer_json->extra->px2package->type == 'theme' ){
							$collection[$vendor_id.'/'.$package_id] = [
								'id'=>$vendor_id.'/'.$package_id,
								'path'=>$this->px->fs()->get_realpath( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/'.@$composer_json->extra->px2package->path ),
								'type'=>'vendor'
							];
						}
					}

				}
			}
		}

		// テーマコレクションを作成
		// (vendor内よりも優先)
		foreach( $this->px->fs()->ls( $this->conf->path_theme_collection ) as $theme_id ){
			$collection[$theme_id] = [
				'id'=>$theme_id,
				'path'=>$this->px->fs()->get_realpath( $this->conf->path_theme_collection.'/'.$theme_id.'/' ),
				'type'=>'collection'
			];
		}

		// 自身の composer.json から作成
		// (最優先)
		if( $this->px->fs()->is_file( $tmp_composer_root_dir.'/composer.json' ) ){
			$composer_json = $this->px->fs()->read_file( $tmp_composer_root_dir.'/composer.json' );
			$composer_json = @json_decode($composer_json);
			if( @is_array($composer_json->extra->px2package) ){
				foreach( $composer_json->extra->px2package as $package_idx=>$package ){
					if( @$package->type == 'theme' ){
						$collection['@'.$package_idx] = [
							'id'=>'@'.$package_idx,
							'path'=>$this->px->fs()->get_realpath( $tmp_composer_root_dir.'/'.@$package->path ),
							'type'=>'vendor'
						];
					}
				}
			}elseif( @$composer_json->extra->px2package->type == 'theme' ){
				$collection[''] = [
					'id'=>'',
					'path'=>$this->px->fs()->get_realpath( $tmp_composer_root_dir.'/'.@$composer_json->extra->px2package->path ),
					'type'=>'vendor'
				];
			}
		}

		// var_dump($collection);
		return $collection;
	}

	/**
	 * テーマIDとして有効な文字列か検証する
	 *
	 * @param string $theme_id 検証対象のテーマID
	 * @return bool 有効なら true, 無効なら false
	 */
	public function is_valid_theme_id( $theme_id ){
		if( preg_match('/[^a-zA-Z0-9\/\.\-\_]/', $theme_id) ){ return false; }
		if( preg_match('/(?:^|\/)[\.]{1,2}(?:$|\/)/', $theme_id) ){ return false; }
		if( preg_match('/^\//', $theme_id) ){ return false; }
		if( preg_match('/\/$/', $theme_id) ){ return false; }
		if( preg_match('/\/\//', $theme_id) ){ return false; }

		return true;
	}

	/**
	 * 選択されたレイアウト名を取得する
	 *
	 * レイアウトは、Pickles 2 のサイトマップCSVの layout 列に指定すると選択できます。
	 *
	 * layout列には、拡張子を含まない値を指定してください。
	 * レイアウト `hoge.html` を選択したい場合、 layout列には `hoge` と入力します。
	 *
	 * layout列が空白の場合、 `default.html` が選択されます。
	 * @return string レイアウト名
	 */
	public function get_layout(){
		return @$this->page['layout'];
	}

	/**
	 * 選択されたテーマIDを取得する
	 *
	 * @return string Theme ID
	 */
	public function get_theme_id(){
		return @$this->theme_id;
	}

	/**
	 * $conf->attr_bowl_name_by 設定の値を受け取る
	 *
	 * このメソッドが返す値は、 テーマのコンテンツエリアを囲うラッパー要素にセットされるべき、bowl名を格納するための属性名です。
	 * デフォルトは `data-contents-area` ですが、コンフィグオプションで変更することができます。
	 *
	 * bowl `main` は次のように実装します。
	 * ```
	 * <div class="contents" <?= htmlspecialchars($theme->get_attr_bowl_name_by())?>="main">
	 * 	 <?= $px->bowl()->pull() ?>
	 * </div>
	 * ```
	 *
	 * 独自の名前 `hoge` という bowl を作るには、次のように実装します。
	 * ```
	 * <div class="contents" <?= htmlspecialchars($theme->get_attr_bowl_name_by())?>="hoge">
	 * 	 <?= $px->bowl()->pull('hoge') ?>
	 * </div>
	 * ```
	 *
	 * この値は、 Pickles 2 Desktop Tool のGUI編集機能が、テーマの画面から編集可能領域を探しだすために利用します。
	 *
	 * @return string bowl名を格納するための属性名
	 */
	public function get_attr_bowl_name_by(){
		return $this->conf->attr_bowl_name_by;
	}

}
