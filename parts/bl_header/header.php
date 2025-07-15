<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- header -->
<header class="bl_header">
    <div class="bl_header_inner">
        <!-- ロゴ -->
        <div class="bl_header_logoWrap">
            <a href="/" class="bl_header_logoWrap_link">
                <span class="un_hide">NYHOME</span>
                <img src="img/logo.svg" alt="NYHOME" width="" height="" class="bl_header_logoWrap_logo">
            </a>
        </div>
        <!-- end ロゴ -->

        <!-- ボタングループ -->
        <div class="bl_header_btnGroup">
            <!-- TEL -->
            <div class="bl_header_btnGroup_iconWrap">
                <a href="tel:" class="bl_header_btnGroup_iconWrap_link">
                    <!-- TODO:電話番号設定 -->
                    <img src="img/tel-icon.svg" alt="TEL" width="" height="" class="bl_header_btnGroup_iconWrap_icon">
                </a>
            </div>
            <!-- end TEL -->

            <!-- LINE -->
            <div class="bl_header_btnGroup_iconWrap">
                <a href="#" class="bl_header_btnGroup_iconWrap_link">
                    <!-- TODO:LINEリンク設定 -->
                    <img src="img/line-icon.svg" alt="" width="" height="" class="bl_header_btnGroup_iconWrap_icon">
                    <span class="bl_header_btnGroup_iconWrap_txt">LINEで無料相談</span>
                </a>
            </div>
            <!-- end LINE -->
        </div>
        <!-- end ボタングループ -->
    </div>
</header>
<!-- end header --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>