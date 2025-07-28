<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
//dataの中にonce dataを入れ、
/*
bl_beforeAfter
    bl_beforeAfter
        tag
    bl_beforeAfter_img
        src
        alt
        width
        height
    bl_beforeAfter_txtGroup_ttl
        txt
    bl_beforeAfter_txtGroup_txt
        txt
*/

?>
<!-- ビフォーアフター -->
<div class="bl_beforeAfter">
    <div class="bl_beforeAfter_imgWrap">
        <!-- TODO:img width height設定 -->
        <img class="bl_beforeAfter_img" src="#" alt="ベランダ波板屋根破損の修繕" width="317" height="198">
    </div>
    <div class="bl_beforeAfter_txtGroup">
        <h3 class="bl_beforeAfter_txtGroup_ttl">ベランダ波板屋根破損の修繕</h3>
        <p class="bl_beforeAfter_txtGroup_txt">強風によりベランダの波板が破損</p>
    </div>
</div>
<!-- end ビフォーアフター --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>