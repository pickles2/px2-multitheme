<?php
/**
 * Pickles2 Multi Theme: Kaleflower processor
 */
namespace tomk79\pickles2\multitheme\ext;

/**
 * Pickles2 Multi Theme: Kaleflower processor
 */
class kflow {
	/** $main */
	private $main;

	/**
	 * constructor
	 * @param object $main メインオブジェクト
	 */
	public function __construct($main){
		$this->main = $main;
	}

	/**
	 * bind content to theme
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $theme テーマオブジェクト
	 * @return string テーマを実行した結果のHTMLコード
	 */
	public function bind( $px, $theme, $pageInfo, $path_theme_layout_file ){

		$realpath_plugin_private_cache = $px->realpath_plugin_private_cache('/_kflow/'.urlencode($this->main->get_theme_id()).'/'.urlencode($pageInfo['layout']).'/');
		$px->fs()->mkdir_r($realpath_plugin_private_cache);

		$kaleflower = new \kaleflower\kaleflower();
		$kflowResult = $kaleflower->build(
			$path_theme_layout_file,
			array(
				'assetsPrefix' => './theme_files/layouts/'.urlencode($pageInfo['layout']).'/resources/',
			)
		);
		$px->fs()->save_file($realpath_plugin_private_cache.'layout.html', $kflowResult->html->main);;

		// --------------------------------------
		// CSSを出力する
		$realpath_files_base = $px->realpath_plugin_files('/'.urlencode($this->main->get_theme_id()).'/layouts/'.urlencode($pageInfo['layout']).'/');

		$realpath_css = $px->fs()->get_realpath($realpath_files_base.'/style.css');
		if( strlen($kflowResult->css ?? '') ){
			if(!is_file($realpath_css) || md5_file($realpath_css) !== md5($kflowResult->css)){
				$px->fs()->mkdir_r(dirname($realpath_css));
				$px->fs()->save_file($realpath_css, $kflowResult->css);
			}
			$px->bowl()->replace( '<link rel="stylesheet" href="'.htmlspecialchars($px->path_plugin_files('/'.urlencode($this->main->get_theme_id()).'/layouts/'.urlencode($pageInfo['layout']).'/style.css')).'" />', 'head' );
		}elseif(is_file($realpath_css)){
			$px->fs()->rm($realpath_css);
		}

		// --------------------------------------
		// JSを出力する
		$realpath_js = $px->fs()->get_realpath($realpath_files_base.'/script.js');
		if( strlen($kflowResult->js ?? '') ){
			if(!is_file($realpath_js) || md5_file($realpath_js) !== md5($kflowResult->js)){
				$px->fs()->mkdir_r(dirname($realpath_js));
				$px->fs()->save_file($realpath_js, $kflowResult->js);
			}
			$px->bowl()->replace( '<script src="'.htmlspecialchars($px->path_plugin_files('/'.urlencode($this->main->get_theme_id()).'/layouts/'.urlencode($pageInfo['layout']).'/script.js')).'"></script>', 'foot' );
		}elseif(is_file($realpath_js)){
			$px->fs()->rm($realpath_js);
		}

		// --------------------------------------
		// アセットを出力する
		$asset_basename_list = array();
		if( count($kflowResult->assets ?? array()) ){
			foreach($kflowResult->assets as $asset){
				$asset_basename_list[basename($asset->path)] = true;
				$realpath_asset = $realpath_files_base.'resources/'.basename($asset->path);
				if(!is_file($realpath_asset) || md5_file($realpath_asset) !== md5(base64_decode($asset->base64))){
					$px->fs()->mkdir_r(dirname($realpath_asset));
					$px->fs()->save_file($realpath_asset, base64_decode($asset->base64));
				}
			}
		}

		// 未定義のアセットを削除
		$realpath_asset_dir = $realpath_files_base.'resources/';
		$file_list = $px->fs()->ls($realpath_asset_dir);
		if( is_array($file_list) && count($file_list) ){
			foreach($file_list as $file_basename){
				if( !($asset_basename_list[$file_basename] ?? null) ){
					$px->fs()->rm($realpath_asset_dir.$file_basename);
				}
			}
		}

		$src = self::exec_content( $px, $theme, $realpath_plugin_private_cache.'layout.html' );

		return $src;
	}

	/**
	 * コンテンツを実行する。
	 * @param object $px picklesオブジェクト
	 * @param object $theme テーマオブジェクト
	 * @return bool true
	 */
	private static function exec_content( $px, $theme, $realpath_template_cache ){
		ob_start();
		include( $realpath_template_cache );
		$src = ob_get_clean();
		return $src;
	}
}
