<?php
//現在のインデントの個数
$indent_level = 0;
//記事idから記事データを取得する、このファイルをincludeしている時点でpは存在していて、該当ページもあるはずなのである前提で進める。
$post_data = get_post_data( $_GET['p'] );
//配列に直す
$post_data = json_decode(json_encode($post_data), true);
//$page_dataに投稿に関するデータが格納されている
//ページ情報
$page_meta = [
    //url canonical
    'canonical' => $blog_info['site-url'].$blog_info["site_meta"]["top_url"].'?p='.array_key_empty_check( $post_data, "id" ),
    //title 文字列
    'title' => $blog_info['name'],
    'description' => array_key_empty_check( $post_data, "post_excerpt" ),
    'og' => [
        'title' => array_key_empty_check( $post_data, "post_title" ),
        'type' => 'article',
        'image' => array_key_empty_check( $post_data, "post_thumbnail" ),
        'description' => array_key_empty_check( $post_data, "post_excerpt" ),
        'locale' => 'ja_JP',
    ],
    'twitter' => [
        'title' => array_key_empty_check( $post_data, "post_title" ),
        'card' => 'summary',
        //twitter user名
        'site' => '',
        'image' => array_key_empty_check( $post_data, "post_thumbnail" ),
        'description' => array_key_empty_check( $post_data, "post_excerpt" ),
    ]
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

            <div class="ly_single bl_single"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
                //現在のインデントの個数
                $indent_level = 4;

                $cat = get_the_category( array_key_empty_check( $post_data, "id" ) );
                //カテゴリーが取得できなかった場合は未分類にする
                if( $cat == false ) {
                    $cat  = [
                        "id" => "unset",
                        "name" => "未分類",
                        "slug" => "unset",
                        "date" => "",
                        "description" => "カテゴリーが未分類の投稿です。",
                        "parent" => false,
                        "parent_id" => "",
                        "child" => false
                    ];
                }
                //カテゴリーが複数ある場合はネストしているので、直接idを参照できるかどうかで判定する
                //カテゴリーが1つの場合はそのまま
                if ( !isset( $cat["id"] ) ) {
                    //カテゴリーが複数の場合最初の一つだけを表示する
                    $cat = $cat[0];
                }

                //出力するデータに加工
                //カテゴリーの色クラスを決定する
                $cat_class = 'el_postCat__gray';
                switch ( $cat["id"] ) {
                    case '1':
                        //html/css
                        $cat_class = "el_postCat__gold";
                        break;

                    case '2':
                        $cat_class = "el_postCat__gray";
                        break;

                    case '3':
                        $cat_class = "el_postCat__green";
                        break;

                    case '4':
                        $cat_class = "el_postCat__blue";
                        break;

                    case '5':
                        $cat_class = "el_postCat__pink";
                        break;

                    case '6':
                        $cat_class = "el_postCat__orange";
                        break;

                    default:
                        $cat_class = "el_postCat__gray";
                        break;
                }
                $current_post = [
                    "title" => array_key_empty_check( $post_data, "post_title" ),
                    //slugには非対応
                    "link" => $blog_info["site_meta"]["top_url"].'?p='.array_key_empty_check( $post_data, "id" ),
                    "img" => array_key_empty_check( $post_data, "post_thumbnail" ),
                    "cat_class" => $cat_class,
                    "cat_name" => $cat["name"],
                    "cat_link" => $blog_info["site_meta"]["top_url"].'?cat='.$cat["id"],
                    "date" => str_replace( '-','/',explode(' ', array_key_empty_check( $post_data, "post_published_date" ))[0]),
                    "datetime" => explode(' ', array_key_empty_check( $post_data, "post_published_date" ))[0],
                ];
                ?>

                <div class="bl_single_info">
                    <a class="el_postCat <?php echo $current_post["cat_class"]?>" href="<?php echo $blog_info['site_meta']['top_url']?>?cat=<?php echo $cat["id"];?>"><?php echo $current_post["cat_name"]?></a>
                    <time datetime="<?php echo $current_post["datetime"]?>" class="el_postDate hp_fw500"><?php echo $current_post["date"]?></time><?//更新日は出力しない、こことオススメ記事のtimeだけpc版font-weight:regular ?>

                </div>
                <h1 class="bl_single_ttl"><?php echo $current_post["title"]?></h1><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
                if( !empty($current_post['img']) ){
                    //サムネイルがあれば表示する
                    ?>

                <img src="<?php echo $current_post['img']?>" class="bl_single_thum" alt="<?php echo $current_post["title"]?>" width="1300" height="630"><?php
                }?>

                <div class="bl_single_cont">
                    <?php echo do_shortcode($post_data['post_content'])?>

                </div>
            </div><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
                //現在のインデントの個数
                $indent_level = 3;?>

        </main><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>

        <!-- 同カテゴリー記事のレコメンド -->
        <?php
            $recommend_cat_id = $cat["id"];
            $current_post_id = $post_data["id"];
            include( "parts/single-recommend.php" );
        ?>
        <!-- end 同カテゴリー記事のレコメンド -->


        <?php
        //ページ下部のバナー表示設定がオフなら表示する
        if( !isset( $post_data["post_meta"]["bnr-display"] ) ) {
        ?><!-- ページ下部バナー -->
        <?php include( "parts/bnr.php" )?>
        <!-- end ページ下部バナー --><?php
        } elseif( $post_data["post_meta"]["bnr-display"] != "none" ) {
            ?><!-- ページ下部バナー --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>
        <?php include( "parts/bnr.php" )?>
        <!-- end ページ下部バナー --><?php
        }
        ?>

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