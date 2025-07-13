<?php
//複数行の文字列をインデントを付けて出力する。
//開始用関数
//インデントがすでに設定されているファイルでネストして実行するときは
//もともと設定されていたインデントに数値が戻るように設定してください
/*
以下は例です。
    //インデントを合わせる
    $indent_level_swap = $indent_level;
    $indent_level = 2;
    include( "header-sns.php" );//←ここでもauto_indent_startをしている場合、二重で実行される。
    //もとのインデント設定に戻す
    $indent_level = $indent_level_swap;
*/
function auto_indent_start() {
    //バッファリング
    ob_start();
}
//終了用用関数
//outputがtrueであればそのままechoし、falseだったら加工後の文字列をそのまま返す
function auto_indent_end( $output = true , $indent, $current_indent, $indent_level) {
    //バッファリング終了
    $compress = ob_get_clean();
    //改行コードを一意にする。
    $compress = str_replace( [ "\r\n", "\r" ], "\n", $compress);
    //一行ごとの配列に変換する
    $compress = explode("\n", $compress);

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
    //インデントを足した文字列に加工
    $out_str = '';
    //一行目かどうかを見る
    $first = true;
    foreach ( $compress as $row ) {
        if( $first ) {
            //1行目にはインデントを付けない
            $out_str .= $row."\n";
            $first = false;
        } else {
            $out_str .= $current_indent.$row."\n";
        }
    }
    //空白だけの行を消す
    $out_str = preg_replace("/^\s+\n/m", "\n", $out_str);

    //$outputの値に合わせて出力か、値を返すか分岐する
    if( $output ) {
        echo $out_str;
    } else {
        return $out_str;
    }
}
//配列のkeyがあるかチェックする
//あれば値を返して、なければ''を返す、存在しない値を出力してエラーになるのを回避するための関数
function array_key_empty_check( $arr, $key ){
    //ない
    if ( empty( $arr[$key] )) {
        //なければ空白を返す
        return '';
    } else {
        //あれば値を返す
        return $arr[$key];
    }
}

//wordpressっぽいデータを作っています
//ぽいってだけで同じ機能が存在するわけではありません。
//idを指定すれば投稿オブジェクトを返して、idが間違っていればfalseを返す
//get_post_data(12)みたいにするとpost オブジェクト返す
function get_post_data( $id = false ){
    //サンプル記事データが入っているjsonを取得
    $json = file_get_contents( "data/post-data.json" );
    $post_data = json_decode( $json );
    if( $id == false ) {
        //投稿データ丸ごと返す
        return $post_data;
    } else if( is_numeric( $id ) ) {//falseでないなら数字かどうかを判断する
        //該当のページがあるか検索
        //投稿データだけ切り出す
        $posts = $post_data->posts;
        //投稿データからidのリストを取得
        $post_id_list = array_column( $posts, 'id');
        //投稿が存在しない場合配列が空なので、空じゃないかチェック
        if(is_array($post_id_list) && empty($post_id_list)){
            //記事がないのでfalseを返す
            return false;
        } else {
            //idのリストからキーがあるか見る
            $target_post_id = array_keys( $post_id_list, intval($id), true);
            //投稿が存在しない場合配列が空なので、空じゃないかチェック
            if(is_array($target_post_id) && empty($target_post_id)) {
                //記事がないのでfalseを返す
                return false;
            } else {
                //複数該当することはないけど、一応1番目だけ出力する形式にしてポストオブジェクトを返す
                return $post_data->posts[$target_post_id[0]];
            }
        }
    } else{
        //想定されていない値が入力されているのでfalse
        return false;
    }
}
//カテゴリーが存在するかをチェックする、slug、catidに対応
//存在すれば該当カテゴリーの配列を返す
function category_exists( $cat ) {
    //該当する投稿があるか見る
    $post_data = get_post_data();
    if ( $post_data != false ) {
        //カテゴリーのリストを取得
        $cat_list = $post_data->taxonomy->categories;
        //配列に直す
        $cat_list = json_decode(json_encode($cat_list), true);
        //idかslugかを判定する、数字slugはfalseが返されるので注意slugに数字だけを指定することはできない
        //[todo]↑is_numericで見てると16進数みたいのも通っちゃうのでたまたま16進数みたいなslugついてたらこれがうまく動かないのでは？
        if( is_numeric($cat) ){
            //数値だった場合
            //カテゴリーが存在しているかを見る
            if ( isset( $cat_list[$cat] ) ) {
                //カテゴリーが存在してればカテゴリー配列を返す
                return json_decode(json_encode($cat_list[$cat]), true);
            } else {
                //存在してないのでfalseを返す
                return false;
            }
        } else {
            //数値以外
            //categoryのslug listを作る
            $category_slug_list = array_column( $cat_list, 'slug');
            //slugのリストから該当のslugがあるか見る、あれば配列のキーが代入されるので、キーからidを特定してカテゴリーオブジェクトを返す
            $target_cat_key = in_array( $cat, $category_slug_list, true);

            //該当のslugが存在しなければ$target_cat_keyがfalseなので、falseを返し、存在していればキーからidを特定してカテゴリーオブジェクトを返す
            if( $target_cat_key != false) {
                //キーからidを特定してカテゴリー配列を返す
                return json_decode(json_encode(current(array_slice($cat_list, ($target_cat_key + 1), 1, true))), true);
            } else {
                //slugが該当するカテゴリーがないのでfalseを返す
                return false;
            }
        }
    } else {
        //投稿データを取得できないのでfalseを返す
        return false;
    }
}

