<?php
/**
 * PHPファイルをインクルードしてHTMLファイルとして書き出すツール
 * PHP 8.2以上対応
 */

// 設定配列
$exportSettings = [
    [
        'include_file' => 'index.php',
        'output_dir' => './',
        'output_file' => 'index.html'
    ]
    // 必要に応じて設定を追加
];

/**
 * PHPファイルをインクルードしてHTMLとして書き出す
 * @param string $includeFile インクルードするPHPファイルのパス
 * @param string $outputDir 出力先ディレクトリ
 * @param string $outputFile 出力ファイル名
 * @return bool 成功時true、失敗時false
 */
function exportToHtml(string $includeFile, string $outputDir, string $outputFile): bool
{
    try {
        // インクルードファイルの存在チェック
        if (!file_exists($includeFile)) {
            throw new Exception("インクルードファイルが見つかりません: {$includeFile}");
        }

        // 出力ディレクトリの作成
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new Exception("出力ディレクトリの作成に失敗しました: {$outputDir}");
            }
        }

        // 出力バッファリング開始
        ob_start();

        // PHPファイルをインクルード
        include $includeFile;

        // バッファの内容を取得
        $htmlContent = ob_get_contents();

        // バッファをクリア
        ob_end_clean();

        // 出力ファイルのフルパス
        $outputPath = rtrim($outputDir, '/') . '/' . $outputFile;

        // HTMLファイルとして書き出し
        $result = file_put_contents($outputPath, $htmlContent);

        if ($result === false) {
            throw new Exception("ファイルの書き出しに失敗しました: {$outputPath}");
        }

        echo "成功: {$includeFile} → {$outputPath}<br>";
        return true;

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}<br>";
        return false;
    }
}

/**
 * 設定配列に基づいて一括書き出し
 * @param array $settings 設定配列
 * @return array 結果配列
 */
function batchExport(array $settings): array
{
    $results = [];
    foreach ($settings as $index => $setting) {
        echo "処理中 (" . ($index + 1) . "/" . count($settings) . "): {$setting['include_file']}<br>";

        $result = exportToHtml(
            $setting['include_file'],
            $setting['output_dir'],
            $setting['output_file']
        );

        $results[] = [
            'setting' => $setting,
            'success' => $result
        ];
    }
    return $results;
}

// メイン処理
echo "HTMLファイル書き出し開始<br>";
echo str_repeat("=", 50) . "<br>";

$results = batchExport($exportSettings);

echo str_repeat("=", 50) . "<br>";
echo "処理完了<br>";

// 結果サマリー
$successCount = count(array_filter($results, fn($r) => $r['success']));
$totalCount = count($results);

echo "成功: {$successCount}/{$totalCount}<br>";

if ($successCount < $totalCount) {
    echo "失敗したファイル:<br>";
    foreach ($results as $result) {
        if (!$result['success']) {
            echo "- {$result['setting']['include_file']}<br>";
        }
    }
}
?>