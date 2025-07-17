<?php
/**
 * SCSS Color管理機能
 * 
 * プロジェクトのSCSS色変数を管理・編集するための機能群
 * - 色の解析・検索
 * - 色の追加・編集・削除
 * - バックアップ・復元
 * - 自動色名生成
 */

// CLI実行チェック
// CLI実行チェック（Web APIからの呼び出しは除外）
if (php_sapi_name() !== 'cli' && !defined('MP_WEB_API')) {
    die("このスクリプトはコマンドラインでのみ実行可能です。\n");
}

define('SCSS_COLOR_FILE', __DIR__ . '/../../scss/global/_color.scss');
define('SCSS_BACKUP_DIR', __DIR__ . '/../backup/color/');
define('SCSS_MAX_BACKUPS', 10);

/**
 * SCSS Color管理クラス
 */
class SCSSColorManager
{
    private string $scssFile;
    private array $colors = [];
    private array $categories = [
        'background' => 'background color',
        'border' => 'border color',
        'font' => 'font color',
        'hover' => 'hover color',
        'shadow' => 'shadow color'
    ];

    public function __construct(string $scssFile = null)
    {
        $this->scssFile = $scssFile ?? SCSS_COLOR_FILE;
        $this->loadColors();
    }

    /**
     * SCSSファイルから色情報を読み込み
     */
    private function loadColors(): void
    {
        if (!file_exists($this->scssFile)) {
            throw new Exception("SCSSファイルが見つかりません: {$this->scssFile}");
        }

        $content = file_get_contents($this->scssFile);
        $this->colors = $this->parseColors($content);
    }

    /**
     * SCSS内容から色変数を解析
     */
    private function parseColors(string $content): array
    {
        $colors = [];
        $lines = explode("\n", $content);
        $currentComment = '';
        $inColorSection = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // :root{ が始まったらカラーセクション開始
            if (str_contains($line, ':root{')) {
                $inColorSection = true;
                $currentComment = ''; // 最初のコメントをリセット
                continue;
            }

            // }でカラーセクション終了
            if ($line === '}' && $inColorSection) {
                $inColorSection = false;
                break;
            }

            // カラーセクション内でのみ処理
            if (!$inColorSection) {
                continue;
            }

            // コメント行の処理（カテゴリーコメントは除外）
            if (str_starts_with($line, '//')) {
                $comment = trim(substr($line, 2));

                // カテゴリーコメントや説明文は除外
                if (!str_contains($comment, 'その場所でしか使わない') && 
                    !str_contains($comment, 'トランジション') &&
                    !str_starts_with($comment, 'background') &&
                    !str_starts_with($comment, 'border') &&
                    !str_starts_with($comment, 'font') &&
                    !str_starts_with($comment, 'hover') &&
                    !str_starts_with($comment, 'shadow')) {
                    $currentComment = $comment;
                }
                continue;
            }

            // カテゴリーコメント行の処理（/* background color */ など）
            if (preg_match('/\/\*\s*([^*]+)\s*\*\//', $line)) {
                $currentComment = ''; // カテゴリー開始時はコメントリセット
                continue;
            }

            // 色変数の解析
            if (preg_match('/--color-([^:]+):\s*([^;]+);/', $line, $matches)) {
                $varName = trim($matches[1]);
                $colorValue = trim($matches[2]);

                // カテゴリー判定
                $category = $this->determineCategory($varName);

                $colors[] = [
                    'name' => $varName,
                    'value' => $colorValue,
                    'category' => $category,
                    'comment' => $currentComment,
                    'variable' => "--color-{$varName}"
                ];

                $currentComment = ''; // コメントをリセット
            }
        }

