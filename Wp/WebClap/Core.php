<?php
/**
 * Web拍手プラグイン 基本クラス
 */
class Wp_WebClap_Core extends Wp_WebClap_Base
{
    /**
     * 自身のインスタンスを保持する
     * @var Wp_WebClap_Core
     */
    private static $s_instance;

    /**
     * wp-webclap本体のインスタンスを取得する。
     * @return Wp_WebClap_Core 本体のインスタンス
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
        // アクション等へのハンドリングを登録する
        $this->initializeHandler();
    }

    /**
     * ハンドラを初期化する
     */
    private function initializeHandler()
    {
        $data = Wp_WebClap_Data::getInstance();

        // 管理メニュー追加
        add_action('admin_menu', array($this, 'handlerAdminOptions'));
        // 投稿項目追加
        add_action('edit_form_advanced', array($this, 'handlerPostOptions'));
        // データ保存処理追加
        add_action('save_post', array($data, 'saveRecord'));
        // ダッシュボード追加
        add_action('wp_dashboard_setup', array($this, 'handlerDashboardSetup'));
        // 受信用フォーム(ショートコード)の表示
        add_shortcode('webclap', array($this, 'handlerReciveForm'));
        // jQueryのロード
        add_action('wp_enqueue_scripts', array($this, 'loadScripts'));
        // 自動ボタン追加
        if ($data->getDispType() === Wp_WebClap_Data::CLAP_DISP_AUTO) {
            add_filter('the_content', array($this, 'tagShowWebClap'), 9999);
        }
    }

    public function handlerReciveForm($atts)
    {
        $display = '';
        $d = Wp_WebClap_Data::getInstance();

        mb_detect_order('UTF-8,SJIS,EUC-JP,JIS,ASCII');

        $count = (isset($_POST['count'])) ? intval($_POST['count']) : 1;

        if (isset($_POST['wcname'])) {
            $enc = mb_detect_encoding($_POST['wcname']);
            if ('UTF-8' !== $enc) {
                $name = mb_convert_encoding($_POST['wcname'], 'UTF-8', $enc);
            } else {
                $name = $_POST['wcname'];
            }
        } else {
            $name = '';
        }

        if (isset($_POST['comment'])) {
            $enc = mb_detect_encoding($_POST['comment']);
            if ('UTF-8' !== $enc) {
                $comment = mb_convert_encoding($_POST['comment'], 'UTF-8', $enc);
            } else {
                $comment = $_POST['comment'];
            }
        } else {
            $comment = '';
        }

        $page = 0;
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
        } elseif (isset($_GET['page'])) {
            $page = intval($_GET['page']);
        } else {
            // エラー
            return 'パラメータエラー';
        }

        if ($count < 10) {
            // 連続拍手可能回数制限
            $data = array(
                'page_id'               => $page,
                'name'                  => $name,
                'comment'               => $comment,
                'count'                 => $count + 1,
                'enableNameRequired'    => false,
                'enableName'            => false,
                'disableName'           => false,
                'isButtonUpper'         => false,
                'isButtonLower'         => false,
            );

            // 表示ウィンドウ設定
            if ($d->getOpenType() === Wp_WebClap_Data::CLAP_OPEN_OTHER) {
                $data['isOtherWindow'] = true;
            } else {
                $data['isOtherWindow'] = false;
            }

            if ($d->getEnableName() === Wp_WebClap_Data::CLAP_ENABLENAME_REQUIRE) {
                $data['enableNameRequired'] = true;
            } else if ($d->getEnableName() === Wp_WebClap_Data::CLAP_ENABLENAME_POSSIBLE) {
                $data['enableName'] = true;
            } else {
                $data['disableName'] = true;
            }

            if ($d->getButtonPlace() === Wp_WebClap_Data::CLAP_BUTTONPLACE_UPPER) {
                $data['isButtonUpper'] = true;
            } else if ($d->getButtonPlace() === Wp_WebClap_Data::CLAP_BUTTONPLACE_LOWER) {
                $data['isButtonLower'] = true;
            } else {
                $data['isButtonUpper'] = true;
                $data['isButtonLower'] = true;
            }

            // 名前が必須でも入力されていない場合
            if ($count > 1 && empty($data['name']) && !empty($data['comment']) && $d->getEnableName() === Wp_WebClap_Data::CLAP_ENABLENAME_REQUIRE) {
                $data['enableNameRequired'] = true;
                $data['count']--; // カウントを戻す
                $data['isErrorName'] = true;
                // コメント/再拍手フォーム
                $display = $this->render('process_success', $data);
            } else {
                $data['isErrorName'] = false;
                // コメント/再拍手フォーム
                $display = $this->render('process_success', $data);
                $d->addClapData($data);
            }
        } else {
            // 最大数突破
            $display = $this->render('process_error', array());
        }
        return $display;
    }

    public function handlerAdminOptions()
    {
        $adminopt = Wp_WebClap_Administration::getInstance();
    }

    public function handlerPostOptions()
    {
        $postoption = Wp_WebClap_Post::getInstance();
    }

    /**
     * ダッシュボード: ウィジェット登録
     */
    public function handlerDashboardSetup()
    {
        $dashboard = Wp_WebClap_Dashboard::getInstance();
    }

    /**
     * テンプレートタグ showWebClas 処理部分
     *
     * パラメータを指定するとその文字で表示される。
     */
    function tagShowWebClap($value = null)
    {
        $data = Wp_WebClap_Data::getInstance();
        $tag = Wp_WebClap_Tags::getInstance();
        if ($data->getDispType() === Wp_WebClap_Data::CLAP_DISP_AUTO) {
            return $value . $tag->show(null);
        } else {
            return $tag->show($value);
        }
    }

    public function loadScripts()
    {
        wp_enqueue_script('jquery');
    }

    public function install()
    {

    }

    public function uninstall()
    {

    }

}