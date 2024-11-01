<?php
abstract class Wp_WebClap_Base
{
    /**
     * 翻訳用テキストドメイン
     * @var string
     */
    const TEXTDOMAIN = 'wp-webclap';

    /**
     * 翻訳ファイルディレクトリ名
     * @var string
     */
    const LANGDIR = 'languages';

    /**
     * 共通コンストラクタ
     */
    protected function __construct()
    {
        // テキストドメインの設定
        $lang = sprintf(
                '%s/%s/%s-%s.mo', WP_WEBCLAP_DIR_ROOT, self::LANGDIR, self::TEXTDOMAIN, get_locale()
        );
        load_textdomain(self::TEXTDOMAIN, $lang);

        // 初期処理の呼び出し
        $this->classInitialize();
    }

    /**
     * クラス初期化処理を行うメソッド定義
     */
    abstract protected function classInitialize();

    /**
     * 翻訳を行う
     * @param string $msg メッセージID
     * @return メッセージ
     */
    protected function __($msg)
    {
        return __($msg, self::TEXTDOMAIN);
    }

    /**
     * 翻訳を行い、表示する。
     * @param string $msg メッセージID
     */
    protected function _e($msg)
    {
        _e($msg, self::TEXTDOMAIN);
    }

    /**
     * テンプレートに値を渡してレンダリングを行う。
     * @param string $template テンプレート名
     * @param array $values 設定する値
     */
    protected function render($template, array $values = array())
    {
        $view = new Wp_WebClap_View();
        return $view->render($template, $values);
    }

}