        return $colors;
    }

    /**
     * 変数名からカテゴリーを判定
     */
    private function determineCategory(string $varName): string
    {
        if (str_starts_with($varName, 'bg-')) return 'background';
        if (str_starts_with($varName, 'border-')) return 'border';
        if (str_starts_with($varName, 'font-')) return 'font';
        if (str_contains($varName, '-hover-') || str_ends_with($varName, '-hover')) return 'hover';
        if (str_contains($varName, '-shadow-') || str_ends_with($varName, '-shadow')) return 'shadow';

        return 'background'; // デフォルト
    }

    /**
     * 指定された色値で色を検索
     */
    public function searchByColor(string $colorValue): array
    {
        $normalizedColor = $this->normalizeColor($colorValue);
        $results = [];

        foreach ($this->colors as $color) {
            if ($this->normalizeColor($color['value']) === $normalizedColor) {
                $results[] = $color;
            }
        }

        return $results;
    }

    /**
     * 色値を正規化（大文字小文字、#の有無を統一）
     */
    private function normalizeColor(string $color): string
    {
        $color = strtolower(trim($color));
        if (!str_starts_with($color, '#') && $color !== 'unset' && $color !== 'transparent') {
            $color = '#' . $color;
        }
        return $color;
    }

    /**
     * 近似色を検索
     */
    public function findSimilarColors(string $colorValue, int $threshold = 30): array
    {
        if (!$this->isValidHexColor($colorValue)) {
            return [];
        }

        $targetRgb = $this->hexToRgb($colorValue);
        $similar = [];

        foreach ($this->colors as $color) {
            if ($this->isValidHexColor($color['value'])) {
                $rgb = $this->hexToRgb($color['value']);
                $distance = $this->colorDistance($targetRgb, $rgb);

                if ($distance <= $threshold && $distance > 0) {
                    $similar[] = array_merge($color, ['distance' => $distance]);
                }
            }
        }

        // 距離順でソート
        usort($similar, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return $similar;
    }

    /**
     * 色相ベースで自動色名を生成
     */
    public function generateColorName(string $colorValue): array
    {
        if (!$this->isValidHexColor($colorValue)) {
            return ['unknown'];
        }

        $hsl = $this->hexToHsl($colorValue);
        $hue = $hsl[0];
        $saturation = $hsl[1];
        $lightness = $hsl[2];

        $names = [];

        // 明度ベースの判定
        if ($lightness > 90) {
            $names[] = 'white';
        } elseif ($lightness < 10) {
            $names[] = 'black';
        } elseif ($saturation < 10) {
            if ($lightness > 70) $names[] = 'lightgray';
            elseif ($lightness > 30) $names[] = 'gray';
            else $names[] = 'darkgray';
        } else {
            // 色相ベースの判定
            if ($hue >= 0 && $hue < 15) $names[] = 'red';
            elseif ($hue >= 15 && $hue < 45) $names[] = 'orange';
            elseif ($hue >= 45 && $hue < 75) $names[] = 'yellow';
            elseif ($hue >= 75 && $hue < 105) $names[] = 'lime';
            elseif ($hue >= 105 && $hue < 135) $names[] = 'green';
            elseif ($hue >= 135 && $hue < 165) $names[] = 'mint';
            elseif ($hue >= 165 && $hue < 195) $names[] = 'cyan';
            elseif ($hue >= 195 && $hue < 225) $names[] = 'blue';
            elseif ($hue >= 225 && $hue < 255) $names[] = 'purple';
            elseif ($hue >= 255 && $hue < 285) $names[] = 'magenta';
            elseif ($hue >= 285 && $hue < 315) $names[] = 'pink';
            elseif ($hue >= 315 && $hue < 345) $names[] = 'rose';
            else $names[] = 'red';

            // 明度・彩度による修飾
            if ($lightness > 70) {
                $names[] = 'light' . $names[0];
            } elseif ($lightness < 30) {
                $names[] = 'dark' . $names[0];
            }

            if ($saturation < 30) {
                $names[] = 'muted' . $names[0];
            }

            // ブラウン系の特別処理
            if ($hue >= 20 && $hue <= 40 && $saturation >= 20 && $lightness >= 20 && $lightness <= 60) {
                $names[] = 'brown';
            }

            // ゴールド系の特別処理
            if ($hue >= 45 && $hue <= 60 && $saturation >= 40 && $lightness >= 40 && $lightness <= 80) {
                $names[] = 'gold';
            }
        }

        return array_unique($names);
    }

    /**
     * 色を追加
     */
    public function addColor(string $category, string $name, string $value, string $comment = ''): bool
    {
        // バックアップ作成
        $this->createBackup();

        // 新しい色情報
        $newColor = [
            'name' => $name,
            'value' => $value,
            'category' => $category,
            'comment' => $comment,
            'variable' => "--color-{$name}"
        ];

        // メモリ上の配列に追加
        $this->colors[] = $newColor;

        // ファイルに書き込み
        return $this->writeToFile();
    }

    /**
     * 色を更新
     */
    public function updateColor(string $oldName, string $newName, string $value, string $comment = ''): bool
    {
        $this->createBackup();

        foreach ($this->colors as &$color) {
            if ($color['name'] === $oldName) {
                $color['name'] = $newName;
                $color['value'] = $value;
                $color['comment'] = $comment;
                $color['variable'] = "--color-{$newName}";
                break;
            }
        }

        return $this->writeToFile();
    }

    /**
     * 色を削除
     */
    public function deleteColor(string $name): bool
    {
        $this->createBackup();

        $this->colors = array_filter($this->colors, fn($color) => $color['name'] !== $name);

        return $this->writeToFile();
    }

    /**
     * SCSSファイルに書き込み
     */
    private function writeToFile(): bool
    {
        $content = $this->generateScssContent();
        return file_put_contents($this->scssFile, $content) !== false;
    }

    /**
     * SCSS内容を生成
     */
    private function generateScssContent(): string
    {
        $content = "/* ベーススタイル(global) */\n";
        $content .= "/* color */\n";
        $content .= "//その場所でしか使わない繰り返さない色は直接定義してここには書かない(量が膨大になってしまうのを防ぐため)\n";
        $content .= "//\n";
        $content .= ":root{\n";

        // カテゴリー別にソート
        $categorizedColors = [];
        foreach ($this->colors as $color) {
            $categorizedColors[$color['category']][] = $color;
        }

        foreach ($this->categories as $key => $label) {
            if (!isset($categorizedColors[$key]) || empty($categorizedColors[$key])) {
                continue;
            }

            $content .= "    /* {$label} */\n";

            foreach ($categorizedColors[$key] as $color) {
                if (!empty($color['comment'])) {
                    $content .= "    //{$color['comment']}\n";
                }
                $content .= "    --color-{$color['name']}: {$color['value']};\n";
            }
            $content .= "\n";
        }

        $content .= "}\n";

        return $content;
    }

    /**
     * バックアップを作成
     */
    public function createBackup(): string
    {
        if (!is_dir(SCSS_BACKUP_DIR)) {
            mkdir(SCSS_BACKUP_DIR, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = SCSS_BACKUP_DIR . "{$timestamp}_color.scss";

        copy($this->scssFile, $backupFile);

        // 古いバックアップを削除
        $this->cleanupOldBackups();

        return $backupFile;
    }

    /**
     * 古いバックアップファイルを削除
     */
    private function cleanupOldBackups(): void
    {
        $files = glob(SCSS_BACKUP_DIR . '*_color.scss');
        if (count($files) <= SCSS_MAX_BACKUPS) {
            return;
        }

        // ファイル名でソート（日時順）
        usort($files, function($a, $b) {
            return filemtime($a) <=> filemtime($b);
        });

        // 古いファイルを削除
        $filesToDelete = array_slice($files, 0, count($files) - SCSS_MAX_BACKUPS);
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }

    /**
     * 全色一覧を取得
     */
    public function getAllColors(): array
    {
        return $this->colors;
    }

    /**
     * カテゴリー一覧を取得
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * カテゴリー別色一覧を取得
     */
    public function getColorsByCategory(): array
    {
        $categorized = [];
        foreach ($this->categories as $key => $label) {
            $categorized[$key] = array_filter($this->colors, fn($color) => $color['category'] === $key);
        }
        return $categorized;
    }

    /**
     * 重複チェック（同一カテゴリー内での色名重複）
     */
    public function checkDuplicate(string $category, string $name): bool
    {
        foreach ($this->colors as $color) {
            if ($color['category'] === $category && $color['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * 番号付きの色名を生成（重複時）
     */
    public function generateNumberedName(string $category, string $baseName): string
    {
        $name = $baseName;
        $counter = 2;

        while ($this->checkDuplicate($category, $name)) {
            $name = $baseName . $counter;
            $counter++;
        }

        return $name;
    }

    // ===== ユーティリティメソッド =====

    private function isValidHexColor(string $color): bool
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3}|[A-Fa-f0-9]{8})$/', $color);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    private function hexToHsl(string $hex): array
    {
        list($r, $g, $b) = $this->hexToRgb($hex);

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $diff = $max - $min;

        // 明度
        $l = ($max + $min) / 2;

        if ($diff === 0) {
            $h = $s = 0;
        } else {
            // 彩度
            $s = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);

            // 色相
            switch ($max) {
                case $r:
                    $h = (($g - $b) / $diff) + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $diff + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $diff + 4;
                    break;
            }
            $h /= 6;
        }

        return [
            round($h * 360),
            round($s * 100),
            round($l * 100)
        ];
    }

    private function colorDistance(array $rgb1, array $rgb2): float
    {
        return sqrt(
            pow($rgb1[0] - $rgb2[0], 2) +
            pow($rgb1[1] - $rgb2[1], 2) +
            pow($rgb1[2] - $rgb2[2], 2)
        );
    }
}

/**
 * CLI実行部分
 */
if (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === 'color.php') {
    $manager = new SCSSColorManager();

    if (count($argv) < 2) {
        echo "使用方法: php color.php <color_value>\n";
        exit(1);
    }

    $colorValue = $argv[1];
    $existing = $manager->searchByColor($colorValue);

    if (!empty($existing)) {
        echo "この色 ({$colorValue}) は既に登録されています:\n";
        foreach ($existing as $color) {
            echo "✓ {$color['variable']}: {$color['value']}";
            if (!empty($color['comment'])) {
                echo " ({$color['comment']})";
            }
            echo "\n";
        }
    } else {
        echo "この色 ({$colorValue}) は登録されていません。\n";
        $suggestions = $manager->generateColorName($colorValue);
        echo "色名候補: " . implode(', ', $suggestions) . "\n";
    }
}
?>