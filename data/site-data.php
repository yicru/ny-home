<?php
//ユーザーの設定がされていない場合の表示データ
$user_default = [
    'name' => '名称未設定',
    'icon-url' => 'https://placehold.jp/3d4070/ffffff/500x500.png?text=icon',
    'icon-min-url' => 'https://placehold.jp/3d4070/ffffff/150x150.png?text=icon'
];
$user = [
    'admin'=>[
        'name' => '',
        'icon-url' => '',
        'icon-min-url' => ''
    ]
];
$blog_info = [
    //サイト名
    'name' => '<!-- TODO:サイト名設定 -->',
    //サイトタイトル(トップページのtitleタグに使用されます)
    'site-title' => '<!-- TODO:サイト名設定 -->',
    //デスクリプション
    'description' => '<!-- TODO:サイトの概要設定 -->',
    //サイトドメイン、URI(httpから入力)
    'site-url' => 'localhost/htdocs/github/',
    //トップページのurl(httpから入力)
    'home-url' => 'localhost/htdocs/github/',
    //keywords(,区切りでキーワード)
    'keywords' => '',
    //robots
    'robots' => 'noindex, nofollow',
    //管理者ユーザー、1人のみ
    'admin' => $user['admin'],
    //ファビコン(url)
    'favicon' => 'img/favicon.ico',
    //link rel iconのurl
    'icon' => 'img/favicon.ico',
    //link rel iconのサイズ
    'icon-sizes' => '128x128',
    //apple-touch-iconのurl
    'apple-touch-icon' => 'img/webclip.png',

    //サイトSNS
    'sns' => [
        'twitter' => [
            //表示するサービス名
            'name' => 'x(旧Twitter)',
            //snsのサービスアイコン
            'icon' => 'img/ico-twitter.svg',
            //SNSリンク
            'url' => 'https://x.com',
            //trueで表示、falseで非表示を切り替える
            'display' => true
        ],
        'facebook' => [
            //表示するサービス名
            'name' => 'facebook',
            //snsのサービスアイコン
            'icon' => 'img/ico-facebook.svg',
            //SNSリンク
            'url' => 'https://facebook.com',
            //trueで表示、falseで非表示を切り替える
            'display' => true
        ],
    ],
    //サイトのテーマカラー
    'theme-color' => 'unset',
    //og image
    'og:image' => 'img/ogp.png',
    'site_meta' => [
        //トップのurlとして使用する文字列、phpとhtmlを切り替えたりするのに使う
        'top_url' => 'index.php',
        'indent' => '    '
    ]
];
//各種パラメーターはhead.phpを読み込む前に書き換えると任意の値に変更できます
$head_meta = [
    'canonical' => '',
    'og' => [
        //site_nameは空の場合$blog_info['name']が適用されます。
        'site_name' => '',
        //urlは空の場合topの場合canonicalを引き継ぎます、それ以外のページはページURLが適用されます。
        'url' => '',
        //typeをページごとに異なる値で設定する場合はhead.phpを読み込む前に書き換えてください。
        //page metaでもこの項目を上書き可能です。page metaが存在する場合pae metaが優先されます。
        'type' => 'website',
        //タイトルは空の場合ページタイトルが適用されます。
        'title' => '',
        //descriptionは空かつtopの場合$blog_info['description']を引き継ぎます
        //それ以外のページで空の場合何も出力されません、この項目を出力する場合はhead.phpを読み込む前に書き換える必要があります
        'description' => '',
        //image空の場合そのページのアイキャッチが適用されます。アイキャッチが設定されていない場合は出力されません。
        'image' => '',
        //設定した値が前ページで利用されます、日本のロケールは「ja_JP」です。
        'locale' => 'ja_JP'
    ],
    'twitter' => [
        'card' => '',
        //descriptionは空かつtopの場合$blog_info['description']を引き継ぎます
        //それ以外のページで空の場合何も出力されません、この項目を出力する場合はhead.phpを読み込む前に書き換える必要があります
        'description' => '',
        //image空の場合そのページのアイキャッチが適用されます。アイキャッチが設定されていない場合は出力されません。
        'image' => '',
        //Twitterのユーザー名を入れてください。空の場合出力されません
        'site' => '',
        //タイトルは空の場合ページタイトルが適用されます。
        //title、page meta twitter title > page meta title > head meta twitter title > head meta og titleの優先順位で適用されます。
        'title' => ''
    ]
];
//twitterをxとしても保存
$head_meta ['x']= $head_meta['twitter'];
if(isset($_GET['local'])){
    //local
    $blog_info['home'] = '';
}