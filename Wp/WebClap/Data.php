<?php
/**
 * プラグインでデータを操作する処理をまとめたクラス
 */
class Wp_WebClap_Data extends Wp_WebClap_Base
{
    /**
     * オプションのID
     * @var string
     */
    const OPTION_DATA_ID = 'wp-webclap-options';

    /**
     * 拍手処理を内蔵エンジンで行う
     * @var integer
     */
    const CLAP_TYPE_BUILTIN = 0;

    /**
     * 拍手処理を外部で行う
     * @var integer
     */
    const CLAP_TYPE_EXTERNAL = 1;

    /**
     * ボタンの表示場所を自分で指定する
     * @var integer
     */
    const CLAP_DISP_MANUAL = 0;

    /**
     * ボタンの表示場所をコンテンツの最後に自動表示する
     * @var integer
     */
    const CLAP_DISP_AUTO = 1;

    /**
     * 拍手ボタンをボタンで表示する
     * @var integer
     */
    const CLAP_VIEW_BUTTON = 0;

    /**
     * 拍手ボタンをテキストリンクで表示する
     * @var integer
     */
    const CLAP_VIEW_LINK = 1;

    /**
     * 拍手ボタンを画像リンクで表示する
     * @var integer
     */
    const CLAP_VIEW_IMAGE = 2;

    /**
     * 拍手ボタンをカスタマイズ表示する
     * @var integer
     */
    const CLAP_VIEW_CUSTOMIZE = 9;

    /**
     * 拍手画面を開くときに別ウィンドウで表示する
     * @var interger
     */
    const CLAP_OPEN_OTHER = 0;

    /**
     * 拍手画面を開くときに同じウィンドウで表示する
     * @var integer
     */
    const CLAP_OPEN_SELF = 1;

    /**
     * 名前入力を必須入力にする
     * @var integer
     */
    const CLAP_ENABLENAME_REQUIRE = 2;

    /**
     * 名前入力を入力可能にする
     * @var integer
     */
    const CLAP_ENABLENAME_POSSIBLE = 1;

    /**
     * 名前入力を入力不可にする
     * @var integer
     */
    const CLAP_ENABLENAME_IMPOSSIBLE = 0;

    /**
     * ボタン表示位置を上下両方にする
     * @var integer
     */
    const CLAP_BUTTONPLACE_BOTH = 0;

    /**
     * ボタン表示位置を上部にする
     * @var integer
     */
    const CLAP_BUTTONPLACE_UPPER = 1;

    /**
     * ボタン表示位置を下部にする
     * @var integer
     */
    const CLAP_BUTTONPLACE_LOWER = 2;

    /**
     * 拍手ボタンにカウント表示を行う
     * @var integer
     */
    const CLAP_BUTTONCOUNT_ENABLE = 1;

    /**
     * 拍手ボタンにカウント表示を行なわない
     * @var integer
     */
    const CLAP_BUTTONCOUNT_DISABLE = 0;

    /**
     * 自身のインスタンスを保持する
     * @var Wp_WebClap_Data
     */
    private static $s_instance;
    /**
     * プラグインのオプション値を保持する
     * @var array
     */
    private $m_options;
    /**
     * 使用するメインテーブル名を保持する
     * @var string
     */
    private $m_table_link;
    /**
     * 使用するコメントテーブル名を保持する
     * @var string
     */
    private $m_table_comment;

    /**
     * データクラスのインスタンスを取得する。
     * @return Wp_WebClap_Data
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
     * @global type $wpdb
     */
    protected function classInitialize()
    {
        global $wpdb;

        // プロパティの初期化
        $this->m_table_link = $wpdb->prefix . 'webclap';
        $this->m_table_comment = $wpdb->prefix . 'webclap_comments';

        // 設定情報の初期化
        $this->initializePluginData();

        // データベースの確認
        $this->checkDatabase();
    }

