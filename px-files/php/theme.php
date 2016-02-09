<?php
/**
 * Pickles2 Multi Theme CORE class
 */
namespace tomk79\pickles2\multitheme;

/**
 * Pickles2 Multi Theme CORE class
 */
class theme{
	private $px;
	private $path_tpl;
	private $page;
	private $theme_id = 'default';
	private $theme_collection;
	private $conf;

	/**
	 * entry method
	 */
	public static function exec( $px, $options = null ){
		$theme = new self($px, $options);
		$src = $theme->bind($px);
		$px->bowl()->replace($src, '');
		return true;
	}

	/**
	 * constructor
	 */
	public function __construct($px, $options = null){
		$this->px = $px;

		$this->conf = new \stdClass();
		$this->conf->path_theme_collection = $this->px->get_path_homedir().'themes'.DIRECTORY_SEPARATOR;
		if( strlen(@$options->path_theme_collection) ){
			$this->conf->path_theme_collection = $this->px->fs()->get_realpath($options->path_theme_collection.DIRECTORY_SEPARATOR);
		}
		$this->conf->default_theme_id = 'default';
		// if( strlen(@$this->px->conf()->plugins->multitheme->default_theme_id) ){
		// 	$this->conf->default_theme_id = $this->px->conf()->plugins->multitheme->default_theme_id;
		// }
		if( strlen(@$options->default_theme_id) ){
			$this->conf->default_theme_id = $options->default_theme_id;
		}
		$this->conf->attr_bowl_name_by = 'data-contents-area';
		if( strlen(@$options->attr_bowl_name_by) ){
			$this->conf->attr_bowl_name_by = $options->attr_bowl_name_by;
		}


		$this->theme_collection = [];
		foreach( $px->fs()->ls( $this->conf->path_theme_collection ) as $theme_id ){
			$this->theme_collection[$theme_id] = [
				'id'=>$theme_id,
				'path'=>$px->fs()->get_realpath( $this->conf->path_theme_collection.'/'.$theme_id ).DIRECTORY_SEPARATOR
			];
		}

		$this->auto_select_theme();


		$this->path_tpl = $this->theme_collection[$this->theme_id]['path'];


		$this->page = $this->px->site()->get_current_page_info();
		if( @!strlen( $this->page['layout'] ) ){
			$this->page['layout'] = 'default';
		}
		if( !$px->fs()->is_file( $this->path_tpl.$this->page['layout'].'.html' ) ){
			$this->page['layout'] = 'default';
		}
		// $this->px->realpath_plugin_private_cache('/test/abc/test.inc');
		$this->px->fs()->copy_r( $this->path_tpl.'/theme_files/', $this->px->realpath_plugin_files('/') );
	}

	/**
	 * auto select theme
	 */
	private function auto_select_theme(){
		$this->theme_id = @$this->conf->default_theme_id;
		if( !strlen( $this->theme_id ) ){
			$this->theme_id = 'default';
		}
		if( strlen( $this->px->req()->get_param('THEME') ) ){
			if( @is_array( $this->theme_collection[$this->px->req()->get_param('THEME')] ) ){
				$this->theme_id = $this->px->req()->get_param('THEME');
				$this->px->req()->set_cookie( 'THEME', $this->theme_id );
			}
		}
		if( strlen( @$this->px->req()->get_cookie('THEME') ) ){
			$this->theme_id = @$this->px->req()->get_cookie('THEME');
		}
		if( $this->theme_id == @$this->conf->default_theme_id ){
			$this->px->req()->delete_cookie( 'THEME' );
		}
		if( @!is_array( $this->theme_collection[$this->theme_id] ) ){
			$this->theme_id = 'default';
		}

		return true;
	}

	/**
	 * bind content to theme
	 */
	private function bind( $px ){
		$theme = $this;
		ob_start();
		include( $this->path_tpl.$this->page['layout'].'.html' );
		$src = ob_get_clean();
		return $src;
	}


	/**
	 * グローバルナビを自動生成する
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
	 * ショルダーナビを自動生成する
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
	 * 指定されたページの小階層のメニューを展開する
	 *
	 * @param string $parent_page_id 親ページのページID
	 * @return string ページリストのHTMLソース
	 */
	public function mk_sub_menu( $parent_page_id ){
		$rtn = '';
		$children = $this->px->site()->get_children( $parent_page_id );
		if( count($children) ){
			$rtn .= '<ul>'."\n";
			foreach( $children as $child ){
				$rtn .= '<li>'.$this->px->mk_link( $child );
				$rtn .= $this->mk_sub_menu( $child );//←再帰的呼び出し
				$rtn .= '</li>'."\n";
			}
			$rtn .= '</ul>'."\n";
		}
		return $rtn;
	}

	/**
	 * グローバルナビを自動生成する
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
