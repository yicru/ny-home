<?php
//現在のインデントの個数
$indent_level = 0;

//$page_dataに投稿に関するデータが格納されている
$page_data = [
    "is_home" => false,
    "is_single" =>  false,
    "is_page" =>  false,
    "is_404" =>  true
];
//ページ情報はなし
$page_meta = [
    //url canonical
    'canonical' => '',
    //title 文字列
    'title' => 'お探しのページは見つかりませんでした。',
    'description' => '',
    'og' => [
        'type' => 'website',
        'locale' => 'ja_JP',
    ],
    'twitter' => [
        'card' => 'summary',
        //twitter user名
        'site' => '',
    ],
];


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
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet" />

        <!-- css共通<?php //<link rel="stylesheet" href="css/reset.css">?> -->
        <link rel="stylesheet" href="css/style.css">

        <!-- css-ページ用<?php //<link rel="stylesheet" href="css/index.css">?> -->
    </head>
    <body><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>

        <!-- header -->
        <?php include( "parts/header.php" )?>
        <!-- end header -->

        <main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>
            <div class="bl_pageTtl_wrapper">
                <h1 class="bl_pageTtl bl_pageTtl__center">404 エラー</h1>
            </div>
            <div class="ly_page bl_page">
                <div class="bl_page_cont">
                    <p class="bl_error_txt">
                        <span class="bl_error_span">申し訳ございません。お探しのページは見つかりませんでした。</span>
                        <span class="bl_error_span">入力したアドレスが間違っているか、ページが移動・削除された可能性があります。</span>
                    </p>
                    <div class="hp_db hp_tac">
                        <a class="el_btn el_btn__black" href="<?php echo $blog_info['site_meta']['top_url']?>">トップへ</a>
                    </div>
                </div>
            </div>
        </main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>


        <!-- ページ下部バナー -->
        <?php include( "parts/bnr.php" )?>
        <!-- end ページ下部バナー -->


        <!-- footer -->
        <?php include( "parts/footer.php" )?>
        <!-- end footer -->
    </body>
</html>
<?php
//GETパラメータに"debug"を追加している場合圧縮しない
if( isset($_GET["debug"]) ){
    //バッファ終了、改行余計な空白削除して出力
    $compress = ob_get_clean();
    $compress = str_replace("\t", '', $compress);
    $compress = str_replace("\r", '', $compress);
    $compress = str_replace("\n", '', $compress);
    $compress = preg_replace('/<!--[\s\S]*?-->/', '', $compress);
    echo $compress;
}
?>