    /**
     * プラグインのデータを初期化する。
     */
    private function initializePluginData()
    {
        // プラグインデータの初期化
        $changed = false;
        $this->m_options = get_option(self::OPTION_DATA_ID);
        if (!$this->m_options) {
            $this->m_options = array();
            $changed = true;
        }

        if (!isset($this->m_options['clap_type'])) {
            $this->m_options['clap_type'] = self::CLAP_TYPE_BUILTIN;
            $changed = true;
        }
        if (!isset($this->m_options['analysis_role'])) {
            $this->m_options['analysis_role'] = 'manage_options';
            $changed = true;
        }
        if (!isset($this->m_options['account'])) {
            $this->m_options['account'] = '';
            $changed = true;
        }
        if (!isset($this->m_options['default'])) {
            $this->m_options['default'] = $this->__('web clap button');
            $changed = true;
        }
        if (!isset($this->m_options['enabled'])) {
            $this->m_options['enabled'] = true;
            $changed = true;
        }
        if (!isset($this->m_options['view_type'])) {
            $this->m_options['view_type'] = self::CLAP_VIEW_BUTTON;
            $changed = true;
        }
        if (!isset($this->m_options['open_type'])) {
            $this->m_options['open_type'] = self::CLAP_OPEN_OTHER;
            $changed = true;
        }
        if (!isset($this->m_options['process_page'])) {
            $this->m_options['process_page'] = '';
            $changed = true;
        }
        if (!isset($this->m_options['view_format'])) {
            $this->m_options['view_format'] = '';
            $changed = true;
        }
        if (!isset($this->m_options['view_image'])) {
            $this->m_options['view_image'] = array('link' => '', 'hover' => '');
            $changed = true;
        }
        if (!isset($this->m_options['disp_type'])) {
            $this->m_options['disp_type'] = self::CLAP_DISP_MANUAL;
            $changed = true;
        }
        if (!isset($this->m_options['enable_name'])) {
            $this->m_options['enable_name'] = self::CLAP_ENABLENAME_IMPOSSIBLE;
            $changed = true;
        }
        if (!isset($this->m_options['button_place'])) {
            $this->m_options['button_place'] = self::CLAP_BUTTONPLACE_BOTH;
            $changed = true;
        }
        if (!isset($this->m_options['enable_buttoncount'])) {
            $this->m_options['enable_buttoncount'] = self::CLAP_BUTTONCOUNT_DISABLE;
            $changed = true;
        }

        if ($changed) $this->save();
    }

    /**
     * データベースのレイアウトを確認する
     * @global type $wpdb
     */
    private function checkDatabase()
    {
        global $wpdb;
        $upgrade = ABSPATH . 'wp-admin/includes/upgrade.php';

        // メインテーブルの構築
        $q1 = sprintf('show tables like "%s"', $this->m_table_link);
        $q2 = sprintf('show columns from %s where Field = "page_id"', $this->m_table_link);
        $q = file_get_contents(WP_WEBCLAP_DIR_ROOT . '/query/create_main.sql');
        $dlt1 = sprintf($q, $this->m_table_link);
        if ($wpdb->get_var($q1) != $this->m_table_link || $wpdb->get_var($q2) === null) {
            require_once($upgrade);
            dbDelta($dlt1);
        }

        // コメントテーブルの構築
        $c1 = sprintf('show tables like "%s"', $this->m_table_comment);
        $c2 = sprintf('show columns from %s where Field = "clap_name"', $this->m_table_comment);
        $q = file_get_contents(WP_WEBCLAP_DIR_ROOT . '/query/create_comment.sql');
        $dlt2 = sprintf($q, $this->m_table_comment);
        if ($wpdb->get_var($c1) != $this->m_table_comment || $wpdb->get_var($c2) === null) {
            require_once($upgrade);
            dbDelta($dlt2);
        }
    }

    /**
     * オプション値を保存する
     */
    public function save()
    {
        update_option(self::OPTION_DATA_ID, $this->m_options);
    }

