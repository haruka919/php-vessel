<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「販売履歴ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
// ログイン認証
require('auth_shop.php');

// 画面表示用データ取得
//================================
$a_id = $_SESSION['author_id'];

// 売れた商品の情報を取得
$soldProductData = getSoldProduct($a_id);

debug('取得した販売した商品データ：'.print_r($soldProductData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '販売履歴';
require('head.php');
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header_shop.php');
  ?>

  <main>
      <div class="contents-outer">

        <!-- メインコンテンツエリア -->
        <section class="mainContents-wrapper">
          <div class="productsWrapper">
            <h2 class="form-ttl as_products">販売履歴一覧</h1>

            <div class="productList-wrapper">

              <?php
                if(!empty($soldProductData)):
                  foreach ($soldProductData as $key => $val):
              ?>

              <div class="productItem">
                <a href="productDetail.php?p_id=<?php echo sanitize($val['id']); ?>">
                  <figure>
                    <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                  </figure>
                  <h3 class="productItem-ttl"><?php echo sanitize($val['name']); ?></h3>
                  <p class="productItem-price">¥<?php echo number_format(sanitize($val['price'])); ?></p>
                </a>
              </div>

              <?php
                  endforeach;
                endif;
              ?>

            </div>
          </div>

        </section>

        <!-- ログイン後サイドバー -->
        <?php
        require('sidebar_mypage_shop.php');
        ?>

      </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
