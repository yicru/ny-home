(function() {
  // 1. 遅延読み込みのためのスタイルをインラインで追加
  const style = document.createElement('style');
  style.textContent = `
    .lazy-loading {
      opacity: 0;
      transition: opacity 0.3s;
    }
    .lazy-loaded {
      opacity: 1;
    }
    .blur-up {
      filter: blur(5px);
      transition: filter 0.3s;
    }
    .blur-up.lazy-loaded {
      filter: blur(0);
    }
  `;
  document.head.appendChild(style);

  // 設定オプション
  const config = {
    // 基本設定
    rootMargin: '200px 0px',
    threshold: 0.01,
    
    // 自動除外の閾値
    autoExclude: {
      minFileSize: 5000,        // 5KB未満の画像は除外
      maxWidth: 100,            // 100px未満の幅の画像は除外  
      maxHeight: 100,           // 100px未満の高さの画像は除外
      foldThreshold: 1.5,       // ファーストビューの1.5倍までは除外
    },
    
    // 確実に除外するセレクタ
    forceExcludeSelectors: [
      'header img',
      '.header img', 
      '#header img',
      '.logo img',
      '.site-logo img',
      '.brand-logo img',
      'nav img',
      '.navigation img',
      '.menu img',
      '[class*="logo"] img',
      '[class*="brand"] img',
      '[class*="icon"] img',
      '.btn img',
      '.button img',
      // 装飾画像の一般的なクラス
      '.decoration img',
      '.ornament img',
      '[class*="tree"] img',
      '[class*="plant"] img',
      '[class*="background"] img',
      // 小さな装飾要素
      '.arrow img',
      '.separator img',
      '[width="16"] img',
      '[width="24"] img',
      '[width="32"] img',
      '[height="16"] img', 
      '[height="24"] img',
      '[height="32"] img',
    ],
    
    // 確実に除外するURLパターン
    excludeUrlPatterns: [
      '/logo.',
      '/icon.',
      '/arrow.',
      '/separator.',
      '/tree.',
      '/plant.',
      '/decoration.',
      '/ornament.',
      '-icon.',
      '-logo.',
      '-arrow.',
      '-separator.',
    ],
    
    // デバッグモード
    debug: false
  };

  // デバッグ用ログ関数
  function debugLog(message, data = null) {
    if (config.debug) {
      console.log(`[LazyLoad Debug] ${message}`, data || '');
    }
  }

  // 2. より厳密なviewport判定
  function isInViewportStrict(el) {
    const rect = el.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    const windowWidth = window.innerWidth || document.documentElement.clientWidth;
    
    // 少し余裕を持たせて判定（ファーストビューの1.5倍まで）
    const threshold = windowHeight * config.autoExclude.foldThreshold;
    
    return (
      rect.top >= -100 &&  // 少し上まで許容
      rect.left >= -100 && // 少し左まで許容
      rect.top <= threshold &&
      rect.left <= windowWidth + 100 // 少し右まで許容
    );
  }

  // 3. 画像の自動除外判定
  function shouldExcludeImage(img) {
    // 明示的な除外指定がある場合
    if (img.hasAttribute('loading') || 
        img.hasAttribute('data-no-lazy') || 
        img.classList.contains('no-lazy') ||
        img.dataset.src) {
      debugLog('除外: 明示的指定', img.src);
      return true;
    }

    // セレクタによる除外
    const isExcludedBySelector = config.forceExcludeSelectors.some(selector => {
      try {
        return img.matches(selector) || img.closest(selector.replace(' img', ''));
      } catch (e) {
        return false;
      }
    });
    
    if (isExcludedBySelector) {
      debugLog('除外: セレクタマッチ', img.src);
      return true;
    }

    // URLパターンによる除外
    if (img.src) {
      const isExcludedByUrl = config.excludeUrlPatterns.some(pattern => 
        img.src.toLowerCase().includes(pattern.toLowerCase())
      );
      
      if (isExcludedByUrl) {
        debugLog('除外: URLパターン', img.src);
        return true;
      }
    }

    // 画像サイズによる除外
    const computedWidth = img.offsetWidth || parseInt(img.getAttribute('width')) || 0;
    const computedHeight = img.offsetHeight || parseInt(img.getAttribute('height')) || 0;
    
    if ((computedWidth > 0 && computedWidth < config.autoExclude.maxWidth) ||
        (computedHeight > 0 && computedHeight < config.autoExclude.maxHeight)) {
      debugLog('除外: サイズが小さい', { src: img.src, width: computedWidth, height: computedHeight });
      return true;
    }

    // ファーストビュー内の画像は除外
    if (isInViewportStrict(img)) {
      debugLog('除外: ファーストビュー内', img.src);
      return true;
    }

    // aria-hidden="true" の装飾画像は除外
    if (img.getAttribute('aria-hidden') === 'true') {
      debugLog('除外: aria-hidden装飾画像', img.src);
      return true;
    }

    // alt属性が空の装飾画像は除外
    if (img.getAttribute('alt') === '') {
      debugLog('除外: alt空の装飾画像', img.src);
      return true;
    }

    return false;
  }

  // 4. 安全な画像変換
  function convertImageSafely(img) {
    if (!img.src || img.src.indexOf('data:') === 0) {
      return false;
    }

    try {
      // 元のsrcを保存
      img.dataset.src = img.src;
      
      // プレースホルダーを設定
      img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E';
      
      // クラスを追加
      img.classList.add('lazy-loading');
      
      debugLog('変換成功', img.dataset.src);
      return true;
    } catch (error) {
      debugLog('変換失敗', { src: img.src, error: error.message });
      return false;
    }
  }

  // 5. メイン画像処理
  function setupLazyLoading() {
    // 基本的な除外条件を適用した画像を取得
    const images = document.querySelectorAll('img:not([loading="lazy"]):not([loading="eager"]):not([data-no-lazy]):not(.no-lazy):not([data-src])');
    
    debugLog(`対象画像数: ${images.length}`);

    // 自動除外判定を適用
    const targetImages = Array.from(images).filter(img => !shouldExcludeImage(img));
    
    debugLog(`遅延読み込み対象: ${targetImages.length}`);

    // 対象画像を遅延読み込み用に変換
    targetImages.forEach(img => {
      convertImageSafely(img);
    });

    // Intersection Observer を設定
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            
            if (img.dataset.src) {
              // 本来の画像を読み込む
              const newImg = new Image();
              newImg.onload = () => {
                img.src = img.dataset.src;
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                debugLog('読み込み完了', img.src);
              };
              newImg.onerror = () => {
                debugLog('読み込みエラー', img.dataset.src);
                img.classList.remove('lazy-loading');
              };
              newImg.src = img.dataset.src;
            }
            
            // この画像の監視を解除
            imageObserver.unobserve(img);
          }
        });
      }, {
        rootMargin: config.rootMargin,
        threshold: config.threshold
      });
      
      // 変換された画像のみ監視
      document.querySelectorAll('img[data-src].lazy-loading').forEach(img => {
        imageObserver.observe(img);
      });
    } 
    // フォールバック（Intersection Observer非対応ブラウザ用）
    else {
      document.querySelectorAll('img[data-src].lazy-loading').forEach(img => {
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.classList.remove('lazy-loading');
          img.classList.add('lazy-loaded');
        }
      });
    }
  }

  // 6. 背景画像の遅延読み込み
  function setupBackgroundLazyLoading() {
    const bgElements = document.querySelectorAll('[data-bg]:not([data-no-lazy]):not(.no-lazy)');
    
    if ('IntersectionObserver' in window) {
      const bgObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            el.style.backgroundImage = `url(${el.dataset.bg})`;
            el.classList.add('lazy-loaded');
            bgObserver.unobserve(el);
          }
        });
      }, {
        rootMargin: config.rootMargin,
        threshold: config.threshold
      });
      
      bgElements.forEach(el => {
        bgObserver.observe(el);
      });
    } else {
      bgElements.forEach(el => {
        el.style.backgroundImage = `url(${el.dataset.bg})`;
      });
    }
  }

  // 7. 初期化とタイミング制御
  function init() {
    debugLog('Lazy loading 初期化開始');
    
    // 少し遅延させて、他のスクリプトの影響を避ける
    setTimeout(() => {
      setupLazyLoading();
      setupBackgroundLazyLoading();
      debugLog('Lazy loading 初期化完了');
    }, 100);
    
    // 動的に追加された画像に対応
    if ('MutationObserver' in window) {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
              if (node.nodeType === Node.ELEMENT_NODE) {
                const newImages = node.querySelectorAll ? 
                  node.querySelectorAll('img:not([data-src]):not(.lazy-loading):not(.lazy-loaded)') : 
                  [];
                if (newImages.length > 0) {
                  setTimeout(() => setupLazyLoading(), 100);
                }
              }
            });
          }
        });
      });
      
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }

  // ページが読み込まれたら実行
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // グローバルに設定を公開（デバッグ用）
  window.LazyLoadConfig = config;
  window.LazyLoadDebug = {
    enable: () => { config.debug = true; console.log('LazyLoad デバッグモード有効'); },
    disable: () => { config.debug = false; },
    reload: () => { location.reload(); }
  };

})();