<?php
class Wp_WebClap_Post extends Wp_WebClap_Base
{
    /**
     * 自身のインスタンスを保持する
     * @var Wp_WebClap_Administration
     */
    private static $s_instance;

    /**
     * データクラスのインスタンスを取得する。
     * @return Wp_WebClap_Administration
     */
    public static function getInstance()
    {
        if (empty(self::$s_instance)) {
            self::$s_instance = new self();
        }
        return self::$s_instance;
    }

    /**
     * クラスの初期化を行う
     */
    protected function classInitialize()
    {
        global $post;
        global $wpdb;

        $d = Wp_WebClap_Data::getInstance();
        $values = $d->getRecord();

        // チェックボックス状態
        $values['enabled_check'] = '';
        if ($values['enabled']) {
            $values['enabled_check'] = ' checked="checked"';
        }


        // テンプレート読み込み
        echo $this->render('post_options', $values);
    }

}