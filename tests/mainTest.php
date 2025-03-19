<?php
/**
 * test
 */

class mainTest extends PHPUnit\Framework\TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	private $fs;

	/**
	 * setup
	 */
	public function setUp() : void{
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * Px2を実行してみる
	 */
	public function testMain(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/../src_px2/.px_execute.php' , '/'] );
		$this->assertTrue( gettype($output) == gettype('') );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/../src_px2/.px_execute.php' , '/?PX=clearcache'] );
	}

	/**
	 * theme "standard"
	 */
	public function testValidThemeId(){
		$cd = realpath('.');
		chdir(__DIR__.'/testdata/standard/');
		$px = new picklesFramework2\px(__DIR__.'/testdata/standard/px-files/');
		$multitheme = new \tomk79\pickles2\multitheme\theme($px);

		$this->assertTrue( $multitheme->is_valid_theme_id('sample_param') );
		$this->assertTrue( $multitheme->is_valid_theme_id('vndr/pkg') );
		$this->assertTrue( $multitheme->is_valid_theme_id('vndr/pkg/sub') );
		$this->assertTrue( $multitheme->is_valid_theme_id('vndr.dir/pkg.pkg') );
		$this->assertTrue( $multitheme->is_valid_theme_id('vndr..dir/pkg..pkg') );
		$this->assertTrue( $multitheme->is_valid_theme_id('vndr...dir/pkg...pkg') );
		$this->assertTrue( $multitheme->is_valid_theme_id('.vndr...dir./.pkg...pkg.') );
		$this->assertTrue( $multitheme->is_valid_theme_id('..vndr...dir../..pkg...pkg..') );
		$this->assertTrue( $multitheme->is_valid_theme_id('...vndr...dir../..pkg...pkg...') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('/...vndr...dir../..pkg...pkg...') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('...vndr...dir../..pkg...pkg.../') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa//bbb') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('../..') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('./.') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa/.') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa/..') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('./aaa') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('../aaa') );
		$this->assertTrue( $multitheme->is_valid_theme_id('aaa/.../bbb') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa/../bbb') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa/./bbb') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('aaa'."\n".'bbb') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a%b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a!b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a@b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a#b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a$b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a%b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a^b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a&b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a*b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a(b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a)b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a{b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a}b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a[b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a]b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a\\b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a|b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a~b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a`b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a:b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a;b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a\'b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a"b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a<b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a>b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a,b') );
		$this->assertTrue( $multitheme->is_valid_theme_id('a.b') );
		$this->assertTrue( $multitheme->is_valid_theme_id('a/b') );
		$this->assertTrue( !$multitheme->is_valid_theme_id('a?b') );

		chdir($cd);
	}


	/**
	 * theme "standard"
	 */
	public function testStandard(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/'] );

		$this->assertEquals( preg_match( '/'.preg_quote('standard - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );

		// 不正なテーマ名を付与して実行した場合、選択は無効になり、オプションなしの実行と同じ結果が返ってくるはず。
		$output_invalid = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?THEME=invalid//theme'] );
		$this->assertEquals( $output, $output_invalid );

		// /layout_test1.html を実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/layout_test1.html'] );
		$this->assertEquals( preg_match( '/'.preg_quote('standard - test1.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_test1.html</p>', '/').'/', $output ), 1 );

		// /layout_kflow.html を実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/layout_kflow.html'] );
		$this->assertEquals( preg_match( '/'.preg_quote('standard - kflow.html.kflow', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_kflow.html</p>', '/').'/', $output ), 1 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );
	}

	/**
	 * theme "standard" publish
	 */
	public function testStandardPublish(){
		$this->assertEquals( 1, 1 );

		// publish
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=publish.run'] );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );
	}

	/**
	 * theme "standard2"
	 */
	public function testStandard2(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?THEME=standard2'] );

		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('standard2 - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/index.html</p>', '/').'/', $output ), 1 );

		// /layout_test1.html を実行
		// ページ /layout_test1.html の layout列には、 test1 がセットされているが、
		// テーマ standard2 は レイアウト test1 を持っていないので、
		// default.html が採用されるのが正解。
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/layout_test1.html?THEME=standard2'] );
		$this->assertEquals( preg_match( '/'.preg_quote('standard2 - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_test1.html</p>', '/').'/', $output ), 1 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );

	}

	/**
	 * theme "standard3"
	 * 存在しないテーマを指定するテスト。デフォルトのテーマが適用されれば正解。
	 */
	public function testStandard3(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?THEME=undefined'] );

		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('standard - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/index.html</p>', '/').'/', $output ), 1 );

		// /layout_test1.html を実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/layout_test1.html?THEME=undefined'] );
		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('standard - test1.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_test1.html</p>', '/').'/', $output ), 1 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );

	}

	/**
	 * theme "not_exists"
	 * 存在しないテーマを読み込むテスト
	 */
	public function testNotExists(){

		// プロジェクト default_not_exists では、
		//
		// - data-contents-area-custom
		// - TEST_THEME_PARAM
		//
		// など、設定値が微妙に変更されています。

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/'] );

		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('HOME | Px2-MultiTheme - test - standard', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area-custom="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/index.html</p>', '/').'/', $output ), 1 );

		// /layout_test1.html を実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/layout_test1.html'] );
		// var_dump($output);
		$this->assertEquals( preg_match( '/'.preg_quote('TEST1 | Px2-MultiTheme - test - standard', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area-custom="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_test1.html</p>', '/').'/', $output ), 1 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/?PX=clearcache'] );

	}

	/**
	 * theme_collection_dir の設定がぜんぜん別の場所を指している場合のテスト
	 */
	public function testThemeCollectionDir(){

		// トップページを実行
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/?TEST_THEME_PARAM=standard2'] );

		$this->assertEquals( preg_match( '/'.preg_quote('standard2 - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span>FAILED</span>', '/').'/', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="contents" data-contents-area-custom="main">', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>これはコンテンツファイル。</p>', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/index.html</p>', '/').'/', $output ), 1 );

		// /layout_test1.html を実行
		// ページ /layout_test1.html の layout列には、 test1 がセットされているが、
		// テーマ standard2 は レイアウト test1 を持っていないので、
		// default.html が採用されるのが正解。
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/layout_test1.html?TEST_THEME_PARAM=standard2'] );
		$this->assertEquals( preg_match( '/'.preg_quote('standard2 - default.html', '/').'/', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>/layout_test1.html</p>', '/').'/', $output ), 1 );

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/default_not_exists/.px_execute.php' , '/?PX=clearcache'] );
	}

	/**
	 * 後始末
	 */
	public function testFinal(){

		// 後始末
		$output = $this->passthru( ['php', __DIR__.'/testdata/standard/.px_execute.php' , '/?PX=clearcache'] );

		$this->assertTrue( true );

	}




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(180); // Windowsのtestがタイム・アウトするため追加
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
	}

}
