<?php
// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「商品詳細ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// var_dump($_SESSION['user_id']);
//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから指定した商品データを取得
$viewData = getProductOne($p_id);
// DBから同じ作り手の商品データを取得
$seriesData = getProductSeries($viewData['author_id']);
// DBから作り手の全データを取得
$dbAuthorData = getAuthorData();
// DBからカテゴリーの全データを取得
$dbCategoryData = getCategory();
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生：指定ページに不正な値が入りました。');
  header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：'.print_r($viewData,true));

// post送信されていた場合
if(!empty($_POST['submit'])){
  debug('POST送信があります');

  // ログイン認証
  require('auth.php');

  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'INSERT INTO bord (sale_user, buy_user, product_id, create_date) VALUES (:s_uid, :b_uid, :p_id, :date)';
    $data = array(':s_uid' => $viewData['author_id'], ':b_uid' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    if($stmt){
      $_SESSION['msg_success'] = SUC05;
      debug('連絡掲示板に遷移します');
      header("Location:msg.php?b_id=".$dbh->lastInsertID()); //連絡掲示板へ
    }
  }catch(Exception $e){
    error_log('エラー発生：'. $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品詳細';
require('head.php');
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>
  <main>
    <div class="contents-wrapper">
      <div class="contents-outer">

        <!-- indexのサイドバー -->
        <?php
        require('sidebar_index.php');
        ?>

        <!-- detaiItemモジュール -->
        <section class="detailItem-wrapper main-wrapper">
          <div class="detailItem-ttlWrapper">
            <h1 class="detailItem-ttl"><span class="detailItem-shop"><?php echo sanitize($viewData['author']); ?></span><?php echo sanitize($viewData['name']);?></h1>
          </div>
          <i class="fas fa-heart icon-like fn-clike-like <?php if(!empty($_SESSION['user_id'])){ if(isLike($_SESSION['user_id'], $viewData['id'])){ echo 'active'; }}  ?>" aria-hidden="true" data-productid= "<?php echo sanitize($viewData['id']); ?>"></i>
          <figure class="detailItem-imageWrapper">
            <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="<?php echo sanitize($viewData['name']);?>">
          </figure>
          <div class="detailItem-txtWrapper">
            <div class="detailItem-infoWrapper">
              <h1 class="detaiItem-ttl-s"><?php echo sanitize($viewData['name']);?></h1>
              <p class="detailItem-copy"><?php echo sanitize($viewData['copy']);?></p>
              <p class="detailItem-desc"><?php echo sanitize($viewData['comment']);?></p>
            </div>
            <form class="" action="" method="post">
              <div class="detailItem-cartWrapper">
                <h1 class="detaiItem-ttl-m"><?php echo sanitize($viewData['name']);?></h1>
                <p class="detailItem-price"><?php echo sanitize(number_format($viewData['price']));?>円<span>（税別）</span></p>
                <dl class="detaiItem-desc">
                  <div class="detaiItem-desc-item">
                    <dt>容量</dt>
                    <dd>100g×1パック</dd>
                  </div>
                  <div class="detaiItem-desc-item">
                    <dt>数量</dt>
                    <dd><input class="input-count" type="text" name=""/>点</dd>
                  </div>
                </dl>
                <input type="submit" name="submit" value="商品を購入" class="input-submit">
              </div>
            </form>
          </div>

          <!-- 同じ作り手の商品始まり -->
          <div class="detailItem-seriesWrapper">
            <h2 class="detailItem-seriesTtl">同じ作り手の商品</h2>
            <div class="seriesItem-wrapper">
              <?php
                foreach ($seriesData as $key => $val) {
                  if($key > 3){
                    break;
                  }
              ?>

              <div class="seriesItem">
                <img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']);?>">
              </div>

              <?php
                }
              ?>

            </div>
          </div>
          <a href="index.php<?php echo appendGetParam(array('p_id')); ?>" class="listBtn">&lt;商品一覧に戻る</a>


          <!-- detaiItemモジュールおわり -->
        </section>
      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