    /**
     * 拍手タイプを取得する
     * @return integer 拍手タイプ(CLAP_TYPE_?)
     */
    public function getClapType()
    {
        if (!isset($this->m_options['clap_type'])) {
            $this->setClapType(self::CLAP_TYPE_BUILTIN);
        }
        return intval($this->m_options['clap_type']);
    }

    /**
     * 拍手タイプを設定する
     * @param integer $value 拍手タイプ(CLAP_TYPE_?)
     */
    public function setClapType($value)
    {
        $this->m_options['clap_type'] = intval($value);
    }

    /**
     * 解析画面の表示可能権限を取得する
     * @return string 権限名
     */
    public function getAnalysisRole()
    {
        if (!isset($this->m_options['analysis_role'])) {
            $this->setAnalysisRole('manage_options');
        }
        return $this->m_options['analysis_role'];
    }

    /**
     * 解析画面の表示可能権限を設定する
     * @param string $value 権限名
     */
    public function setAnalysisRole($value)
    {
        $this->m_options['analysis_role'] = $value;
    }

    /**
     * 処理用固定ページ名の取得
     * @return string 処理用固定ページ名
     */
    public function getProcessPage()
    {
        if (!isset($this->m_options['process_page'])) {
            $this->setProcessPage('');
        }
        return $this->m_options['process_page'];
    }

    /**
     * 処理用固定ページ名の設定
     * @param string $value 固定用ページ名
     */
    public function setProcessPage($value)
    {
        $this->m_options['process_page'] = $value;
    }

    /**
     * 表示方法を取得する
     * @return integer 表示方法(CLAP_DISP_?)
     */
    public function getDispType()
    {
        if (!isset($this->m_options['disp_type'])) {
            $this->setDispType(self::CLAP_DISP_MANUAL);
        }
        return intval($this->m_options['disp_type']);
    }

    /**
     * 表示方法を設定する
     * @param integer $value 表示方法(CLAP_DISP_?)
     */
    public function setDispType($value)
    {
        $this->m_options['disp_type'] = intval($value);
    }

    /**
     * 外部エンジン時のアカウント名を取得する
     * @return string アカウント名
     */
    public function getAccount()
    {
        if (!isset($this->m_options['account'])) {
            $this->setAccount('');
        }
        return $this->m_options['account'];
    }

    /**
     * 外部エンジン時のアカウント名を設定する
     * @param string $value アカウント名
     */
    public function setAccount($value)
    {
        $this->m_options['account'] = $value;
    }

    /**
     * デフォルトボタンテキストを取得する
     * @return string デフォルトボタンテキスト
     */
    public function getDefaultText()
    {
        if (!isset($this->m_options['default'])) {
            $this->setDefault($this->__('web clap button'));
        }
        return $this->m_options['default'];
    }

    /**
     * デフォルトボタンテキストを設定する
     * @param string $value デフォルトボタンテキスト
     */
    public function setDefaultText($value)
    {
        $this->m_options['default'] = $value;
    }

    /**
     * ボタンが有効かどうかを取得する
     * @return boolean ボタン有効無効
     */
    public function getEnabled()
    {
        if (!isset($this->m_options['enabled'])) {
            $this->setEnabled(true);
        }
        return $this->m_options['enabled'];
    }

    /**
     * ボタンが有効かどうかを設定する
     * @param boolean $value ボタン有効無効
     */
    public function setEnabled($value)
    {
        $this->m_options['enabled'] = $value;
    }

    /**
     * ボタンの表示タイプを取得する
     * @return integer 表示タイプ(CLAP_VIEW_?)
     */
    public function getViewType()
    {
        if (!isset($this->m_options['view_type'])) {
            $this->setViewType(self::CLAP_VIEW_BUTTON);
        }
        return intval($this->m_options['view_type']);
    }