//記事のid貰ったら設定中のカテゴリーを返す、カテゴリーは複数ついてたら複数で返す。
function get_the_category( $id = false ) {
    $unset = [
        "id" => "unset",
        "name" => "未分類",
        "slug" => "unset",
        "date" => "",
        "description" => "カテゴリーが未分類の投稿です。",
        "parent" => false,
        "parent_id" => "",
        "child" => false
    ];
    //何も渡されなかったときにエラーにならないようにしておく
    if( $id != false) {
        //該当記事を取得
        $post_data = get_post_data( $id );
        //配列に変換
        $post_data = json_decode(json_encode($post_data), true);
        if( $post_data != false ) {
            //カテゴリーが設定されているかを確認
            if( isset( $post_data["taxonomy"]["category"] )) {
                //未分類が設定されている場合
                if( $post_data["taxonomy"]["category"] == 'unset') {
                    //未分類のカテゴリー配列を返す
                    return $unset;
                } elseif( is_array( $post_data["taxonomy"]["category"] ) ) {//複数設定されている場合
                    $cats = [];
                    foreach( $post_data["taxonomy"]["category"] as $cat ) {
                        //カテゴリーが存在して居れば追加
                        //unsetがあれば無視する
                        if( $cat != 'unset') {
                            if( category_exists( $cat ) != false ) {
                                $cats []= category_exists( $cat );
                            }
                        }
                    }
                    return $cats;
                } else {
                    //ひとつだけ設定されている場合
                    //カテゴリーが存在するかチェック
                    $cat = category_exists( $post_data["taxonomy"]["category"] );
                    if ( $cat != false ) {
                        //存在すればカテゴリーを返す
                        return $cat;
                    } else {
                        //存在しないカテゴリーなのでfalseを返す
                        return false;
                    }
                }
            } else {
                //カテゴリーが設定されていなかったら未分類のカテゴリーを返す。
                return $unset;
            }
        } else {
            //記事が存在しないのでfalseを返す
            return false;
        }
    } else {
        //id指定されていないのでfalse
        return false;
    }
}

//<!--[if IE ]>のようなものを壊さないように、常に角括弧を復元する関数
function unescape_invalid_shortcodes( $content ) {
	// Clean up entire string, avoids re-parsing HTML.
	$trans = array(
		'&#91;' => '[',
		'&#93;' => ']',
	);

	$content = strtr( $content, $trans );

	return $content;
}
//
function get_shortcode_regex( $tagnames = null ) {
	global $shortcode_tags;

	if ( empty( $tagnames ) ) {
		$tagnames = array_keys( $shortcode_tags );
	}
	$tagregexp = implode( '|', array_map( 'preg_quote', $tagnames ) );

	/*
	 * WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag().
	 * Also, see shortcode_unautop() and shortcode.js.
	 */

	// phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
	return '\\['                             // Opening bracket.
		. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
		. "($tagregexp)"                     // 2: Shortcode name.
		. '(?![\\w-])'                       // Not followed by word character or hyphen.
		. '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
		.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
		.     '(?:'
		.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
		.         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
		.     ')*?'
		. ')'
		. '(?:'
		.     '(\\/)'                        // 4: Self closing tag...
		.     '\\]'                          // ...and closing bracket.
		. '|'
		.     '\\]'                          // Closing bracket.
		.     '(?:'
		.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
		.             '[^\\[]*+'             // Not an opening bracket.
		.             '(?:'
		.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
		.                 '[^\\[]*+'         // Not an opening bracket.
		.             ')*+'
		.         ')'
		.         '\\[\\/\\2\\]'             // Closing shortcode tag.
		.     ')?'
		. ')'
		. '(\\]?)';                          // 6: Optional second closing bracket for escaping shortcodes: [[tag]].
	// phpcs:enable
}

//ショートコード用に作ったcta出力
function single_bnr_output(){
    //該当のファイルを実行させて、その間で出力した文字をバッファリングし返す
    ob_start();
    //single用を想定しているので他のページで実行してもレイアウトがうまくいかない
    include( "parts/single-bnr.php" );
    return ob_get_clean();
}
//ショートコード用に作ったコンタクトフォーム出力
function contact_form_output(){
    //該当のファイルを実行させて、その間で出力した文字をバッファリングし返す
    ob_start();
    //single用を想定しているので他のページで実行してもレイアウトがうまくいかない
    include( "parts/contact-form.php" );
    return ob_get_clean();
}

$shortcode_tags = [
    "single-bnr" => "single_bnr_output",
    "contact-form" => "contact_form_output",
];
function do_shortcode( $content ) {
	global $shortcode_tags;
    //開始タグを含んでいなかったらそのまま返す
	if ( ! str_contains( $content, '[' ) ) {
		return $content;
	}
    //サイト側に設定されているショートコードがなければそのまま返す
	if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
		return $content;
	}

	// 存在するすべてのタグを配列に変える
	preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
	$tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

    //タグがなかったらそのまま返却
	if ( empty( $tagnames ) ) {
		return $content;
	}

	//wordpressにあるフィルター系の処理はしない
    //html内に直接入っているショートコードは実行せずに無視する。
	//$content = do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames );

    //ショートコードの正規表現を取得
	$pattern = get_shortcode_regex( $tagnames );
    //ショートコード実行して置き換える
	$content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content );

	// <!--[if IE ]>のようなものを壊さないように、常に角括弧を復元する。
	$content = unescape_invalid_shortcodes( $content );

	return $content;
}

function do_shortcode_tag( $matches ) {
    global $shortcode_tags;
    //$matches[0]にマッチした文字列がはいってる
    //[]を取り除く
    $shortcode = str_replace('[', '', $matches[0]);
    $shortcode = str_replace(']', '', $shortcode);
    //定義されているタグを実行して返す
    return $shortcode_tags[$shortcode]();
}