<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
// ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
$u_id = $_SESSION['user_id'];
// DBから自分が購入した商品データを取得
$purchaseData = getMyPurchased($u_id);
// DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBord($u_id);
// DBからお気に入りデータを取得
$likeData = getMyLike($u_id);

debug('取得した購入した商品データ：'.print_r($purchaseData, true));
debug('取得した掲示板データ：'.print_r($bordData, true));
debug('取得したお気に入りデータ：'.print_r($likeData, true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>

  <p class="fn-showMsg msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <main>
      <div class="contents-outer">

        <!-- メインコンテンツエリア -->
        <section class="mainContents-wrapper">
          <div class="productsWrapper">
            <h2 class="form-ttl as_products">購入商品一覧</h1>
            <div class="productList-wrapper">
              <?php
                if(!empty($purchaseData)):
                  foreach ($purchaseData as $key => $val):
                    if($key > 3){
                      break;
                    }
              ?>
              <div class="productItem">
                <a href="productDetail.php?p_id=<?php echo $val['id']; ?>">
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
                        <td><?php echo sanitize($val['author']); ?></td>
                        <td><a href="msg.php?b_id=<?php echo sanitize($val['id']);?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?>...</a></td>
                      </tr>
                  <?php
                      }else{
                  ?>
                      <tr>
                        <td>--</td>
                        <td>○○　○○</td>
                        <td><a href="msg.php?b_id=<?php echo sanitize($val['id']);?>">まだメッセージはありません</a></td>
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


          <div class="productsWrapper">
            <h2 class="form-ttl as_products">お気に入り一覧</h1>
            <div class="productList-wrapper">
              <?php
                  if(!empty($likeData)):
                    foreach ($likeData as $key => $val):
              ?>
              <div class="productItem">
                <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                  <figure>
                    <img src="<?php echo sanitize($val['pic1']); ?>" alt="">
                  </figure>
                  <h3 class="productItem-ttl"><?php echo sanitize($val['name']); ?></h3>
                  <p class="productItem-price">¥<?php echo sanitize(number_format($val['price'])); ?></p>
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
        require('sidebar_mypage.php');
        ?>

      </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
