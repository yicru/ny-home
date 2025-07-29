<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- header -->
<header class="bl_header">
    <div class="bl_header_inner">
        <!-- ロゴ -->
        <h1 class="bl_header_logoWrap">
            <a href="/" class="bl_header_logoWrap_link">
                <span class="un_hide">NYHOME</span>
                <img src="/img/header-logo.jpg" alt="NYHOME" width="130" height="31" class="bl_header_logoWrap_logo">
            </a>
        </h1>
        <!-- end ロゴ -->

        <!-- ボタングループ -->
        <div class="bl_header_btnGroup">
            <!-- TEL -->
            <div class="bl_header_btnGroup_iconWrap">
                <a href="tel:" class="bl_header_btnGroup_iconWrap_link">
                    <!-- TODO:電話番号設定 -->
                    <img src="img/header-tel-btn.svg" alt="TEL" width="" height="" class="bl_header_btnGroup_iconWrap_icon">
                </a>
            </div>
            <!-- end TEL -->

            <!-- LINE -->
            <div class="bl_header_btnGroup_iconWrap">
                <a href="#" class="bl_header_btnGroup_iconWrap_link">
                    <!-- TODO:LINEリンク設定 -->
                    <img src="img/header-line-btn.svg" alt="" width="" height="" class="bl_header_btnGroup_iconWrap_icon">
                    <span class="un_hide">LINEで無料相談</span>
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