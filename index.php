<?php
/*ページ概要
1ページのみなのでindexで完結
pc版のスタイルは
https://marukawa-reform.com/
こんな感じのデザインになる

トップページだけなので細かくいじるのはhome.phpのみ

cssはscssでページごとに出し分け

*/
//このサイトのメタデータ等を読み込む
include( 'data/site-data.php' );
//共通関数
include( 'functions.php');
//使用するインデント
$indent = $blog_info['site_meta']['indent'];
//現在のインデントの個数
$indent_level = 0;

//slug、パーマリンク設定には非対応
//todo slug対応
//p id 45はトップページ、pidが設定されていないか、idが45だったらトップページとして扱う
$page_id = 45;
//トップページのデータを仮で入れておく
$page_data = get_post_data($page_id);
//配列に直す
$page_data = json_decode( json_encode($page_data), true);
//slugは非対応
//archiveよりもpage idのほうが優先される
if ( isset($_GET['p']) ) {
    //pは数値idである必要があるので数値かどうかを見る
    if( is_numeric($_GET['p']) ){
        //ページを取得
        $page_data = get_post_data( $_GET['p'] );
        //配列に直す
        $page_data = json_decode( json_encode($page_data), true);
        //取得できなかったら404へ飛ばす
        if ( $page_data != false ) {
            //取得できていればページごとにテンプレートを切り分ける
            if ( $page_data["is_home"] ) {
                include( "template/home.php" );
            } elseif ( $page_data["is_single"] ) {
                include( "template/single.php" );
            } elseif ( $page_data["is_page"] ) {
                include( "template/page.php" );
            } else {
                //カスタムポストタイプ
                include( "template/single.php" );
            }
        } else {
            //存在しないページなので404に飛ばす
            include( "template/404.php" );
        }
    } else {
        //存在しないページなので404に飛ばす
        include( "template/404.php" );
    }
} else {
    //archiveか否かを判断
    if ( isset($_GET['cat']) ) {
        //カテゴリーが存在するかどうかを確認
        if( category_exists( $_GET['cat'] ) != false ) {
            //存在しているのでカテゴリーページへ
            include( "template/category.php" );
        } elseif( $_GET['cat'] == 'unset' ) {
            //未分類一覧を表示する
            include( "template/category.php" );
        } else {
            //存在しないページなので404に飛ばす
            include( "template/404.php" );
        }
    } else {
        //カテゴリーでもなくて、idが設定されていなければ基本はトップページとして扱う
        include( "template/home.php" );
    }
}