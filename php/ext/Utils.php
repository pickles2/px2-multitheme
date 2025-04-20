<?php
namespace tomk79\pickles2\multitheme\ext;

/**
 * Utility
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class Utils {

	/**
	 * build Twig
	 * @param string $template テンプレート
	 * @param array $data 入力データ
	 * @param array $funcs カスタム関数
	 * @return string バインド済み文字列
	 */
	public function bindTwig($template, $data = array(), $funcs = array()){
		$rtn = $template;
		if( is_object($data) ){
			$data = (array) $data;
		}

		if( class_exists('\\Twig_Loader_Array') ){
			// Twig ^1.35, ^2.12
			$loader = new \Twig_Loader_Array(array(
				'index' => $template,
			));
			$twig = new \Twig_Environment($loader, array('debug' => true, 'autoescape' => 'html'));
			$twig->addExtension(new \Twig_Extension_Debug());
			foreach( $funcs as $fncName=>$callback ){
				$function = new \Twig_SimpleFunction($fncName, $callback);
				$twig->addFunction($function);
			}
			$rtn = $twig->render('index', $data);

		}elseif( class_exists('\\Twig\\Loader\\ArrayLoader') ){
			// Twig ^3.0.0
			$loader = new \Twig\Loader\ArrayLoader([
				'index' => $template,
			]);
			$twig = new \Twig\Environment($loader, array('debug' => true, 'autoescape' => 'html'));
			$twig->addExtension(new \Twig\Extension\DebugExtension());
			foreach( $funcs as $fncName=>$callback ){
				$function = new \Twig\TwigFunction($fncName, $callback);
				$twig->addFunction($function);
			}
			$rtn = $twig->render('index', $data);

		}

		return $rtn;
	}
}
