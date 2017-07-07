<?php
/**
 * Pickles2 Multi Theme template utility class
 */
namespace tomk79\pickles2\multitheme;

/**
 * Pickles2 Multi Theme template utility class
 */
class template_utility{
	/** Picklesオブジェクト */
	private $px;

	/** px2-multithemeオブジェクト */
	private $multitheme;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $multitheme px2-multithemeオブジェクト
	 */
	public function __construct($px, $multitheme){
		$this->px = $px;
		$this->multitheme = $multitheme;
	} // __construct()

	/**
	 * テーマごとのオプションを取得する
	 *
	 * コンフィグオプションに指定されたテーマ別設定の値を取り出します。
	 *
	 * @param string $key 取り出したいオプションのキー
	 */
	public function get_option($key){
		return $this->multitheme->get_option($key);
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
		return $this->multitheme->get_layout();
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
		return $this->multitheme->get_attr_bowl_name_by();
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
			$rtn .= '<li>';
			if( $this->px->href($pid) != $this->px->href($this->px->site()->get_current_page_info('id')) ){
				$rtn .= $this->px->mk_link( $pid, array('label'=>$this->px->site()->get_page_info($pid, 'title_breadcrumb'), 'current'=>false) );
			}else{
				$rtn .= '<span>'.htmlspecialchars( $this->px->site()->get_page_info($pid, 'title_breadcrumb') ).'</span>';
			}
			$rtn .= '</li>';
		}
		$rtn .= '<li><span>'.htmlspecialchars( $this->px->site()->get_current_page_info('title_breadcrumb') ).'</span></li>';
		$rtn .= '</ul>';
		return $rtn;
	}

	/**
	 * テーマリソースへのパスを取得する
	 * @param  string $path_resource `theme_files` をルートとしたリソースのパス
	 * @return string リソースへの実際のパス
	 */
	public function files($path_resource){
		return $this->px->path_plugin_files($path_resource);
	}

}
