<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- CTA -->
<div class="bl_cta">
    <div class="bl_cta_inner">
        <!-- CTAメッセージ -->
        <div class="bl_cta_msg">
            <span class="bl_cta_msg_tag">相談無料</span>
            <span class="bl_cta_msg_txt">お気軽にお問い合わせください！</span>
        </div>
        <!-- end CTAメッセージ -->

        <!-- CTAボタングループ -->
        <div class="bl_cta_btnGroup">
            <!-- 電話ボタン -->
            <div class="bl_cta_btnGroup_item">
                <a href="tel:070-8524-5047" class="bl_cta_btnGroup_item_link">
                    <div class="bl_cta_btnGroup_item_iconWrap">
                        <img src="/img/tel-icon-white.svg" alt="" width="32" height="32" class="bl_cta_btnGroup_item_iconWrap_icon">
                    </div>
                    <div class="bl_cta_btnGroup_item_txtGroup">
                        <span class="bl_cta_btnGroup_item_txtGroup_tel">070-8524-5047</span>
                        <span class="bl_cta_btnGroup_item_txtGroup_time">平日 9:00-17:00 営業</span>
                    </div>
                </a>
            </div>
            <!-- end 電話ボタン -->

            <!-- LINEボタン -->
            <div class="bl_cta_btnGroup_item">
                <a href="#" class="bl_cta_btnGroup_item_link">
                    <!-- TODO:LINEリンク設定 -->
                    <div class="bl_cta_btnGroup_item_iconWrap">
                        <img src="/img/line-icon.svg" alt="" width="32" height="32" class="bl_cta_btnGroup_item_iconWrap_icon">
                    </div>
                    <div class="bl_cta_btnGroup_item_txtGroup">
                        <span class="bl_cta_btnGroup_item_txtGroup_title">LINE</span>
                        <div class="bl_cta_btnGroup_item_txtGroup_group">
                            <span class="bl_cta_btnGroup_item_txtGroup_desc">
                                で気軽にご相談
                            </span>
                            <span class="bl_cta_btnGroup_item_txtGroup_arrow">
                                <img class="bl_cta_btnGroup_item_txtGroup_arrow_img" src="/img/arrow-white.svg" alt="" aria-hidden="true" width="9" height="9">
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <!-- end LINEボタン -->
        </div>
        <!-- end CTAボタングループ -->
    </div>
</div>
<!-- end CTA --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>