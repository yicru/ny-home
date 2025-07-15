# MP (Module Pallet) 統合ツール v1.0.0 使用方法

Module Pallet（MP）は、PHPプロジェクトの効率的な開発をサポートする統合ツールです。モジュールベースの開発フローを効率化し、静的HTMLファイルの生成まで一貫してサポートします。

## 概要

MPツールは以下の3つの主要機能を提供します：

- **Export**: PHPファイルを静的HTMLファイルに変換（GETパラメータ対応）
- **Init**: modules.txtからpartsディレクトリを一括初期化
- **Add**: 単一モジュールの追加

## ディレクトリ構成

```
project/
├── mp/
│   ├── mp.php              # MP統合ツール
│   └── config/
│       ├── export-config.json  # HTML変換設定
│       └── modules.txt         # モジュール一覧
├── parts/
│   ├── base.php           # モジュールテンプレート
│   ├── bl_header/         # ヘッダーモジュール
│   │   ├── header.php
│   │   └── header2.php
│   ├── bl_kv/            # キービジュアルモジュール
│   │   ├── kv.php
│   │   └── kv-single.php
│   └── bl_footer/        # フッターモジュール
│       └── footer.php
├── index.php             # メインPHPファイル
└── dist/                 # 生成されたHTMLファイル（任意）
```

## 基本的な使用方法

### コマンド構文

```bash
php mp.php <command> [options]
```

### コマンド構文

```bash
php mp/mp.php <command> [options]
```

### 利用可能なコマンド

```bash
php mp/mp.php help      # ヘルプを表示
php mp/mp.php version   # バージョン情報を表示
php mp/mp.php export    # HTML変換
php mp/mp.php init      # プロジェクト初期化
php mp/mp.php add       # モジュール追加
```

### 詳細ヘルプ

各コマンドの詳細ヘルプを表示：

```bash
php mp/mp.php export --help
php mp/mp.php init --help
php mp/mp.php add --help
```

## Export機能（HTML変換）

PHPファイルを静的HTMLファイルに変換します。GETパラメータを含むページにも対応。

### 基本的な使用方法

```bash
# デフォルト設定で実行
php mp/mp.php export

# 出力ディレクトリを指定
php mp/mp.php export -d dist/

# 設定ファイルを指定
php mp/mp.php export -c custom-config.json
```

### 初期設定

```bash
# サンプル設定ファイルを生成
php mp/mp.php export --init
```

これにより `mp/config/export-config.json` が生成されます。

### 設定ファイル形式

`mp/config/export-config.json` の例：

```json
{
  "exports": [
    {
      "input": "../index.php",
      "output": "index.html",
      "params": {}
    },
    {
      "input": "../index.php",
      "output": "post-15.html",
      "params": {
        "p": "15"
      }
    },
    {
      "input": "../index.php",
      "output": "category-news.html",
      "params": {
        "cat": "news"
      }
    }
  ]
}
```

### 設定項目詳細

- **input**: 変換元のPHPファイル（mpディレクトリからの相対パス）
- **output**: 出力するHTMLファイル名
- **params**: GETパラメータ（オブジェクト形式）

### GETパラメータの例

**投稿ページ**
```json
{
  "input": "../index.php",
  "output": "post-15.html",
  "params": {"p": "15"}
}
```

**カテゴリページ**
```json
{
  "input": "../category.php",
  "output": "category-news.html", 
  "params": {"cat": "news"}
}
```

**複雑なパラメータ**
```json
{
  "input": "../archive.php",
  "output": "archive-2024-12.html",
  "params": {
    "year": "2024",
    "month": "12",
    "page": "2"
  }
}
```

## Init機能（プロジェクト初期化）

modules.txtファイルを読み込んで、partsディレクトリを一括で初期化します。

### 基本的な使用方法

```bash
# デフォルト設定で実行
php mp/mp.php init

# modules.txtファイルを指定
php mp/mp.php init -f custom-modules.txt
```

### 初期設定

```bash
# サンプルmodules.txtを生成
php mp/mp.php init --sample
```

これにより `mp/config/modules.txt` が生成されます。

### modules.txtの形式

`mp/config/modules.txt` の例：

```
bl_header/header
bl_header/header2
bl_kv/kv
bl_kv/kv-single
bl_cta/top-kvBottom-cta
bl_cta/cta
bl_footer/footer
```

### フォーマット規則

- 1行につき1つのモジュール
- `モジュール名/ファイル名` 形式（.php拡張子は不要）
- 空行は無視される
- `#` で始まる行はコメントとして無視される

### 実行結果

```bash
プロジェクト初期化開始...
parts ディレクトリ: /path/to/project/parts/
--------------------------------------------------
作成: bl_header/header.php
作成: bl_header/header2.php
作成: bl_kv/kv.php
スキップ: 既存 - bl_kv/kv-single.php
作成: bl_cta/top-kvBottom-cta.php
作成: bl_cta/cta.php
作成: bl_footer/footer.php
--------------------------------------------------
初期化完了: 6ファイル作成
```

## Add機能（モジュール追加）

開発中に新しいモジュールを追加する際に使用します。

### 基本的な使用方法

```bash
# 新しいモジュールを追加
php mp/mp.php add bl_news/news-list
php mp/mp.php add bl_gallery/gallery-single
php mp/mp.php add bl_sidebar/widget-recent
```

### コマンド形式

```bash
php mp/mp.php add <モジュール名>/<ファイル名>
```

- モジュール名: ディレクトリ名（bl_news など）
- ファイル名: 作成するPHPファイル名（.php拡張子は不要）

### 実行結果

