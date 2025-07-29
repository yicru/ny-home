<?php
//$page_dataに投稿に関するデータが格納されている
//ページ情報
$page_meta = [
    //url canonical
    'canonical' => $blog_info['home-url'],
    //title 文字列
    'title' => $blog_info['name'],
    'description' => '',
    'og' => [
        'type' => 'website',
        'locale' => 'ja_JP',
    ],
    'twitter' => [
        //twitter user名
        'site' => '',
    ]
];
//現在のインデントの個数
$indent_level = 0;

//GETパラメータに"debug"を追加している場合圧縮しない
if( !isset($_GET["debug"]) ){
    //バッファリングスタート
    ob_start();
} else {
    echo "<script>console.log('debugが有効です、HTMLファイルを圧縮せずに出力しています。');</script>";
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head><?php //余計な改行、インデントを防ぐためのタグ開始位置

        //現在のインデントの個数
        $indent_level = 2;
        ?>

        <!-- head内のtitle, meta, icon等 -->
        <?php include( "parts/head.php" )?>
        <!-- end head内のtitle, meta、icon等 -->

        <!-- font -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <!-- TODO:使用フォントの設定 -->
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet" />

        <!-- css共通 -->
        <link rel="stylesheet" href="css/style-top.css">

        <!-- css-ページ用<?php //<link rel="stylesheet" href="css/index.css">?> -->
    </head>
    <body class="bl_spArea"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>

        <div class="bl_spArea_inner"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>

            <?php
            //ヘッダー
            include( "parts/bl_header/header.php" )?>

            <main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
                //現在のインデントの個数
                $indent_level = 4;?>

                <?php
                //キービジュアル
                include( "parts/bl_kv/kv.php" )?>

                <?php //CTA
                include( "parts/bl_cta/top-kvBottom-cta.php" )?>

                <?php //お悩みセクション
                include( "parts/bl_worries/worries.php" )?>

                <?php //future
                include( "parts/bl_future/future.php" )?>

                <?php //case study 記事っぽい見た目は別に記事ではないっぽいけど、上のスクロールの部分を押したら切り替わりそう 
                include( "parts/bl_case/case.php" )?>

                <?php //flow
                include( "parts/bl_flow/flow.php" )?>

                <?php //faq
                include( "parts/bl_faq/faq.php" )?>

                <?php //company
                include( "parts/bl_company/company.php" )?>

            </main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>

            <?php //footer
            include( "parts/bl_footer/footer.php" )?>
        </div><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 2;?>

        <!-- TODO:footerしたのリンクを設定 -->
        <!-- TODO:divでナビゲーション(pc) -->
        <!-- TODO:divでナビゲーション(pc) -->
    </body>
</html>
<?php
//GETパラメータに"debug"を追加している場合圧縮しない
//強制オフ
if(0){
    if( isset($_GET["debug"]) ){
        //バッファ終了、改行余計な空白削除して出力
        $compress = ob_get_clean();
        $compress = str_replace("\t", '', $compress);
        $compress = str_replace("\r", '', $compress);
        $compress = str_replace("\n", '', $compress);
        $compress = preg_replace('/<!--[\s\S]*?-->/', '', $compress);
        echo $compress;
    }
}
?>