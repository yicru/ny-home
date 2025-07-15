<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- ビフォーアフター -->
<article class="bl_beforeAfter">
    <!-- TODO:リンクの設定 -->
    <a href="#" class="bl_beforeAfter_link">
        <div class="bl_beforeAfter_imgGroup">
            <div class="bl_beforeAfter_imgGroup_group">
                <div class="bl_beforeAfter_imgGroup_group_img">
                    <!-- TODO:img width height設定 -->
                    <img src="#" alt="修繕前のベランダ波板屋根" width="" height="">
                </div>
                <div class="bl_beforeAfter_imgGroup_group_label">BEFORE</div>
            </div>
            <div class="bl_beforeAfter_imgGroup_group2">
                <div class="bl_beforeAfter_imgGroup_group2_img">
                    <!-- TODO:img width height設定 -->
                    <img src="#" alt="修繕後のベランダ波板屋根" width="" height="">
                </div>
                <div class="bl_beforeAfter_imgGroup_group2_label">AFTER</div>
            </div>
        </div>
        <div class="bl_beforeAfter_txtGroup">
            <h3 class="bl_beforeAfter_txtGroup_ttl">ベランダ波板屋根破損の修繕</h3>
            <p class="bl_beforeAfter_txtGroup_txt">強風によりベランダの波板が破損</p>
        </div>
    </a>
</article>
<!-- end ビフォーアフター --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>