<?php
/**
 * test
 */

class mainTest extends PHPUnit_Framework_TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * Px2を実行してみる
	 */
	public function testMain(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/../.px_execute.php' , '/'] );

		// var_dump($output);
		$this->assertTrue( gettype($output) == gettype('') );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/../.px_execute.php' , '/?PX=clearcache'] );

	}//testMain()

	/**
	 * theme "standard"
	 */
	public function testStandard(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/'] );

		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('standard - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>FAILED</p>', '/').'/', $output ), 0 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );

	}//testStandard()

	/**
	 * theme "standard2"
	 */
	public function testStandard2(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?THEME=standard2'] );

		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('standard2 - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>FAILED</p>', '/').'/', $output ), 0 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );

	}//testStandard2()




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
