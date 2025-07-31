<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<div class="bl_topKvBottomCta"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
    //現在のインデントの個数
    $indent_level = 1;?>

    <div class="bl_topKvBottomCta_ctaWrap"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
        //現在のインデントの個数
        $indent_level = 2;?>

        <?php //CTA
            include( "parts/bl_cta/cta.php" )
        ?>
    </div>
</div><?php //ここにモジュールの内容 ?><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>