    /**
     * ボタンの表示タイプを設定する
     * @param integer $value 表示タイプ(CLAP_VIEW_?)
     */
    public function setViewType($value)
    {
        $this->m_options['view_type'] = intval($value);
    }

    public function getOpenType()
    {
        if (!isset($this->m_options['open_type'])) {
            $this->setViewType(self::CLAP_OPEN_OTHER);
        }
        return intval($this->m_options['open_type']);
    }

    public function setOpenType($value)
    {
        $this->m_options['open_type'] = intval($value);
    }

    /**
     * 表示フォーマットの取得
     * @return string 表示フォーマット
     */
    public function getViewFormat()
    {
        if (!isset($this->m_options['view_format'])) {
            $this->setViewFormat('');
        }
        return $this->m_options['view_format'];
    }

    /**
     * 表示フォーマットの設定
     * @param string $value 表示フォーマット
     */
    public function setViewFormat($value)
    {
        $this->m_options['view_format'] = $value;
    }

    /**
     * ボタンの画像ファイルを取得する
     * @return array 画像ファイルパス(link,hover)
     */
    public function getViewImage()
    {
        if (!isset($this->m_options['view_image'])) {
            $this->setViewImage(array('link' => '', 'hover' => ''));
        }
        return $this->m_options['view_image'];
    }

    /**
     * ボタンの画像ファイルを設定する
     * @param array $value 画像ファイルパス(link,hover)
     */
    public function setViewImage(array $value)
    {
        $this->m_options['view_image'] = $value;
    }

    /**
     * 名前入力設定を取得する
     * @return integer 入力方式(CLAP_ENABLENAME_?)
     */
    public function getEnableName()
    {
        if (!isset($this->m_options['enable_name'])) {
            $this->setViewImage(self::CLAP_ENABLENAME_IMPOSSIBLE);
        }
        return $this->m_options['enable_name'];
    }

    /**
     * 名前入力設定を設定する
     * @param integer 入力方式(CLAP_ENABLENAME_?)
     */
    public function setEnableName($value)
    {
        $this->m_options['enable_name'] = intval($value);
    }




    /**
     * ボタン表示位置を取得する
     * @return integer ボタン表示位置(CLAP_BUTTONPLACE_?)
     */
    public function getButtonPlace()
    {
        if (!isset($this->m_options['button_place'])) {
            $this->setViewImage(self::CLAP_BUTTONPLACE_BOTH);
        }
        return $this->m_options['button_place'];
    }

    /**
     * ボタン表示位置を設定する
     * @param integer ボタン表示位置(CLAP_BUTTONPLACE_?)
     */
    public function setButtonPlace($value)
    {
        $this->m_options['button_place'] = intval($value);
    }

    /**
     * 拍手ボタンカウント表示可否を取得する
     * @var integer
     */
    public function getButtonCountEnable()
    {
        if (!isset($this->m_options['enable_buttoncount'])) {
            $this->setViewImage(self::CLAP_BUTTONCOUNT_DISABLE);
        }
        return $this->m_options['enable_buttoncount'];
    }

    /**
     * 拍手ボタンカウント表示可否を設置する
     * @param integer $value ボタンカウント表示可否(CLAP_BUTTONCOUNT_*)
     */
    public function setButtonCountEnable($value)
    {
        $this->m_options['enable_buttoncount'] = intval($value);
    }

    /**
     * 指定したパラメータでクエリを発行し、1行取得する。
     * @global type $wpdb
     * @param string $1 SQLファイル名(拡張子無し)
     * @param mixed $... SQLに代入する値(複数件)
     * @return stdClass クエリの結果（1行）
     */
    private function getRow()
    {
        global $wpdb;

        $args = func_get_args();
        $sqlfile = file_get_contents(WP_WEBCLAP_DIR_ROOT . '/query/' . array_shift($args) . '.sql');
        array_unshift($args, $sqlfile);

        $query = call_user_func_array('sprintf', $args);
        $result = $wpdb->get_row($query);

        return $result;
    }

