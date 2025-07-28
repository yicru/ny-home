<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- bl_future -->
<section class="bl_future">
    <div class="bl_future_inner">
        <div class="bl_future_header">
            <div class="el_ttlHeader">
                <span>future</span>
            </div>
            <h2 class="bl_future_header_ttl">
                <div class="bl_future_header_ttl_group">
                    <span>NY HOME</span>
                    が選ばれる
                </div>
                <div class="bl_future_header_ttl_lv2">
                    <span class="bl_future_header_ttl_num">3</span>
                    <span>つ</span>
                    <span>の</span>
                    <span>理</span>
                    <span>由</span>
                </div>
            </h2>
        </div>

        <ul class="bl_future_list"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>

            <?php
            //ヘッダー
            include( "parts/bl_pointCard/pointCard.php" )?>
        </ul>
    </div>
</section>
<!-- end bl_future --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>