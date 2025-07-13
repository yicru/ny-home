<?php
//カテゴリーのタイトルを設定する
$cat_name = '未分類';
if(isset($_GET['cat'])){
    $cat = category_exists($_GET['cat']);
    if( $cat != false) {
        $cat_name = $cat["name"];
    }
}

//$page_dataに投稿に関するデータが格納されている
//ページ情報
$page_meta = [
    //url canonical
    'canonical' => $blog_info['site-url'].$blog_info['site_meta']['top_url'],
    //title 文字列
    'title' => '『'.$cat_name.'』の記事一覧',
    'description' => '『'.$cat_name.'』の記事一覧ページです',
    'og' => [
        'type' => 'website',
        'locale' => 'ja_JP',
    ],
    'twitter' => [
        'card' => 'summary',
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
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet" />

        <!-- css共通<?php //<link rel="stylesheet" href="css/reset.css">?> -->
        <link rel="stylesheet" href="css/style.css">

        <!-- css-ページ用<?php //<link rel="stylesheet" href="css/index.css">?> -->
    </head>
    <body><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>

        <!-- header --><?php //ヘッダーはsingleと同じものを呼び出す ?>
        <?php include( "parts/single-header.php" )?>
        <!-- end header -->

        <main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;
            ?>
            <div class="bl_pageTtl_wrapper">
                <h1 class="bl_pageTtl">『<?php echo $cat_name?>』の記事一覧</h1>
            </div>

            <!-- 記事一覧 -->
            <?php include( "parts/cat-loop.php" )?>
            <!-- end 記事一覧 -->
        </main>

        <!-- ページ下部バナー --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>
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