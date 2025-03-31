<?php
/**
 * Pickles2 Multi Theme: Kaleflower processor
 */
namespace tomk79\pickles2\multitheme\ext;

/**
 * Pickles2 Multi Theme: Kaleflower processor
 */
class kflow {
	/** $multitheme */
	private $multitheme;

	/**
	 * constructor
	 * @param object $multitheme メインオブジェクト
	 */
	public function __construct($multitheme){
		$this->multitheme = $multitheme;
	}

	/**
	 * bind content to theme
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $theme テーマオブジェクト
	 * @return string テーマを実行した結果のHTMLコード
	 */
	public function bind( $px, $theme, $pageInfo, $path_theme_layout_file ){

		$realpath_plugin_private_cache = $px->realpath_plugin_private_cache('/_kflow/'.urlencode($this->multitheme->get_theme_id()).'/'.urlencode($pageInfo['layout']).'/');
		$path_files_base = '/kflow/'.urlencode($this->multitheme->get_theme_id()).'/layouts/'.urlencode($pageInfo['layout']).'/';
		$realpath_files_base = $px->realpath_plugin_files($path_files_base);
		$px->fs()->mkdir_r($realpath_plugin_private_cache);

		if( $px->fs()->is_newer_a_than_b($realpath_plugin_private_cache.'layout.html', $path_theme_layout_file) ){
			// キャッシュが新しいので、キャッシュを返す
			$src = self::exec_content( $px, $theme, $realpath_plugin_private_cache.'layout.html' );
			return $src;
		}

		// --------------------------------------
		// Kaleflowerをビルドする
		$kaleflower = new \kaleflower\kaleflower();
		$kflowResult = $kaleflower->build(
			$path_theme_layout_file,
			array(
				'assetsPrefix' => './theme_files/layouts/'.urlencode($pageInfo['layout']).'/resources/',
			)
		);

		// --------------------------------------
		// CSSを出力する
		$src_css = '';
		$realpath_css = $px->fs()->get_realpath($realpath_files_base.'/style.css');
		if( strlen($kflowResult->css ?? '') ){
			if(!is_file($realpath_css) || md5_file($realpath_css) !== md5($kflowResult->css)){
				$px->fs()->mkdir_r(dirname($realpath_css));
				$px->fs()->save_file($realpath_css, $kflowResult->css);
			}
			$src_css = '<link rel="stylesheet" href="'.htmlspecialchars($px->path_plugin_files($path_files_base.'style.css')).'" />';
		}elseif(is_file($realpath_css)){
			$px->fs()->rm($realpath_css);
		}

		// --------------------------------------
		// JSを出力する
		$src_js = '';
		$realpath_js = $px->fs()->get_realpath($realpath_files_base.'/script.js');
		if( strlen($kflowResult->js ?? '') ){
			if(!is_file($realpath_js) || md5_file($realpath_js) !== md5($kflowResult->js)){
				$px->fs()->mkdir_r(dirname($realpath_js));
				$px->fs()->save_file($realpath_js, $kflowResult->js);
			}
			$src_js = '<script src="'.htmlspecialchars($px->path_plugin_files($path_files_base.'script.js')).'"></script>';
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

		// --------------------------------------
		// テーマを実行してHTMLを生成
		$src_theme_layout = $this->bind_template($kflowResult->html);
		$src_theme_layout = preg_replace('/(\<\/head\>)/si', $src_css.$src_js.'$1', $src_theme_layout);
		$src_theme_layout = str_replace('./theme_files/layouts/'.urlencode($pageInfo['layout']).'/resources/', $px->path_plugin_files($path_files_base.'resources/'), $src_theme_layout);

		$px->fs()->save_file($realpath_plugin_private_cache.'layout.html', $src_theme_layout);
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

	/**
	 * テンプレートをバインドする。
	 * @param array $htmls HTMLコード
	 * @return string テンプレート
	 */
	private function bind_template($htmls){
		$fin = '';
		foreach( $htmls as $bowlId=>$html ){
			if( $bowlId == 'main' ){
				$fin .= $htmls->main;
			}else{
				$fin .= "\n";
				$fin .= "\n";
				$fin .= '<'.'?php ob_start(); ?'.'>'."\n";
				$fin .= (strlen($htmls->{$bowlId} ?? '') ? $htmls->{$bowlId}."\n" : '');
				$fin .= '<'.'?php $px->bowl()->send( ob_get_clean(), '.json_encode($bowlId).' ); ?'.'>'."\n";
				$fin .= "\n";
			}
		}
		$template = '<'.'%- body %'.'>';
		$pathKflowThemeLayout = $this->multitheme->realpath_theme_dir().'/kflow/_layout.html';
		if(is_file($pathKflowThemeLayout)){
			$template = file_get_contents( $pathKflowThemeLayout );
		}
		// PHP では ejs は使えないので、単純置換することにした。
		// $fin = $ejs.render($template, {'body': $fin}, {'delimiter': '%'});
		$fin = str_replace('<'.'%- body %'.'>', $fin, $template);

		return $fin;
	}
}