```bash
モジュール追加: bl_news/news-list.php
ディレクトリ作成: bl_news/
成功: bl_news/news-list.php を作成しました
```

### 既存ファイルの場合

```bash
モジュール追加: bl_header/header.php
警告: ファイルが既に存在します - /path/to/parts/bl_header/header.php
```

## テンプレート機能

### base.phpテンプレート

新しく作成されるモジュールファイルは、`parts/base.php` の内容をテンプレートとして使用します。

`parts/base.php` の例：
```php
<?php
//最後にインデントを合わせるために、バッファリングを開始する
auto_indent_start();
?>
<?php //ここにモジュールの内容 ?><?php //余計な改行、インデントを防ぐためのphpタグ開始位置
//インデントを付けて書き出し
auto_indent_end( true , $indent, $current_indent, $indent_level);
?>
```

### テンプレートが存在しない場合

`parts/base.php` が存在しない場合は、空のファイルが作成されます。

## 実用的なワークフロー

### 1. 新規プロジェクト開始

```bash
# 1. プロジェクト要件からmodules.txtを作成
php mp/mp.php init --sample

# 2. modules.txtを編集

# 3. 初期モジュールを一括作成
php mp/mp.php init

# 4. 開発開始...
```

### 2. 開発中のモジュール追加

```bash
# 新しいモジュールが必要になった場合
php mp/mp.php add bl_testimonial/testimonial-card
php mp/mp.php add bl_testimonial/testimonial-list

# すぐに開発開始...
```

### 3. 静的ファイル生成

```bash
# 1. export設定ファイルを準備
php mp/mp.php export --init

# 2. export-config.jsonを編集

# 3. HTML生成
php mp/mp.php export

# 4. 特定ディレクトリに出力
php mp/mp.php export -d dist/
```

## よくある使用例

### ブログサイト

**modules.txt**
```
bl_header/header
bl_kv/hero
bl_post/post-list
bl_post/post-single
bl_sidebar/widget-recent
bl_sidebar/widget-category
bl_pagination/pagination
bl_footer/footer
```

**export-config.json**
```json
{
  "exports": [
    {"input": "../index.php", "output": "index.html", "params": {}},
    {"input": "../index.php", "output": "post-1.html", "params": {"p": "1"}},
    {"input": "../index.php", "output": "post-2.html", "params": {"p": "2"}},
    {"input": "../index.php", "output": "category-news.html", "params": {"cat": "news"}},
    {"input": "../archive.php", "output": "archive-2024.html", "params": {"year": "2024"}}
  ]
}
```

### 企業サイト

**modules.txt**
```
bl_header/header
bl_kv/hero
bl_about/company-info
bl_service/service-list
bl_service/service-detail
bl_news/news-list
bl_contact/contact-form
bl_footer/footer
```

## エラーハンドリング

### よくあるエラーと対処法

**設定ファイルが見つからない**
```bash
エラー: 設定ファイルが見つかりません: mp/config/export-config.json
```
→ `php mp/mp.php export --init` でサンプルファイルを生成

**modules.txtが見つからない**
```bash
エラー: modules.txtが見つかりません: mp/config/modules.txt
```
→ `php mp/mp.php init --sample` でサンプルファイルを生成

**無効なモジュール形式**
```bash
エラー: 無効な形式です。module_name/file_name の形式で指定してください
```
→ `bl_news/news-list` のようにスラッシュで区切って指定

**JSONが無効**
```bash
エラー: 設定ファイルのJSONが無効です: mp/config/export-config.json
```
→ JSON形式を確認（カンマ、括弧、クォートなど）

## コマンドオプション一覧

### Export

| オプション | 短縮形 | 説明 | 例 |
|-----------|--------|------|-----|
| --help | -h | ヘルプを表示 | `php mp/mp.php export -h` |
| --init | - | サンプル設定ファイルを生成 | `php mp/mp.php export --init` |
| --config | -c | 設定ファイルを指定 | `php mp/mp.php export -c my-config.json` |
| --dir | -d | 出力ディレクトリを指定 | `php mp/mp.php export -d dist/` |

### Init

| オプション | 短縮形 | 説明 | 例 |
|-----------|--------|------|-----|
| --help | -h | ヘルプを表示 | `php mp/mp.php init -h` |
| --sample | - | サンプルmodules.txtを生成 | `php mp/mp.php init --sample` |
| --file | -f | modules.txtファイルを指定 | `php mp/mp.php init -f my-modules.txt` |

### Add

| オプション | 短縮形 | 説明 | 例 |
|-----------|--------|------|-----|
| --help | -h | ヘルプを表示 | `php mp/mp.php add -h` |

## 注意事項

1. **mpディレクトリでの実行**: 必ず `mp/` ディレクトリ内で `php mp.php` を実行してください
2. **相対パス**: 設定ファイル内のパスはmpディレクトリからの相対パスで指定します
3. **ファイル上書き**: 既存ファイルは上書きされないよう保護されています
4. **GETパラメータ**: Export実行時、各変換後にGETパラメータは自動でクリアされます
5. **権限**: ディレクトリ・ファイル作成に必要な権限があることを確認してください

## バージョン履歴

- **v1.0.0**: 初回リリース
  - Export機能（GETパラメータ対応）
  - Init機能（modules.txt一括初期化）
  - Add機能（単一モジュール追加）
  - 統合コマンドライン形式
  - 設定ファイルのmp/config/ディレクトリ集約

## サポート

Module Pallet（MP）ツールは、モジュールベースのPHP開発を効率化するために設計されています。各機能を組み合わせることで、開発から静的ファイル生成まで一貫したワークフローを実現できます。