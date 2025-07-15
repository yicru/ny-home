<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- footer -->
<footer class="bl_footer">
    <div class="bl_footer_inner">
        <!-- フッターナビゲーション -->
        <nav class="bl_footer_nav">
            <ul class="bl_footer_nav_list">
                <li class="bl_footer_nav_item">
                    <a href="#" class="bl_footer_nav_item_link">お問い合わせ</a>
                    <!-- TODO:リンク設定 -->
                </li>
                <li class="bl_footer_nav_item">
                    <a href="#" class="bl_footer_nav_item_link">プライバシーポリシー</a>
                    <!-- TODO:リンク設定 -->
                </li>
            </ul>
        </nav>
        <!-- end フッターナビゲーション -->

        <!-- コピーライト -->
        <div class="bl_footer_copy">
            <p class="bl_footer_copy_txt">© NY HOME All Rights Reserved.</p>
        </div>
        <!-- end コピーライト -->
    </div>
</footer>
<!-- end footer --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>