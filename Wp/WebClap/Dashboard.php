<?php
/**
 * ダッシュボードに表示するウィジェット処理を行うクラス
 */
class Wp_WebClap_Dashboard extends Wp_WebClap_Base
{
    /**
     * ダッシュボードウィジェットの識別ID
     * @var string
     */
    const DASHBOARD_ID = 'wp-webclap-bashboard';

    /**
     * 自身のインスタンスを保持する
     * @var Wp_WebClap_Dashboard
     */
    private static $s_instance;

    /**
     * ダッシュボードウィジェットのインスタンスを取得する
     * @return Wp_WebClap_Dashboard ダッシュボードウィジェットのインスタンス
     */
    public static function getInstance()
    {
        if (empty(self::$s_instance)) {
            self::$s_instance = new self();
        }
        return self::$s_instance;
    }

    /**
     * ダッシュボードウィジェットを初期化する
     */
    protected function classInitialize()
    {
        $d = Wp_WebClap_Data::getInstance();
        if ($d->getClapType() === Wp_WebClap_Data::CLAP_TYPE_BUILTIN) {
            wp_add_dashboard_widget(
                    self::DASHBOARD_ID, $this->__('Web Clap'),
                                                  array($this, 'handlerDashboardDisplay'),
                                                  array($this, 'handlerDashboardOptions')
            );
            add_action('admin_print_scripts', array($this, 'scriptRegister'));
        }
        
        if(!get_option(self::DASHBOARD_ID)) {
            update_option(self::DASHBOARD_ID, array(
                'wpwc_graph_number'     => 7,
                'wpwc_top_number'       => 5,
                'wpwc_comments_number'  => 5,
            ));
        }
    }

    /**
     * Google APIのスクリプト追加
     */
    public function scriptRegister()
    {
        wp_register_script('gjsapi', 'https://www.google.com/jsapi');
        wp_enqueue_script('gjsapi');
    }

    /**
     * ダッシュボードに表示する内容
     */
    public function handlerDashboardDisplay()
    {
        $options = get_option(self::DASHBOARD_ID);
        $data = array();
        $d = Wp_WebClap_Data::getInstance();

        // データ取得
        $graph = $d->getDailyGraphData();
        $data['rank'] = $d->getEntryRankData();
        $data['comment'] = $d->getEntryCommentData();

        // グラフデータ調整
        $line = '';
        foreach ($graph as $day => $row) {
            $day = explode('-', $day);
            array_shift($day);
            $day = implode('/', $day);
            
            if ($line !== '') $line .= ",\n";
            $line .= sprintf("    ['%s', %d, %d, %d]", $day, $row['human'], $row['count'],
                             $row['comment']);
        }
        $data['graph'] = '[' . $line . ']';

        echo $this->render('dashboard', $data);
    }

    /**
     * ダッシュボードの設定時に表示する内容
     */
    function handlerDashboardOptions()
    {
        // 設定値情報
        $options = array();
        $keys = array(
            'wpwc_graph_number',
            'wpwc_top_number',
            'wpwc_comments_number',
        );

        // データ取得
        $get = get_option(self::DASHBOARD_ID);
        foreach ($keys as $key) {
            if (!isset($get[$key])) {
                switch ($key) {
                    case 'wpwc_graph_number':
                        $value = 7;
                        break;
                    default:
                        $value = 5;
                }
            } else {
                $value = $get[$key];
            }
            $options[$key] = $value;
        }

        // データ更新
        if (
                'post' == strtolower($_SERVER['REQUEST_METHOD']) &&
                isset($_POST['widget_id']) &&
                self::DASHBOARD_ID == $_POST['widget_id']
        ) {
            foreach ($keys as $key) {
                $value = '';
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];
                }
                $options[$key] = $value;
            }
            update_option(self::DASHBOARD_ID, $options);
        }

        // 画面表示
        echo $this->render('dashboard_options', $options);
    }

}