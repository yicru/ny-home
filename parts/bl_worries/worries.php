<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- お悩みセクション -->
<section class="bl_worries">
    <!-- 背景グレーエリア -->
    <div class="bl_worries_group">
        <div class="bl_worries_group_inner">
            <div class="el_ttlHeader">
                <span>worries</span>
            </div>
            <h2 class="bl_worries_ttl">こんなお悩みありませんか？</h2>

            <div class="bl_worries_fukidasi">
                <!-- お悩み1 -->
                <div class="bl_worries_txtGroup">
                    <p class="bl_worries_txtGroup_txt">自然災害で家がボロボロ…</p>
                </div>
                <!-- end お悩み1 -->

                <!-- お悩み2 -->
                <div class="bl_worries_txtGroup bl_worries_txtGroup__rev">
                    <p class="bl_worries_txtGroup_txt">トイレが壊れて壁と床が水浸し…</p>
                </div>
                <!-- end お悩み2 -->

                <!-- お悩み3 -->
                <div class="bl_worries_txtGroup">
                    <p class="bl_worries_txtGroup_txt">掃除中に窓を壊しちゃった！！</p>
                </div>
                <!-- end お悩み3 -->

                <!-- お悩み4 -->
                <div class="bl_worries_txtGroup bl_worries_txtGroup__rev">
                    <p class="bl_worries_txtGroup_txt">留守の間に盗難にあった！！</p>
                </div>
                <!-- end お悩み4 -->
            </div>

            <img src="/img/worries-illlust.png" alt="" class="bl_worries_img" width="377" height="206" aria-hidden="true">
        </div>
    </div>
    <!-- end 背景グレーエリア -->

    <!-- 背景白エリア -->
    <div class="bl_worries_group2">
        <div class="bl_worries_group2_inner">
            <div class="bl_worries_solutionTxt">
                <h2 class="bl_worries_solutionTxt_para">
                    <span>あなたのお悩みを</span>
                    <div class="bl_worries_solutionTxt_para_group">
                        <span class="un_hide">NYHOME</span>
                        <img src="/img/header-logo.jpg" alt="NYHOME" width="130" height="31" class="bl_header_logoWrap_logo">
                        <span>の</span>
                    </div>
                    <div class="bl_worries_solutionTxt_para_group2">
                        <span class="bl_worries_solutionTxt_para_lv2">火災保険適応工事で</span>解決！
                    </div>
                </h2>
            </div>

            <img src="/img/worries-illlust2.png" alt="" class="bl_worries_group2_img" width="" height="" aria-hidden="true">

            <h3 class="bl_worries_subTtl">
                <span>火災保険の申請から工事まで</span>
                <span class="bl_worries_subTtl_lv2">一貫して対応します！</span>
            </h3>

            <div class="bl_worries_descTxt_group">
                <div class="bl_worries_descTxt">
                    <p class="bl_worries_descTxt_para">
                        火災保険は、自然災害による被害はもちろん、<br>
                        <span class="bl_worries_descTxt_para_lv2">
                            建物の破損や汚損、水漏れ、盗難など…
                        </span>
                        日常生活で起こる様々なトラブルも保証の対象です。
                    </p>
                </div>

                <div class="bl_worries_descTxt">
                    <p class="bl_worries_descTxt_para">
                        NY HOMEは、<span class="bl_worries_descTxt_para_lv2">火災保険の申請サポートから工事まで一貫して対応します！</span>
                    </p>
                </div>

                <div class="bl_worries_descTxt">
                    <p class="bl_worries_descTxt_para">
                        まずは、お持ちの火災保険でどこまでの補償が受けられるのかチェック！まずはお気軽にご相談ください。
                    </p>
                </div>
            </div>

            <div class="bl_worries_ctaWrap"><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
                //現在のインデントの個数
                $indent_level = 4;?>

                <?php
                //cta
                include( "parts/bl_cta/cta.php" )?>
            </div>
        </div>
    </div>
    <!-- end 背景白エリア -->
</section>
<!-- end お悩みセクション --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>