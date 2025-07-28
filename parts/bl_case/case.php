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
        <div class="bl_case_list"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
            //現在のインデントの個数
            $indent_level = 3;?>
            <?php
            //リスト
            include( "parts/bl_case/beforeAfter.php" )?>
        </div>
        <!-- end 実績リスト -->
    </div>
</section>
<!-- end 施工実績 -->
<?php //ここにモジュールの内容 ?><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>