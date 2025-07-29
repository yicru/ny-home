<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<div class="bl_topKvBottomCta">
    <div class="bl_topKvBottomCta_ctaWrap">
    <?php //CTA
        include( "parts/bl_cta/cta.php" )
    ?>
    </div>
</div><?php //ここにモジュールの内容 ?><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>