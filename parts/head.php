<?php
//$head_meta, $page_metaが読み込まされている必要があります。
if ( !isset( $head_meta ) ) {
    echo '<!-- 警告:$head_metaが読み込まれていません(head.php) -->'."\n";
}
if ( !isset( $page_meta ) ) {
    echo '<!-- 警告:$page_metaが読み込まれていません(head.php) -->'."\n";
}

//head内で使うmetaタグの出力
//nameもしくはcontentの中身を空白で設定している場合falseを返し、両方存在する場合はtagオブジェクトを返します。
function head_metaTag_generator( $meta_comment = '', $meta_name = '', $meta_content = ''){
    //tagオブジェクト
    $tag = [];
    //nameがあるかチェック
    if ( $meta_name == '') {
        //nameが空なら出力しない
        return false;
    } else {
        //contentがあるかチェック
        if ( $meta_content == '') {
            //contentが空なら出力しない
            return false;
        } else {
            //コメントがある場合は$tagに含む
            if ( $meta_comment != '') {
                $tag ['comment'] = $meta_comment;
            }
            //metaタグを$tagに含む
            $tag ['tag'] = '<meta name="'.$meta_name.'" content="'.$meta_content.'">';
            return $tag;
        } //end contentがあるかチェック
    } //end nameがあるかチェック
} //end head内で使うmetaタグの出力

//タグ配列にタグを格納するための関数です。
//タグオブジェクトが代入されない、もしくはfalseが代入されるとタグ配列をそのまま返します。
function add_tag($tag = false, $tags = []){
    //タグオブジェクトが存在する場合格納する
    if ( $tag != false ) {
        $tags []= $tag;
    }
    return $tags;
}

//headで出力されるタグです、フォーマットや出力のオンオフを切り替えるために一度配列に格納しています。
//格納された順番でそのまま出力されます
$head_tags = [
    //[
        //'comment' => 'コメント(任意)',
        //'tag' => 'タグ配列(タグを文字列で格納したもの)、もしくは任意の文字列を入力するとそのまま出力されます(タグそのものでも大丈夫です)'
    //]
];

//charsetを作成して追加
$head_tags []= [
    'tag' => [
        '<meta charset="utf-8">',
        '<title>'.$page_meta['title'].'</title>'
    ]
];
//descriptionを作成して追加
$head_tags = add_tag( head_metaTag_generator( '', 'description', $blog_info['description']), $head_tags );

//viewportを作成して追加
$head_tags []= [
    'comment' => 'viewport user-scalable y',
    'tag' => '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">',
];

//referrerを作成して追加
$head_tags []= [
    'comment' => 'referrer cross origin',
    'tag' => '<meta name="referrer" content="strict-origin-when-cross-origin">',
];

//tel linkを作成して追加
$head_tags []= [
    'comment' => 'no tel link',
    'tag' => '<meta name="format-detection" content="telephone=no">',
];

//theme colorを作成して追加
$head_tags = add_tag( head_metaTag_generator( 'theme color', 'theme-color', $blog_info['theme-color']), $head_tags );

//canonicalタグを作成して追加
if ( !empty( $head_meta['canonical'] ) ) {//head metaのcanonicalが未設定の場合出力しない
    //canonicalタグのタグオブジェクトをhead metaの情報から作成し、tag配列に追加
    $head_tags []= [
        'comment' => 'canonical',
        'tag' => '<link rel="canonical" href="'.$head_meta['canonical'].'">',
    ];
}// end canonicalタグの出力処理

if( $page_data["is_404"] ) {
    //robotsを作成して追加
    $head_tags = add_tag( head_metaTag_generator( 'robots', 'robots', 'noindex nofollow'), $head_tags );
} else {
//robotsを作成して追加
    $head_tags = add_tag( head_metaTag_generator( 'robots', 'robots', $blog_info['robots']), $head_tags );
}


//keywordsを作成して追加
$head_tags = add_tag( head_metaTag_generator( 'keywords', 'keywords', $blog_info['keywords']), $head_tags );

//faviconを作成して追加
$tags = [];

//faviconがあるかチェック
if ( !empty($blog_info['favicon']) ) {
    //$tagsにfavicon追加
    $tags []= '<meta name="msapplication-TileImage" content="'.$blog_info['favicon'].'">';
}

