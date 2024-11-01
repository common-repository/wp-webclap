<?php
class Wp_WebClap_Tags extends Wp_WebClap_Base
{
    /**
     * 自身のインスタンスを保持する
     * @var Wp_WebClap_Tags
     */
    private static $s_instance;

    /**
     * データクラスのインスタンスを取得する。
     * @return Wp_WebClap_Tags
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

    }

    /**
     * タグを表示する
     * @global type $post
     * @global type $wpdb
     * @param type $value
     * @return type
     */
    public function show($value = null)
    {
        global $post;
        global $wpdb;

        $d = Wp_WebClap_Data::getInstance();
        $r = $d->getRecord();

        // 処理ページの場合はタグを表示しない
        if (get_page_uri($post->ID) === $d->getProcessPage()) return;
        if ('?page_id='.$post->ID === $d->getProcessPage()) return;

        // disableの場合はタグを表示しない
        if (!isset($r['enabled'])) return;
        if (intval($r['enabled']) === 0) return;

        $values = array();

        // ボタンタイトル
        if ($value === null || $d->getDispType() === Wp_WebClap_Data::CLAP_DISP_AUTO) {
            $values['title'] = $r['button_text'];
        } else {
            $values['title'] = $value;
        }

        // IDの設定
        $values['postid'] = $post->ID;

        // カスタム時のフォーマット設定
        $values['custom'] = $d->getViewFormat();

        // 画像ボタン時のパス設定
        $images = $d->getViewImage();
        $values['image_link'] = $images['link'];
        $values['image_hover'] = $images['hover'];

        // 処理エンジン
        if ($d->getClapType() === Wp_WebClap_Data::CLAP_TYPE_EXTERNAL) {
            // web clap plus

            $values['account'] = $d->getAccount();
            $values['pageid'] = $r['page_id'];
            $values['custom'] = str_replace('[account]', $values['account'], $values['custom']);
            $values['custom'] = str_replace('[value]', $values['title'], $values['custom']);
            $values['custom'] = str_replace('[page]', $values['pageid'], $values['custom']);

            $url = '';
            if (!empty($values['account'])) {
                $url .= ( empty($url)) ? '?' : '&';
                $url .= 'id=' . $values['account'];
            }
            if (!empty($values['pageid'])) {
                $url .= ( empty($url)) ? '?' : '&';
                $url .= 'page_id=' . $values['pageid'];
            }
            $values['clapurl'] = 'http://clap.webclap.com/clap.php' . $url;

            // テンプレート選択
            switch ($d->getViewType()) {
                case Wp_WebClap_Data::CLAP_VIEW_LINK:
                    $template = 'tag_external_text';
                    break;
                case Wp_WebClap_Data::CLAP_VIEW_IMAGE:
                    $template = 'tag_external_image';
                    break;
                case Wp_WebClap_Data::CLAP_VIEW_CUSTOMIZE:
                    $template = 'tag_custom';
                    break;
                default:
                    $template = 'tag_external_button';
            }
        } else {
            // built in
            $pid = $d->getProcessPage();
            if (preg_match('/^\?/', $pid)) {
                $clapurl = get_site_url() . '/' . $pid;
                $values['clapurl'] = esc_url($clapurl);
            } else {
                $clapurl = get_page_by_path($d->getProcessPage());
                $values['clapurl'] = esc_url(get_permalink($clapurl->ID));
            }
            $values['custom'] = str_replace('[value]', $values['title'], $values['custom']);
            $values['custom'] = str_replace('[page]', $values['postid'], $values['custom']);

            // テンプレート選択
            switch ($d->getViewType()) {
                case Wp_WebClap_Data::CLAP_VIEW_LINK:
                    $template = 'tag_internal_text';
                    break;
                case Wp_WebClap_Data::CLAP_VIEW_IMAGE:
                    $template = 'tag_internal_image';
                    break;
                case Wp_WebClap_Data::CLAP_VIEW_CUSTOMIZE:
                    $template = 'tag_custom';
                    break;
                default:
                    $template = 'tag_internal_button';
            }
        }

        // 表示ウィンドウ設定
        if ($d->getOpenType() === Wp_WebClap_Data::CLAP_OPEN_OTHER) {
            $values['isOtherWindow'] = true;
        } else {
            $values['isOtherWindow'] = false;
        }

        // ボタンカウント表示
        if ($d->getButtonCountEnable() === Wp_WebClap_Data::CLAP_BUTTONCOUNT_ENABLE) {
            $values['isButtonCount'] = true;
            $values['buttonCount'] = $d->getButtonCount($post->ID);
        } else {
            $values['isButtonCount'] = false;
        }

        echo $this->render($template, $values);
    }

}
