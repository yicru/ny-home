<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- 施工実績 -->
<section class="bl_case">
    <div class="bl_case_inner">
        <!-- セクションヘッダー -->
        <div class="bl_case_header">
            <div class="el_ttlHeader">
                <span>case study</span>
            </div>
            <h2 class="bl_case_header_ttl">施工実績</h2>
        </div>
        <!-- end セクションヘッダー -->

        <!-- 実績リスト -->
        <ul class="bl_case_list"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>
            <?php
            set_include_data('bl_beforeAfter', [
                [
                    'tag' => 'li',
                    'src' => '/img/case-img.jpg',
                    'alt' => 'キッチン水漏れ：排水管からの水漏れで床材が腐敗',
                    'width' => 317,
                    'height' => 198,
                    'ttl' => 'キッチン水漏れ',
                    'txt' => '：排水管からの水漏れで床材が腐敗'
                ],
                [
                    'tag' => 'li',
                    'src' => '/img/case-img2.jpg',
                    'alt' => 'トイレ壁水濡れ：トイレタンクからの水濡れで壁が被害',
                    'width' => 317,
                    'height' => 198,
                    'ttl' => 'トイレ壁水濡れ',
                    'txt' => '：トイレタンクからの水濡れで壁が被害'
                ],
                [
                    'tag' => 'li',
                    'src' => '/img/case-img3.jpg',
                    'alt' => '店舗雨漏れ：強風により雨漏れが発生し店舗内設備が被害',
                    'width' => 317,
                    'height' => 198,
                    'ttl' => '店舗雨漏れ',
                    'txt' => '：強風により雨漏れが発生し店舗内設備が被害'
                ],
                [
                    'tag' => 'li',
                    'src' => '/img/case-img4.jpg',
                    'alt' => 'ベランダ波板屋根破損：強風によりベランダの波板が破損',
                    'width' => 317,
                    'height' => 198,
                    'ttl' => 'ベランダ波板屋根破損',
                    'txt' => '：強風によりベランダの波板が破損'
                ],
                [
                    'tag' => 'li',
                    'src' => '/img/case-img5.jpg',
                    'alt' => 'ブラインドカーテン破損：掃除中にひっかけてしまい破損',
                    'width' => 317,
                    'height' => 198,
                    'ttl' => 'ブラインドカーテン破損',
                    'txt' => '：掃除中にひっかけてしまい破損'
                ],
            ]);
            // beforeAfterデータをループで呼び出し
            render_include_loop('bl_beforeAfter', 'parts/bl_case/beforeAfter.php',3);?>
        </ul>
        <!-- end 実績リスト -->
    </div>
</section>
<!-- end 施工実績 -->
<?php //ここにモジュールの内容 ?><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>