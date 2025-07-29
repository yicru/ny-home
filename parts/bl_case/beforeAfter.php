<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<!-- ビフォーアフター -->
<<?php echo htmlspecialchars($module['tag']); ?> class="bl_beforeAfter">
    <div class="bl_beforeAfter_imgWrap">
        <img class="bl_beforeAfter_img"
            src="<?php echo htmlspecialchars($module['src']); ?>"
            alt="<?php echo htmlspecialchars($module['alt']); ?>"
            width="<?php echo $module['width']; ?>"
            height="<?php echo $module['height']; ?>">
    </div>
    <div class="bl_beforeAfter_txtGroup">
        <?php
        // ttlが存在し、空でない場合のみ出力
        if (!empty($module['ttl'])) {
            echo '<h3 class="bl_beforeAfter_txtGroup_ttl">' . htmlspecialchars($module['ttl']) . '</h3>';
        }
        ?>
        <?php
        // txtが存在し、空でない場合のみ出力
        if (!empty($module['txt'])) {
            echo '<p class="bl_beforeAfter_txtGroup_txt">' . htmlspecialchars($module['txt']) . '</p>';
        }

        //この書き方でもいい、インデントを考慮していない
        // 条件付き出力（helper関数使用）
        //echo output_if_exists('<h3 class="bl_beforeAfter_txtGroup_ttl">%s</h3>', $module['ttl'] ?? '');
        //echo output_if_exists('<p class="bl_beforeAfter_txtGroup_txt">%s</p>', $module['txt'] ?? '');
        ?>
    </div>
</<?php echo htmlspecialchars($module['tag']); ?>>
<!-- end ビフォーアフター --><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>