# mp記法 複数ページ管理システム

## ディレクトリ構成

```
scss/
├── global/                    # 既存のまま
│   ├── _index.scss
│   ├── _breakpoint.scss
│   ├── _color.scss
│   ├── _font.scss
│   └── ...
├── module/                    # 既存のまま
│   ├── _index.scss           # 削除または使用停止
│   ├── bl_header/
│   │   ├── _header.scss      # 汎用ヘッダー
│   │   └── _header2.scss     # 別バージョンヘッダー
│   ├── bl_kv/
│   │   ├── _kv.scss          # 汎用KV
│   │   ├── _kv2.scss         # 汎用KV別バージョン
│   │   └── _about.scss       # aboutページ専用KV
│   ├── bl_service/
│   │   └── _service.scss
│   ├── bl_service2/          # 2つ目のサービスモジュール
│   │   └── _service.scss     # クラス名: .bl_service2
│   └── ...
├── page/                     # 新規作成
│   ├── _common.scss          # 全ページ共通モジュール
│   ├── _top.scss             # トップページ用
│   ├── _about.scss           # aboutページ用
│   └── _contact.scss         # contactページ用
├── top.scss                  # トップページ エントリーポイント
├── about.scss                # aboutページ エントリーポイント
└── contact.scss              # contactページ エントリーポイント
```

## 実装例

### page/_common.scss
```scss
// 全ページで使用する共通モジュール
@use "../module/bl_header/header";
@use "../module/bl_footer/footer";
@use "../module/el_arrow/arrow";
@use "../module/bl_arrowLink/arrowLink";
```

### page/_top.scss
```scss
// トップページで使用するモジュール
@use "../module/bl_kv/kv";
@use "../module/bl_service/service";
@use "../module/bl_case/case";
@use "../module/bl_cta/cta";
@use "../module/bl_concept/concept";

// トップページ固有のスタイル
.ly_top_hero {
    padding: 60px 0;
    @include g.mq('pc') {
        padding: 120px 0;
    }
}

// 既存モジュールの微調整
.bl_kv {
    &.is_top {
        height: 100vh;
        min-height: 600px;
    }
}
```

### page/_about.scss
```scss
// aboutページで使用するモジュール
@use "../module/bl_kv/about" as kv;  // about専用KVを使用
@use "../module/bl_service2/service" as service2;
@use "../module/bl_concept/concept";

// aboutページ固有のスタイル
.about_timeline {
    margin: 80px 0;
    
    &__item {
        display: flex;
        gap: 20px;
        
        @include g.mq('tab') {
            gap: 40px;
        }
    }
}

// 他ページとは違う改変
.bl_concept {
    &.is_about {
        background: var(--color-bg-white3);
        
        .bl_concept__title {
            color: var(--color-font-green3);
        }
    }
}
```

### エントリーポイント例

#### top.scss
```scss
@charset "UTF-8";

// グローバル設定
@use "global";

// 共通モジュール
@use "page/common";

// トップページ固有
@use "page/top";
```

#### about.scss
```scss
@charset "UTF-8";

// グローバル設定
@use "global";

// 共通モジュール  
@use "page/common";

// aboutページ固有
@use "page/about";
```

## コンパイル設定

### DartJS Sass Compiler and Sass Watcher での設定方法（推奨）

あなたが既に使用されている DartJS Sass Compiler and Sass Watcher でページ別CSS生成を実現できます。

**settings.json 設定例：**

```json
{
  "dartsass.targetDirectory": "css",
  "dartsass.outputFormat": "cssonly", 
  "dartsass.autoPrefixBrowsersList": [
    "> 3% in JP",
    "ie 11", 
    "android 4.4",
    "last 1 versions"
  ],
  "dartsass.disableSourceMap": false,
  "dartsass.disableAutoPrefix": false
}
```

**複数エントリーポイントの監視方法：**

1. **フォルダ右クリック方式（基本）**
   - プロジェクトルートまたはscssフォルダを右クリック
   - 「DartSass: Sass Watch」を選択
   - 全ての.scssファイル（エントリーポイント）が自動監視される

2. **個別ファイル監視方式**
   - 各エントリーポイント（top.scss、about.scss等）を個別に右クリック
   - 「DartSass: Sass Watch」で個別監視開始

**重要なポイント：**

- **ファイル名規則**: エントリーポイントは`top.scss`、`about.scss`のように`_`なしで作成
- **パーシャルファイル**: `_`で始まるファイル（global/_index.scss等）は個別にコンパイルされない
- **自動監視**: エントリーポイントを保存すると、読み込んでいるパーシャルファイルも含めて自動でコンパイル

**実際の動作：**
1. `top.scss`を保存 → `css/top.css`が生成
2. `about.scss`を保存 → `css/about.css`が生成
3. `page/_top.scss`（パーシャル）を変更 → `top.scss`を保存すると反映

**パーシャルファイル変更時の自動コンパイル設定：**

より快適に使用するには、パーシャルファイル変更時も自動コンパイルする設定が可能です：

1. プロジェクトルートでsassをインストール：
```bash
npm install sass
```

2. settings.jsonに追加：
```json
{
  "dartsass.sassBinPath": "node_modules/sass/sass.js"
}
```

これで、パーシャルファイルを変更・保存するだけで自動的に関連するCSSが更新されます。

### 出力結果
- `css/top.css` - トップページ用CSS（global + common + top）
- `css/about.css` - aboutページ用CSS（global + common + about）
- `css/contact.css` - contactページ用CSS（global + common + contact）

### 動作確認手順
1. VS Code下部に「Sass Watchers: X」の表示があることを確認
2. エントリーポイント（top.scss等）を保存してテスト
3. cssフォルダに対応するCSSファイルが生成されることを確認

## 運用フロー

### 新しいページを追加する場合
1. `page/_newpage.scss` を作成
2. 必要なモジュールを@useで選択
3. ページ固有スタイルを記述
4. `newpage.scss` エントリーポイントを作成

### 新しいモジュールを作成する場合
1. `module/bl_newmodule/` フォルダを作成
2. `_newmodule.scss` を作成
3. 必要なページの `page/_xxx.scss` で@useして読み込み

### モジュールのバリエーションを作成する場合
1. 同一機能：`bl_kv/_kv2.scss` として追加
2. 別機能：`bl_newmodule2/` として新規作成