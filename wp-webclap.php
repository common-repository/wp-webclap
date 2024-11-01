<?php
/*
  Plugin Name: WP WebClap
  Version: 0.1.8
  Plugin URI: http://transrain.net/blog/2007/07/12/135906
  Author: Yuki Kisaragi
  Author URI: http://trainsrain.net/
  Description: this plugin can "web clap" button setup in blog.
  Text Domain: wp-webclap
  Domain Path: /languages
 */

// デバッグ用
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
// ルートディレクトリ
define('WP_WEBCLAP_DIR_ROOT', dirname(__FILE__));
// ルートファイル
define('WP_WEBCLAP_FILE_ROOT', __FILE__);
// クラス読み込み
wp_webclap_register_autoload();

// アクティベート/デアクティベート時の処理
register_activation_hook(__FILE__, 'wp_webclap_activate');
register_deactivation_hook(__FILE__, 'wp_webclap_deactivate');

// クラスの実行
$wp_webclap = Wp_WebClap_Core::getInstance();

/**
 * wp-webclapを有効化した時の処理
 */
function wp_webclap_activate()
{
    $wp_webclap = Wp_WebClap_Core::getInstance();
    $wp_webclap->install();
}

/**
 * wp-webclapを無効化した時の処理
 */
function wp_webclap_deactivate()
{
    $wp_webclap = Wp_WebClap_Core::getInstance();
    $wp_webclap->uninstall();
}

/**
 * wp-webclapのクラス自動読み込みを登録する
 */
function wp_webclap_register_autoload()
{
    $path = explode(PATH_SEPARATOR, get_include_path());
    $root = dirname(__FILE__);
    if (false === array_search($root, $path)) {
        array_unshift($path, $root);
    }
    set_include_path(implode(PATH_SEPARATOR, $path));
    spl_autoload_register('wp_webclap_autoload');
}

/**
 * wp-webclapのクラスを自動読み込みする
 * @param string $className クラス名
 */
function wp_webclap_autoload($className)
{
    if (strncmp('Wp_WebClap_', $className, 11) === 0) {
        $classPath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require_once $classPath;
    }
}

/**
 * テンプレートタグ showWebClap
 *
 * パラメータを指定するとその文字で表示される。
 */
function showWebClap($value = null)
{
    $wp_webclap = Wp_WebClap_Core::getInstance();
    $wp_webclap->tagShowWebClap($value);
}

/**
 * テンプレートタグ show_webclap
 *
 * パラメータを指定するとその文字で表示される。
 */
function show_webclap($value = null)
{
    $wp_webclap = Wp_WebClap_Core::getInstance();
    $wp_webclap->tagShowWebClap($value);
}