    /**
     * 指定したパラメータでクエリを発行し、データを取得する。
     * @global type $wpdb
     * @param string $1 SQLファイル名(拡張子無し)
     * @param mixed $... SQLに代入する値(複数件)
     * @return stdClass クエリの結果
     */
    private function getRows()
    {
        global $wpdb;

        $args = func_get_args();
        $sqlfile = file_get_contents(WP_WEBCLAP_DIR_ROOT . '/query/' . array_shift($args) . '.sql');
        array_unshift($args, $sqlfile);

        $query = call_user_func_array('sprintf', $args);
        $result = $wpdb->get_results($query);

        return $result;
    }

    /**
     * 対象記事のボタンカウント数を取得する
     * @param integer $postid ページID
     * @return integer ボタンカウント数
     */
    public function getButtonCount($postid)
    {
        global $wpdb;

        $query = "SELECT COUNT(*) AS cnt FROM %s WHERE post_id = %d";
        $query = sprintf($query, $this->m_table_comment, $postid);
        $clapdata = $wpdb->get_row($query);
        if (empty($clapdata)) {
            return 0;
        } else {
            return intval($clapdata->cnt);
        }
    }

    /**
     * 日別グラフのデータを取得する
     * @global type $wpdb
     * @return string 日別グラフデータ
     */
    public function getDailyGraphData($start = null, $end = null)
    {
        global $wpdb;

        $dashboard = get_option(Wp_WebClap_Dashboard::DASHBOARD_ID);
        $interval = 86400 * ($dashboard['wpwc_graph_number'] - 1);

        if (is_null($start)) $start = date_i18n('Y-m-d', time() - $interval);
        if (is_null($end)) $end = date_i18n('Y-m-d');

        // 日別人数
        $hc = $this->getRows('get_dairy_human', $this->m_table_comment, $start, $end);

        // 日別回数
        $cc = $this->getRows(
                        'get_dairy_count', $this->m_table_comment, $start, $end);

        // 日別コメント数
        $mc = $this->getRows(
                        'get_dairy_comment', $this->m_table_comment, $start, $end);

        $graph = array();

        foreach ($hc as $row) {
            $rowd = isset($graph[$row->clap_date]) ? $graph[$row->clap_date] : array();
            $rowd['human'] = intval($row->cnt);
            $graph[$row->clap_date] = $rowd;
        }
        foreach ($cc as $row) {
            $rowd = isset($graph[$row->clap_date]) ? $graph[$row->clap_date] : array();
            $rowd['count'] = intval($row->cnt);
            $graph[$row->clap_date] = $rowd;
        }
        foreach ($mc as $row) {
            $rowd = isset($graph[$row->clap_date]) ? $graph[$row->clap_date] : array();
            $rowd['comment'] = intval($row->cnt);
            $graph[$row->clap_date] = $rowd;
        }

        // 対象日付の取得
        $days = array();
        $time_s = strtotime($start);
        $time_e = strtotime($end);
        for ($tm = $time_e; $tm >= $time_s; $tm = $tm - 86400) {
            $days[] = date_i18n('Y-m-d', $tm);
        }

        // データ無し部分の補完
        $result = array();
        foreach ($days as $day) {
            if (!isset($graph[$day])) {
                $result[$day] = array(
                    'human' => 0,
                    'count' => 0,
                    'comment' => 0,
                );
            } else {
                $result[$day] = $graph[$day];
            }
        }

        return $result;
    }

