<?php
class Wp_WebClap_Administration extends Wp_WebClap_Base
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
        $d = Wp_WebClap_Data::getInstance();

        if ($d->getClapType() === Wp_WebClap_Data::CLAP_TYPE_BUILTIN) {
            $callback1 = array($this, 'displayAnalysisSubPanel');
            add_submenu_page(
                 'index.php'
                ,$this->__('Analysis for Webclap')
                ,$this->__('Analysis for Webclap')
                ,$d->getAnalysisRole()
                ,'webclap-analysis'
                ,$callback1
            );
        }

        $callback2 = array($this, 'displayOptionsSubPanel');
        add_submenu_page(
                 'options-general.php'
                ,$this->__('configuration for web clap')
                ,$this->__('configuration for web clap')
                ,'manage_options'
                ,'webclap-config'
                ,$callback2
        );
    }

    public function displayAnalysisSubPanel()
    {
        // 値の設定
        $values = array();
        $d = Wp_WebClap_Data::getInstance();

        // パラメータチェック
        $date = isset($_GET['date']) ? $_GET['date'] : date_i18n('Y-m-d');
        $ranking = isset($_GET['ranking']) ? $_GET['ranking'] : false;

        $values['pos_next'] = date_i18n('Y-m-d', strtotime($date) + (86400 * 14));
        $values['pos_prev'] = date_i18n('Y-m-d', strtotime($date) - (86400 * 14));

        if (strtotime($values['pos_next']) >= strtotime(date_i18n('Y-m-d'))) {
            if (strtotime($date) >= strtotime(date_i18n('Y-m-d'))) {
                $values['pos_next'] = '';
            } else {
                $values['pos_next'] = date_i18n('Y-m-d');
            }
        }

        // アイコンURL
        $values['icon_clap'] = plugins_url('assets/clap.png', WP_WEBCLAP_FILE_ROOT);
        $values['icon_human'] = plugins_url('assets/head.png', WP_WEBCLAP_FILE_ROOT);
        $values['icon_comment'] = plugins_url('assets/comment.png', WP_WEBCLAP_FILE_ROOT);

        // 日付タイトル
        $values['date'] = date_i18n('Y/m/d', strtotime($date));
        $values['rawdate'] = $date;

        // 複数日カウント
        $start = date_i18n('Y-m-d', strtotime($date) - 86400 * 13);
        $end = date_i18n('Y-m-d', strtotime($date));


        if ($ranking) {
            // ランキング取得
            $values['is_ranking'] = true;
            $values['rank'] = $d->getEntryRankData(true);
        } else {
            $values['is_ranking'] = false;
            // 解析情報取得
            $week = $d->getDailyGraphData($start, $end);
            $nm = 0;
            $nc = 0;
            $nd = array(
                'count' => array(),
                'human' => array(),
                'comment' => array(),
                'date' => array(),
                'linkdate' => array(),
            );

            foreach ($week as $day => $value) {
                if ($nm < $value['count']) $nm = $value['count'];

                $nd['count'][] = $value['count'];
                $nd['human'][] = $value['human'];
                $nd['comment'][] = $value['comment'];
                $nd['date'][] = date_i18n('m/d', strtotime($day));
                $nd['linkdate'][] = $day;
                $nd['graph'][] = sprintf('t:%d|%d', $value['count'] - $value['human'], $value['human']);
                $nc++;
            }


            $values['week_count'] = $nc;
            $values['week_max'] = $nm;
            $values['week'] = $nd;

            // 日別解析（カウント）
            list($values['day'], $values['max']) = $d->getAnalysisDayData($date);

            // 日別解析（コメント）
            $values['comments'] = $d->getAnalysisCommentData($date);

            // コメントに名前を表示する
            if ($d->getEnableName() !== Wp_WebClap_Data::CLAP_ENABLENAME_IMPOSSIBLE) {
                $values['isCommentName'] = true;
            } else {
                $values['isCommentName'] = false;
            }
        }

        // 画面表示
        echo $this->render('admin_analysis', $values);
    }

    /**
     * 設定画面を表示する
     */
    public function displayOptionsSubPanel()
    {
        // 値の設定
        $values = array();
        $values['updatemessage'] = '';

        $d = Wp_WebClap_Data::getInstance();

        if (isset($_POST['clear'])) {
            // データ削除
            $query = file_get_contents(WP_WEBCLAP_DIR_ROOT . '/query/clear_entry_count.sql');

            global $wpdb;
            $wpdb->query($query);

            $values['updatemessage'] = $this->__('cleared webclap count.');
        } else if (isset($_POST['submit'])) {
            // 更新時
            $d->setClapType($this->getPost('claptype', Wp_WebClap_Data::CLAP_TYPE_BUILTIN));
            $d->setAnalysisRole($this->getPost('analysisrole', 'manage_options'));
            $d->setProcessPage($this->getPost('processpage'));
            $d->setDispType($this->getPost('disptype', Wp_WebClap_Data::CLAP_DISP_MANUAL));
            $d->setViewType($this->getPost('viewtype', Wp_WebClap_Data::CLAP_VIEW_BUTTON));
            $d->setOpenType($this->getPost('opentype', Wp_WebClap_Data::CLAP_OPEN_OTHER));
            $d->setViewFormat($this->getPost('viewformat'));
            $d->setEnabled($this->getPost('enabled'));
            $d->setDefaultText($this->getPost('default', $this->__('web clap button')));
            $d->setEnableName($this->getPost('enableName', Wp_WebClap_Data::CLAP_ENABLENAME_IMPOSSIBLE));
            $d->setButtonPlace($this->getPost('buttonPlace', Wp_WebClap_Data::CLAP_BUTTONPLACE_BOTH));
            $d->setButtonCountEnable($this->getPost('appendclapcount', Wp_WebClap_Data::CLAP_BUTTONCOUNT_DISABLE));
            $d->setAccount($this->getPost('account'));

            if (
                    (isset($_FILES['imageslink']) && !empty($_FILES['imageslink']['name']))
                    || (isset($_FILES['imageshover']) && !empty($_FILES['imageshover']['name']))
            ) {
                // 画像がアップロードされた場合
                $diropt = wp_upload_dir('/');
                // ディレクトリ取得
                $updir = realpath($diropt['path']);
                // URL正規化
                $url = $diropt['url'];
                $ul = parse_url($url);
                $url = sprintf('%s://%s', $ul['scheme'], $ul['host']);
                if (isset($ul['port'])) $url .= ':' . $ul['port'];
                $ulp = explode('/', $ul['path']);
                $stack = array();
                foreach ($ulp as $p) {
                    if (trim($p) === '') continue;

                    if (trim($p) === '..') {
                        array_pop($stack);
                    } else {
                        $stack[] = $p;
                    }
                }
                $url .= '/' . implode('/', $stack);

                // hoverのみの場合はlinkとして扱う
                if (!isset($_FILES['imageslink'])) {
                    $_FILES['imageslink'] = $_FILES['imageshover'];
                    unset($_FILES['imageshover']);
                }

                // ファイルチェック
                $imgs = array('link' => '', 'hover' => '');
                if (isset($_FILES['imageslink'])) {
                    $f = $_FILES['imageslink'];
                    $tmp = $f['tmp_name'];
                    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                    $fil = sprintf('%s/clap_l.%s', $updir, $ext);
                    $ful = sprintf('%s/clap_l.%s', $url, $ext);

                    if (is_uploaded_file($tmp)) {
                        move_uploaded_file($tmp, $fil);
                        $imgs['link'] = $ful;
                    }
                }
                if (isset($_FILES['imageshover'])) {
                    $f = $_FILES['imageshover'];
                    $tmp = $f['tmp_name'];
                    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                    $fil = sprintf('%s/clap_h.%s', $updir, $ext);
                    $ful = sprintf('%s/clap_h.%s', $url, $ext);

                    if (is_uploaded_file($tmp)) {
                        move_uploaded_file($tmp, $fil);
                        $imgs['hover'] = $ful;
                    }
                }

                $d->setViewImage($imgs);
            }
            $d->save();
            $values['updatemessage'] = $this->__('updated webclap information.');
        }

        // 拍手タイプ
        switch (intval($d->getClapType())) {
            case Wp_WebClap_Data::CLAP_TYPE_BUILTIN:
                $values['claptype_builtin'] = ' selected="selected"';
                $values['claptype_external'] = '';
                break;
            default:
                $values['claptype_builtin'] = '';
                $values['claptype_external'] = ' selected="selected"';
        }

        // 解析ページ権限
        $values['analysis_administrator'] = '';
        $values['analysis_editor']        = '';
        $values['analysis_author']        = '';
        $values['analysis_contributor']   = '';
        $values['analysis_subscriber']    = '';
        switch ($d->getAnalysisRole()) {
            case 'publish_pages':
                $values['analysis_editor']        = ' selected="selected"';
                break;
            case 'publish_posts':
                $values['analysis_author']        = ' selected="selected"';
                break;
            case 'edit_posts':
                $values['analysis_contributor']   = ' selected="selected"';
                break;
            case 'read':
                $values['analysis_subscriber']    = ' selected="selected"';
                break;
            default:
                $values['analysis_administrator'] = ' selected="selected"';
                break;
        }

        // 表示モード
        switch (intval($d->getDispType())) {
            case Wp_WebClap_Data::CLAP_DISP_MANUAL:
                $values['disptype_manually'] = ' selected="selected"';
                $values['disptype_automatic'] = '';
                break;
            default:
                $values['disptype_manually'] = '';
                $values['disptype_automatic'] = ' selected="selected"';
        }

        // 表示タイプ
        switch (intval($d->getViewType())) {
            case Wp_WebClap_Data::CLAP_VIEW_LINK:
                $values['viewtype_button'] = '';
                $values['viewtype_link'] = ' selected="selected"';
                $values['viewtype_image'] = '';
                $values['viewtype_custom'] = '';
                break;
            case Wp_WebClap_Data::CLAP_VIEW_IMAGE:
                $values['viewtype_button'] = '';
                $values['viewtype_link'] = '';
                $values['viewtype_image'] = ' selected="selected"';
                $values['viewtype_custom'] = '';
                break;
            case Wp_WebClap_Data::CLAP_VIEW_CUSTOMIZE:
                $values['viewtype_button'] = '';
                $values['viewtype_link'] = '';
                $values['viewtype_image'] = '';
                $values['viewtype_custom'] = ' selected="selected"';

                break;
            default:
                $values['viewtype_button'] = ' selected="selected"';
                $values['viewtype_link'] = '';
                $values['viewtype_image'] = '';
                $values['viewtype_custom'] = '';
        }

        // 表示方法タイプ
        switch (intval($d->getOpenType())) {
            case Wp_WebClap_Data::CLAP_OPEN_OTHER:
                $values['opentype_other'] = ' selected="selected"';
                $values['opentype_self'] = '';
                break;
            default:
                $values['opentype_other'] = '';
                $values['opentype_self'] = ' selected="selected"';
        }
        $values['processpage'] = $d->getProcessPage();
        $values['viewformat'] = $d->getViewFormat();

        // ボタン有効
        $values['enabled'] = ($d->getEnabled()) ? ' checked="checked"' : '';

        // デフォルトボタンテキスト
        $values['default'] = $d->getDefaultText();

        // ボタン画像
        $images = $d->getViewImage();
        $values['imglink'] = $images['link'];
        $values['imghover'] = $images['hover'];

        // 名前入力タイプ
        $values['enablename_require'] = '';
        $values['enablename_possible'] = '';
        $values['enablename_impossible'] = '';
        switch (intval($d->getEnableName())) {
            case Wp_WebClap_Data::CLAP_ENABLENAME_REQUIRE:
                $values['enablename_require'] = ' selected="selected"';
                break;
            case Wp_WebClap_Data::CLAP_ENABLENAME_POSSIBLE:
                $values['enablename_possible'] = ' selected="selected"';
                break;
            default:
                $values['enablename_impossible'] = ' selected="selected"';
        }

        // ボタン表示位置
        $values['buttonplace_both'] = '';
        $values['buttonplace_upper'] = '';
        $values['buttonplace_lower'] = '';
        switch (intval($d->getButtonPlace())) {
            case Wp_WebClap_Data::CLAP_BUTTONPLACE_UPPER:
                $values['buttonplace_upper'] = ' selected="selected"';
                break;
            case Wp_WebClap_Data::CLAP_BUTTONPLACE_LOWER:
                $values['buttonplace_lower'] = ' selected="selected"';
                break;
            default:
                $values['buttonplace_both'] = ' selected="selected"';
        }

        // カウント表示可否
        $values['clapcount_enable'] = '';
        $values['clapcount_disable'] = '';
        switch (intval($d->getButtonCountEnable())) {
            case Wp_WebClap_Data::CLAP_BUTTONCOUNT_ENABLE:
                $values['clapcount_enable'] = ' selected="selected"';
                break;
            default:
                $values['clapcount_disable'] = ' selected="selected"';
        }

        // アカウント名
        $values['account'] = $d->getAccount();

        // テンプレート読み込み
        echo $this->render('admin_options', $values);
    }

    /**
     * POSTリクエストの値を取得する
     * @param string $name パラメータ名
     * @param string $default パラメータ未設定時の値
     * @return string リクエスト値
     */
    private function getPost($name, $default = '')
    {
        if (empty($_POST[$name])) {
            return $default;
        }
        return $_POST[$name];
    }

}