//iconがあるかチェック
if ( !empty($blog_info['icon']) ) {
    //icon-sizesがあればsizesを追加して$tagに追加、なければそのまま追加
    if ( !empty($blog_info['icon-sizes']) ) {
        $tags []= '<link rel="icon" href="'.$blog_info['icon'].'" sizes="'.$blog_info['icon-sizes'].'">';
    } else {
        $tags []= '<link rel="icon" href="'.$blog_info['icon'].'">';
    }
}

//apple-touch-iconがあるかチェック
if ( !empty($blog_info['apple-touch-icon']) ) {
    //$tagsにapple-touch-icon追加
    $tags []= '<link rel="apple-touch-icon" href="'.$blog_info['apple-touch-icon'].'">';
}

//tagが空じゃなければ追加
if ( !empty( $tags ) ) {
    $head_tags []= [
        'comment' => 'favicon',
        'tag' => $tags,
    ];
}

//og系のタグ
$tags = [];
//そもそもogのデータがあるか
if ( !empty( $head_meta['og'] ) ) {
    //site nameが空もしくは未設定であればblog infoを参照する
    if ( !empty( $head_meta['og']['site_name'] ) ) {
        $tags []= '<meta property="og:site_name" content="'.$head_meta['og']['site_name'].'">';
    } elseif ( !empty( $head_meta['og']['site_name'] )) {
        $tags []= '<meta property="og:site_name" content="'.$head_meta['og']['site_name'].'">';
    }

    //urlはcanonicalはそのページのURLが入力される
    if ( !empty( $page_meta['canonical'] )) {
        $tags []= '<meta property="og:url" content="'.$page_meta['canonical'].'">';
    }

    //type、page meta og type > head meta og typeの優先順位で適用されます。
    if ( !empty( $page_meta['og']['type'] )) {
        $tags []= '<meta property="og:type" content="'.$page_meta['og']['type'].'">';
    } elseif ( !empty( $head_meta['og']['type'] )) {
        $tags []= '<meta property="og:type" content="'.$head_meta['og']['type'].'">';
    }

    //title、page meta og > page meta title > head meta og titleの優先順位で適用されます。
    if ( !empty( $page_meta['og']['title'] )) {
        $tags []= '<meta property="og:title" content="'.$page_meta['og']['title'].'">';
    } elseif ( !empty( $page_meta['title'] )) {
        $tags []= '<meta property="og:title" content="'.$page_meta['title'].'">';
    } elseif ( !empty( $head_meta['og']['title'] )) {
        $tags []= '<meta property="og:title" content="'.$head_meta['og']['title'].'">';
    }

    //description、page meta og description > page meta description > head meta og descriptionの優先順位で適用されます。
    if ( !empty( $page_meta['og']['description'] )) {
        $tags []= '<meta property="og:description" content="'.$page_meta['og']['description'].'">';
    } elseif ( !empty( $page_meta['description'] )) {
        $tags []= '<meta property="og:description" content="'.$page_meta['description'].'">';
    } elseif ( !empty( $head_meta['og']['description'] )) {
        $tags []= '<meta property="og:description" content="'.$head_meta['og']['description'].'">';
    }

    //imageはpage meta og image > page meta thumbnailの優先順位で適用されます。
    if ( !empty( $page_meta['og']['image'] )) {
        $tags []= '<meta property="og:image" content="'.$page_meta['og']['image'].'">';
    } elseif ( !empty( $page_meta['thumbnail'] )) {
        $tags []= '<meta property="og:image" content="'.$page_meta['thumbnail'].'">';
    } elseif ( !empty($blog_info['og:image']) ) {
        $tags []= '<meta property="og:image" content="'.$blog_info['og:image'].'">';
    }

    //localeはhead metaがある場合のみ適用されます
    if ( !empty( $head_meta['locale'] )) {
        $tags []= '<meta property="og:locale" content="'.$head_meta['locale'].'">';
    }

    //tagが空じゃなければ追加
    if ( !empty( $tags ) ) {
        $head_tags []= [
            'comment' => 'og',
            'tag' => $tags,
        ];
    }
}

