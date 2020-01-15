<?php
// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「(店舗用)掲示板ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
$saleUserId = '';
$saleUserInfo = '';
$buyUserId = '';
$buyUserInfo = '';
$productInfo = '';  // 購入した商品情報を取得
// 画面表示用データ取得
//================================
// GETパラメータを取得
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
// DBから掲示板とメッセージデータを取得
debug('取得した(0)DBデータ：'.print_r($b_id, true));

$viewData = getMsgsAndBord($b_id);
debug('取得した(1)掲示板データ：'.print_r($viewData, true));
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生：指定ページに不正な値が入りました。');
  header("Location:mypage_shop.php"); //マイページへ
}
$buyUserId = $viewData[0]['buy_user'];
// 売れた商品情報を取得
$productInfo = getProductOne($viewData[0]['product_id']);
debug('取得した(2)売れた商品データ：'.print_r($productInfo,true));
// 商品情報が入っているかチェック
if(empty($productInfo)){
  error_log('エラー発生：商品情報が取得できませんでした。');
  header("Location:mypage_shop.php"); //マイページへ
}
// DBから店の情報を取得
$saleUserInfo = getAuthor($_SESSION['author_id']);
// DBから購入者のユーザー情報を取得
$buyUserInfo = getUser($buyUserId);
debug('取得した(4)購入者のユーザー情報：'.print_r($buyUserInfo,true));
// 自分のユーザー情報が取れたかチェック
if(empty($buyUserInfo)){
  error_log('エラー発生：購入者のユーザー情報が取得できませんでした。');
  header("Location:mypage_shop.php"); //マイページへ
}

// post送信された場合
if(!empty($_POST)){
  debug('POST送信があります。');

  // ログイン認証
  require('auth_shop.php');

  // バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  // 最大文字数チェック
  validMaxLen($msg, 'msg', 500);
  // 未入力チェック
  validRequired($msg, 'msg');

  if(empty($err_msg)){
    debug('バリデーションOKです。');
    // 例外処理
    try{
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'INSERT INTO message (send_date, to_user, from_user, msg, bord_id, create_date) VALUES (:send_date, :to_user, :from_user, :msg, :b_id, :date)';
      $data = array(':send_date' => date('Y-m-d H:i:s'), ':to_user' => $_SESSION['author_id'], ':from_user' => $buyUserId, ':msg' => $msg, ':b_id' => $b_id, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        $_POST = array(); //postをクリア
        debug('連絡掲示板に遷移します。');
        header("Location:".$_SERVER['PHP_SELF'].'?b_id='.$b_id); //自分自身に遷移する
      }
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '掲示板ページ';
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
      <div class="bord-wrapper">
        <div class="bordInfo-wrapper">
          <div class="buyUser-wrapper">
            <h3>お客様情報</h3>
            <div class="buyUser-outer">
              <img src="<?php echo sanitize($buyUserInfo['pic']); ?>" alt="" class="buyUser-pic">
              <ul class="buyUser-inner">
                <li class="buyUser-inner-info buyUser-name"><?php echo sanitize($buyUserInfo['username']); ?><span>様</span></li>
                <li class="buyUser-inner-info buyUser-Ip"><span class="fontBold">[郵便番号]</span>〒<?php echo sanitize($buyUserInfo['zip']); ?></li>
                <li class="buyUser-inner-info buyUser-addr"><span class="fontBold">[ご住所]</span><?php echo sanitize($buyUserInfo['addr']); ?></li>
                <li class="buyUser-inner-info buyUser-tel"><span class="fontBold">[電話番号]</span><?php echo sanitize($buyUserInfo['tel']); ?></li>
              </ul>
            </div>
          </div>
          <div class="buyItem-wrapper">
            <h3>購入商品</h3>
            <div class="buyItem-outer">
              <figure class="buyItem-pic">
                <img src="<?php echo sanitize($productInfo['pic1']); ?>" alt="">
              </figure>
                <ul class="buyItem-inner">
                  <li class="buyItem-name"><?php echo sanitize($productInfo['name']); ?></li>
                  <li class="buyItem-price">￥<?php echo sanitize(number_format($productInfo['price'])); ?><span>（税込）</span></li>
                  <li class="buyItem-date"><?php echo date('Y/m/d', strtotime(sanitize($viewData[0]['create_date']))); ?></li>
                </ul>
            </div>
          </div>
        </div>

        <div class="msgBord-wrapper">
          <div class="msgBord-outer">
            <ul class="msgBord fn_scroll-bottom">
              <?php
                if(!empty($viewData)){
                  foreach ($viewData as $key => $val){
                    if(!empty($val['to_user']) && $val['to_user'] == $_SESSION['author_id']){
                    // form_user(受信者)が客、to_user(送信者)が店だったら、
              ?>
              <!-- （送信(send)が店、右側） -->
              <li class="sendTalk msgBord-talk">
                <div class="sendTalk-pic">
                  <img class="" src="<?php echo sanitize($saleUserInfo['pic']); ?>">
                  <p class="talkTime"><?php echo sanitize($val['send_date']); ?></p>
                </div>
                <div class="sendTalk-baloon baloon">
                  <p class="sendTalk-voice"><?php echo sanitize($val['msg']); ?></p>
                </div>
              </li>

              <?php
                  }else{
              ?>
              <!--（受信が客） -->
              <li class="reseiveTalk msgBord-talk">
                <div class="reseiveTalk-pic">
                  <img class="" src="<?php echo sanitize($buyUserInfo['pic']); ?>">
                  <p class="talkTime"><?php echo sanitize($val['send_date']); ?></p>
                </div>
                <div class="reseiveTalk-baloon baloon">
                  <p class="reseiveTalk-voice"><?php echo sanitize($val['msg']); ?></p>
                </div>
              </li>

              <?php
                    }
                  }
                }else{
              ?>
              <p>メッセージ投稿はまだありません。</p>

              <?php
                }
              ?>

            </ul>
            <form class="chatForm" action="" method="post">
              <textarea class="chatform-text" name="msg" rows="8" cols="80"></textarea>
              <button type="submit" name="submit" class="mailBtn">
                <i class="far fa-paper-plane"></i>
              </button>
            </form>
          </div>

        </div>

      </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
