<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「(店舗用)マイページ ');
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
// DBから自分が出品した商品データを取得
$productData = getMyProducts($a_id);
// DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBordShop($a_id);

debug('（店舗用）取得した出品した商品データ：'.print_r($productData, true));
debug('(店舗用)取得した掲示板データ（$bordData）：'.print_r($bordData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ（出店用）';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php
    require('header_shop.php');
  ?>

  <p class="fn-showMsg msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <main>
      <div class="contents-outer">

        <!-- メインコンテンツエリア -->
        <section class="mainContents-wrapper">
          <div class="productsWrapper">
            <h2 class="form-ttl as_products">登録商品一覧</h1>
            <div class="productList-wrapper">
              <?php
                if(!empty($productData)):
                  foreach ($productData as $key => $val):
                    if($key > 3){
                      break;
                    }
              ?>
              <div class="productItem">
                <a href="registProduct_shop.php?p_id=<?php echo $val['id']; ?>">
                  <figure>
                    <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                  </figure>
                  <h3 class="productItem-ttl"><?php echo sanitize($val['name']); ?></h3>
                  <p class="productItem-price">¥<?php echo sanitize($val['price']); ?></p>
                </a>
              </div>
              <?php
                  endforeach;
                endif;
              ?>
            </div>
          </div>


          <div class="productsWrapper">
            <h2 class="form-ttl as_products">連絡掲示板一覧</h1>
            <div class="productList-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>最新送信日時</th>
                    <th>取引相手</th>
                    <th>メッセージ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                      if(!empty($bordData)){
                        foreach ($bordData as $key => $val) {
                          if(!empty($val['msg'])){
                            $msg = array_shift($val['msg']);
                  ?>
                      <tr>
                        <td><?php echo sanitize($msg['send_date']); ?></td>
                        <td><?php echo sanitize($val['username']); ?></td>
                        <td><a href="msg_shop.php?b_id=<?php echo sanitize($val['id']);?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?>...</a></td>
                      </tr>
                  <?php
                      }else{
                  ?>
                      <tr>
                        <td>--</td>
                        <td><?php echo sanitize($val['username']); ?></td>
                        <td><a href="msg_shop.php?b_id=<?php echo sanitize($val['id']);?>">まだメッセージはありません</a></td>
                      </tr>
                  <?php
                          }
                        }
                      }
                  ?>
                </tbody>
              </table>
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
