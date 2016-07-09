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

		$this->conf = new \stdClass();
		$this->conf->path_theme_collection = $this->px->get_path_homedir().'themes'.DIRECTORY_SEPARATOR;
		if( strlen(@$options->path_theme_collection) ){
			$this->conf->path_theme_collection = $this->px->fs()->get_realpath($options->path_theme_collection.DIRECTORY_SEPARATOR);
		}
		$this->conf->default_theme_id = 'default';

		if( strlen(@$options->default_theme_id) ){
			$this->conf->default_theme_id = $options->default_theme_id;
		}
		$this->conf->attr_bowl_name_by = 'data-contents-area';
		if( strlen(@$options->attr_bowl_name_by) ){
			$this->conf->attr_bowl_name_by = $options->attr_bowl_name_by;
		}
		if( strlen(@$options->param_theme_switch) ){
			$this->param_theme_switch = $options->param_theme_switch;
		}
		if( strlen(@$options->cookie_theme_switch) ){
			$this->cookie_theme_switch = $options->cookie_theme_switch;
		}

		$this->theme_options = (@$options->options ? $options->options : new \stdClass());
		$this->theme_options = json_decode( json_encode($this->theme_options), true );
		// var_dump($this->theme_options);


		// サイトマップからページ情報を取得
		$this->page = $this->px->site()->get_current_page_info();
		if( @!strlen( $this->page['layout'] ) ){
			$this->page['layout'] = 'default';
		}

		// テーマを選択する
		$this->auto_select_theme();


		// テーマディレクトリを決定する
		$this->path_theme_dir = $this->px->fs()->get_realpath( $this->conf->path_theme_collection.'/'.$this->theme_id.'/' );
		if( !is_dir($this->path_theme_dir) ){
			$this->path_theme_dir = $this->px->fs()->get_realpath( $this->get_composer_root_dir().'/vendor/'.$this->theme_id.'/theme/' );
		}

		// テーマのリソースファイルをキャッシュに複製する
		if( is_dir($this->path_theme_dir.'/theme_files/') ){
			$this->px->fs()->copy_r(
				$this->path_theme_dir.'/theme_files/' ,
				$this->px->realpath_plugin_files('/')
			);
		}
	} // __construct()

	/**
	 * auto select theme
	 */
	private function auto_select_theme(){
		$this->theme_id = @$this->conf->default_theme_id;
		if( !strlen( $this->theme_id ) ){
			$this->theme_id = 'default';
		}

		if( strlen( @$this->px->req()->get_cookie($this->cookie_theme_switch) ) ){
			$this->theme_id = @$this->px->req()->get_cookie($this->cookie_theme_switch);
		}

		if( strlen( $this->px->req()->get_param($this->param_theme_switch) ) ){
			if( $this->theme_id !== $this->px->req()->get_param($this->param_theme_switch) ){
				$plugin_cache_dir = $this->px->realpath_plugin_files('/');
				$this->px->fs()->rm($plugin_cache_dir);// ← テーマを切り替える際に、公開キャッシュを一旦削除する
				$this->theme_id = $this->px->req()->get_param($this->param_theme_switch);
				$this->px->req()->set_cookie( $this->cookie_theme_switch, $this->theme_id );

				if( $this->theme_id == @$this->conf->default_theme_id ){
					$this->px->req()->delete_cookie( $this->cookie_theme_switch );
				}
			}
		}

		return true;
	}

	/**
	 * bind content to theme
	 * @param object $px Picklesオブジェクト
	 */
	private function bind( $px ){
		if( !$px->fs()->is_file( $this->path_theme_dir.$this->page['layout'].'.html' ) ){
			$this->page['layout'] = 'default';
		}

		$theme = $this;

		ob_start();
		include( $this->path_theme_dir.$this->page['layout'].'.html' );
		$src = ob_get_clean();
		return $src;
	}

	/**
	 * テーマごとのオプションを取得する
	 *
	 * コンフィグオプションに指定されたテーマ別設定の値を取り出します。
	 * @param string $key 取り出したいオプションのキー
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
	 * @return array テーマコレクション
	 */
	public function mk_theme_collection(){
		$collection = array();

		// テーマコレクションを作成
		foreach( $this->px->fs()->ls( $this->conf->path_theme_collection ) as $theme_id ){
			$collection[$theme_id] = [
				'id'=>$theme_id,
				'path'=>$this->px->fs()->get_realpath( $this->conf->path_theme_collection.'/'.$theme_id.'/' ),
				'type'=>'collection'
			];
		}

		// vendorディレクトリ内から検索
		$tmp_composer_root_dir = $this->get_composer_root_dir();
		// var_dump( $tmp_composer_root_dir );

		if( $this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/' ) ){
			foreach( $this->px->fs()->ls( $tmp_composer_root_dir.'/vendor/' ) as $vendor_id ){
				if( !$this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/'.$vendor_id ) ){ continue; }
				foreach( $this->px->fs()->ls( $tmp_composer_root_dir.'/vendor/'.$vendor_id ) as $package_id ){
					if( $this->px->fs()->is_dir( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/theme/' ) ){
						$collection[$vendor_id.'/'.$package_id] = [
							'id'=>$vendor_id.'/'.$package_id,
							'path'=>$px->fs()->get_realpath( $tmp_composer_root_dir.'/vendor/'.$vendor_id.'/'.$package_id.'/theme/' ),
							'type'=>'vendor'
						];
					}
				}
			}
		}
		// var_dump($collection);

		return $collection;
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
	 */
	public function get_layout(){
		return @$this->page['layout'];
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

	/**
	 * グローバルメニューを自動生成する
	 *
	 * サイトマップCSVに登録されたページの一覧から、グローバルメニューを自動生成し、HTMLコードを返します。
	 *
	 * 対象となるページの一覧は、 `$px->site()->get_global_menu()` から取得します。
	 *
	 * @return string HTMLコード
	 */
	public function mk_global_menu(){
		$global_menu = $this->px->site()->get_global_menu();
		if( !count($global_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $global_menu as $global_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $global_menu_page_id );
			$rtn .= $this->mk_sub_menu( $global_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	/**
	 * ショルダーメニューを自動生成する
	 *
	 * サイトマップCSVに登録されたページの一覧から、ショルダーメニューを自動生成し、HTMLコードを返します。
	 *
	 * 対象となるページの一覧は、 `$px->site()->get_shoulder_menu()` から取得します。
	 *
	 * @return string HTMLコード
	 */
	public function mk_shoulder_menu(){
		$shoulder_menu = $this->px->site()->get_shoulder_menu();
		if( !count($shoulder_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $shoulder_menu as $shoulder_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $shoulder_menu_page_id );
			$rtn .= $this->mk_sub_menu( $shoulder_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	/**
	 * 指定されたページの子階層のメニューを展開する
	 *
	 * 主にローカルナビゲーションを生成する用途を想定したメソッドです。 `$parent_page_id` に与えられたページを頂点として、ページの階層構造を HTML化して生成します。
	 * カレントページの直系の祖先にあたる階層は、子階層が開かれた状態で生成され、直系に当たらない階層は隠されます。
	 *
	 * @param string $parent_page_id 親ページのページID
	 * @return string ページリストのHTMLコード
	 */
	public function mk_sub_menu( $parent_page_id ){
		$rtn = '';
		$children = $this->px->site()->get_children( $parent_page_id );
		if( count($children) ){
			$rtn .= '<ul>'."\n";
			foreach( $children as $child ){
				$rtn .= '<li>'.$this->px->mk_link( $child );
				if( $this->px->site()->is_page_in_breadcrumb( $child ) ){
					$rtn .= $this->mk_sub_menu( $child );//←再帰的呼び出し
				}
				$rtn .= '</li>'."\n";
			}
			$rtn .= '</ul>'."\n";
		}
		return $rtn;
	}

	/**
	 * メガフッターメニューを自動生成する
	 *
	 * メガフッターに表示する項目として、グローバルメニューの一覧と、その子階層までの一覧を構造化し、HTMLコードとして生成します。
	 *
	 * @return string メガフッターのHTMLコード
	 */
	public function mk_megafooter_menu(){
		$global_menu = $this->px->site()->get_global_menu();
		if( !count($global_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $global_menu as $global_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $global_menu_page_id );
			$children = $this->px->site()->get_children( $global_menu_page_id );
			if( count( $children ) ){
				$rtn .= '<ul>'."\n";
				foreach( $children as $child_page_id ){
					$rtn .= '<li>'.$this->px->mk_link( $child_page_id );
					$rtn .= '</li>'."\n";
				}
				$rtn .= '</ul>'."\n";
			}
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}

	/**
	 * パンくずを自動生成する
	 *
	 * このメソッドは、パンくずリストのHTMLコードを生成して返します。
	 * 祖先ページは aタグ で囲われ、カレントページは aタグの代わりに spanタグ で囲われます。
	 *
	 * @return string パンくずのHTMLコード
	 */
	public function mk_breadcrumb(){
		$breadcrumb = $this->px->site()->get_breadcrumb_array();
		$rtn = '';
		$rtn .= '<ul>';
		foreach( $breadcrumb as $pid ){
			$rtn .= '<li>'.$this->px->mk_link( $pid, array('label'=>$this->px->site()->get_page_info($pid, 'title_breadcrumb'), 'current'=>false) ).'</li>';
		}
		$rtn .= '<li><span>'.htmlspecialchars( $this->px->site()->get_current_page_info('title_breadcrumb') ).'</span></li>';
		$rtn .= '</ul>';
		return $rtn;
	}

}