//twitter系のタグ
$tags = [];
//そもそもogのデータがあるか
if ( !empty( $head_meta['twitter'] ) ) {

    //cardはpage meta twitter card > head meta twitter cardの優先順位で適用されます。
    if ( !empty( $page_meta['twitter']['card'] )) {
        $tags []= '<meta property="og:card" content="'.$page_meta['twitter']['card'].'">';
    } elseif ( !empty( $head_meta['twitter']['card'] )) {
        $tags []= '<meta name="twitter:card" content="'.$head_meta['twitter']['card'].'">';
    }

    //imageはpage meta twitter image > page meta twitter thumbnailの優先順位で適用されます。
    if ( !empty( $page_meta['twitter']['image'] )) {
        $tags []= '<meta property="og:image" content="'.$page_meta['twitter']['image'].'">';
    } elseif ( !empty( $page_meta['thumbnail'] )) {
        $tags []= '<meta property="og:image" content="'.$page_meta['thumbnail'].'">';
    }

    //description、page meta twitter description > page meta description > head meta twitter descriptionの優先順位で適用されます。
    if ( !empty( $page_meta['twitter']['description'] )) {
        $tags []= '<meta name="twitter:description" content="'.$page_meta['twitter']['description'].'">';
    } elseif ( !empty( $page_meta['description'] )) {
        $tags []= '<meta name="twitter:description" content="'.$page_meta['description'].'">';
    } elseif ( !empty( $head_meta['twitter']['description'] )) {
        $tags []= '<meta name="twitter:description" content="'.$head_meta['twitter']['description'].'">';
    }

    //site、page meta twitter site > head meta twitter siteの優先順位で適用されます。
    if ( !empty( $page_meta['twitter']['site'] )) {
        $tags []= '<meta name="twitter:site" content="'.$page_meta['twitter']['site'].'">';
    } elseif ( !empty( $head_meta['twitter']['site'] )) {
        $tags []= '<meta name="twitter:site" content="'.$head_meta['twitter']['site'].'">';
    }

    //title、page meta twitter title > page meta title > head meta twitter title > head meta og titleの優先順位で適用されます。
    if ( !empty( $page_meta['twitter']['title'] )) {
        $tags []= '<meta property="twitter:title" content="'.$page_meta['twitter']['title'].'">';
    } elseif ( !empty( $page_meta['title'] )) {
        $tags []= '<meta property="twitter:title" content="'.$page_meta['title'].'">';
    } elseif( !empty( $head_meta['twitter']['title'] )) {
        $tags []= '<meta property="twitter:title" content="'.$head_meta['twitter']['title'].'">';
    } elseif ( !empty( $head_meta['og']['title'] )) {
        $tags []= '<meta property="twitter:title" content="'.$head_meta['og']['title'].'">';
    }

    //tagが空じゃなければ追加
    if ( !empty( $tags ) ) {
        $head_tags []= [
            'comment' => 'X',
            'tag' => $tags,
        ];
    }
}
//挿入されるインデント
$current_indent = '        ';
//インデントの情報があれば上書きする
if ( isset( $indent ) && isset( $indent_level ) ) {
    //事前設定されていなければ追加する
    $current_indent = '';
    for ( $i = $indent_level ; $i > 0 ; $i--) {
        $current_indent .= $indent;
    }
}
//最初の一回のループ目か判断するための変数
$first = true;

//タグを出力
foreach ( $head_tags as $head_tag ) {
    //コメントを出力する
    if ( !empty( $head_tag['comment'] ) ) {
        //最初の1行だった場合インデントを付けない
        if ( $first ) {
            echo '<!-- '.$head_tag['comment'].' -->'."\n";
            $first = false;
        } else {
            echo $current_indent.'<!-- '.$head_tag['comment'].' -->'."\n";
        }
    }
    //タグを出力する
    if ( !empty( $head_tag['tag'] ) ) {
        //タグが配列になってるかどうかを判定する
        if( is_array( $head_tag['tag'] ) ) {
            //タグを全部出力
            foreach ( $head_tag['tag'] as $tag ) {
                //最初の1行だった場合インデントを付けない
                if ( $first ) {
                    echo $tag."\n";
                    $first = false;
                } else {
                    echo $current_indent.$tag."\n";
                }
            }

        } else {
            //最初の1行だった場合インデントを付けない
            if ( $first ) {
                echo $head_tag['tag']."\n";
                $first = false;
            } else {
                echo $current_indent.$head_tag['tag']."\n";
            }
        }
        //最後のタグを出力したあとに改行を入れる
        echo "\n";
    }
}
?>