    /**
     * ランク順拍手データを取得する
     * @global type $wpdb
     * @return array 拍手データ配列
     */
    public function getEntryRankData($all = false)
    {
        global $wpdb;
        $dashboard = get_option(Wp_WebClap_Dashboard::DASHBOARD_ID);
        $result = array();

        // ランク取得
        if ($all) {
            $rank = $this->getRows('get_entry_base_all', $this->m_table_comment);
        } else {
            $rank = $this->getRows('get_entry_base', $this->m_table_comment, $dashboard['wpwc_top_number']);
        }

        $i = 0;
        foreach ($rank as $entry) {
            $i++;
            $row = array();

            $row['rank'] = $i;

            $row['page'] = intval($entry->post_id);
            $row['count'] = intval($entry->cnt);

            // 人数取得
            $hm = $this->getRow(
                            'get_entry_human', $this->m_table_comment, $entry->post_id);
            $row['human'] = intval($hm->cnt);

            // コメント数取得
            $cm = $this->getRow(
                            'get_entry_comment', $this->m_table_comment, $entry->post_id);
            $row['comment'] = intval($cm->cnt);

            // タイトル等取得
            $post = get_post($entry->post_id);
            if (!is_null($post)) {
                $row['title'] = $post->post_title;
                $row['url'] = get_permalink($entry->post_id);
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * 直近のコメントを取得する
     * @global type $wpdb
     * @return array コメントデータ
     */
    public function getEntryCommentData()
    {
        global $wpdb;
        $dashboard = get_option(Wp_WebClap_Dashboard::DASHBOARD_ID);
        $result = array();

        // コメント取得
        $cm = $this->getRows(
                        'get_comment_list', $this->m_table_comment,
                        $dashboard['wpwc_comments_number']);

        foreach ($cm as $row) {
            $result[] = array(
                'page' => intval($row->post_id),
                'comment' => $row->clap_comment,
            );
        }

        return $result;
    }

    public function getAnalysisDayData($date = null)
    {
        $rows = array();
        $hchk = array();

        if ($date === null) $date = date_i18n('Y-m-d');

        $buf = $this->getRows('get_analysis_day', $this->m_table_comment, $date);

        // データ整形
        foreach ($buf as $row) {
            if (!isset($rows[$row->clap_hour])) {
                $rows[$row->clap_hour] = array(
                    'count' => 0,
                    'comment' => 0,
                    'human' => 0,
                );
            }


            $rows[$row->clap_hour]['count']++;

            if (!empty($row->clap_comment)) $rows[$row->clap_hour]['comment']++;

            if (!isset($hchk[$row->clap_hour])) $hchk[$row->clap_hour] = array();
            if (!isset($hchk[$row->clap_hour][$row->ipaddress])) {
                $hchk[$row->clap_hour][$row->ipaddress] = '';
                $rows[$row->clap_hour]['human']++;
            }
        }

        // 出力データ作成
        $max = 0;
        $data = array();
        for ($i = 0; $i < 24; $i++) {
            if (!isset($rows[$i])) {
                $data[$i] = array(
                    'count' => 0,
                    'comment' => 0,
                    'human' => 0,
                );
            } else {
                $data[$i] = $rows[$i];
                if ($max < $rows[$i]['count']) $max = $rows[$i]['count'];
                if ($max < $rows[$i]['comment']) $max = $rows[$i]['comment'];
                if ($max < $rows[$i]['human']) $max = $rows[$i]['human'];
            }
        }
        return array($data, $max);
    }

    public function getAnalysisCommentData($date = null)
    {
        if ($date === null) $date = date_i18n('Y-m-d');
        $rows = $this->getRows('get_analysis_comment', $this->m_table_comment, $date);
        for ($i = 0; $i < count($rows); $i++) {
            $rows[$i]->post_title = '';
            $rows[$i]->post_url = '';

            $rows[$i]->clap_datetime = date('y/m/d H:i', strtotime($rows[$i]->clap_datetime));
            $post = get_post($rows[$i]->post_id);
            if (!is_null($post)) {
                $rows[$i]->post_title = $post->post_title;
                $rows[$i]->post_url = get_permalink($rows[$i]->post_id);
            }
        }
        return $rows;
    }

    /**
     * レコード情報を取得する
     * @access public
     * @global <type> $post
     * @global <type> $wpdb
     * @return array レコード配列
     */
    function getRecord()
    {
        global $post;
        global $wpdb;

        $clapdata = null;
        if (isset($post->ID)) {
            $id = $post->ID;
            $q = 'SELECT * FROM %s WHERE post_id = %d';
            $q = sprintf($q, $this->m_table_link, $id);
            $clapdata = $wpdb->get_row($q);
        } else {
            $id = -1;
        }

        $status = false;
        switch ($post->post_status) {
            case 'publish':
            case 'future':
                $status = true;
                break;
        }

        if ($clapdata === null || !$status) {
            // 値の設定
            $values = array(
                'post_id' => $id,
                'enabled' => $this->getEnabled(),
                'button_text' => $this->getDefaultText(),
                'page_id' => null,
            );
        } else {
            // 値の設定
            $values = array(
                'post_id' => $clapdata->post_id,
                'enabled' => $clapdata->enabled,
                'button_text' => $clapdata->button_text,
                'page_id' => $clapdata->page_id,
            );
        }

        return $values;
    }

    /**
     * データのアップデート
     */
    function saveRecord($post)
    {
        global $wpdb;

        // パラメータ初期化
        $id = null;
        $on = null;
        $vl = null;
        $pi = null;

        // パラメータ取得
        if (isset($_POST['ID'])) $id = $_POST['ID'];
        if ($id === null) $id = intval(wp_is_post_revision($post));

        // クエリ作成
        $sq = sprintf('SELECT * FROM %s WHERE post_id = %d', $this->m_table_link, $id);
        $clapdata = $wpdb->get_row($sq);

        if (!empty($clapdata)) {
            // データ存在時
            $on = $clapdata->enabled;
            $vl = $clapdata->button_text;
            $pi = $clapdata->page_id;
        }
        if (isset($_POST['clapon'])) $on = $_POST['clapon'];
        if (isset($_POST['clapvalue'])) $vl = $_POST['clapvalue'];
        if (isset($_POST['clappageid'])) $pi = $_POST['clappageid'];

        // 値設定
        if (empty($on)) $on = '0';
        if (empty($vl)) $vl = $this->getDefaultText();

        // クエリ作成
        // 登録/更新
        if (!is_null($clapdata)) {
            //update
            $q = "UPDATE %s SET enabled = %d, button_text = '%s', page_id = '%s' WHERE post_id = %d";
            $sql = $wpdb->prepare($q, $this->m_table_link, $on, $vl, $pi, $id);
        } else {
            //insert
            $q = "INSERT INTO %s (post_id, enabled, button_text, page_id) VALUES(%d,%d,'%s','%s')";
            $sql = $wpdb->prepare($q, $this->m_table_link, $id, $on, $vl, $pi);
        }
        $wpdb->query($sql);
        if ($id === null) {
            $id = $wpdb->get_var('SELECT LAST_INSERT_ID() as id');
        }

        return $id;
    }

    public function addClapData(array $data)
    {
        global $wpdb;
        $id = $data['page_id'];
        $data['ipaddress'] = $_SERVER['REMOTE_ADDR'];
        $data['date'] = date_i18n('Y-m-d');
        $data['datetime'] = date_i18n('Y-m-d H:i:s');

        $query = "INSERT INTO %s "
               . "(clap_id, clap_date, clap_datetime, post_id, ipaddress, clap_name, clap_comment) "
               . "VALUES (NULL, '%s', '%s', %d, '%s', '%s', '%s');";
        $query = sprintf($query, $this->m_table_comment, $data['date'], $data['datetime'],
                         $data['page_id'], $data['ipaddress'], $data['name'], $data['comment']
        );

        $wpdb->query($query);
        if ($id === null) {
            $id = $wpdb->get_var('SELECT LAST_INSERT_ID() as id');
        }
        return $id;
    }

}
