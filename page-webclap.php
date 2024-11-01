<?php
/*
固定ページ用のテンプレート
page-<web拍手処理ページ>.phpの表示用のテンプレートです。

このファイルを使用しているテーマのディレクトリにコピーし、
好みの表示に修正して使用します。

処理ページの内容には「[webclap]」ショートタグを含めてください。
*/
?><!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
</head>
<body <?php body_class(); ?>>
    <div id="webclap"><?php the_post();the_content(); ?></div>
</body>
</html>