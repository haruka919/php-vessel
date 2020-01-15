<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「退会ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth_shop.php');
//==============================
// 画面処理
//==============================
// post送信された場合
if(!empty($_POST)){
  debug('post送信があります。');
  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    //SQL分作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE product SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE `like` SET delete_flg = 1 WHERE user_id = :us_id';
    // データ流し込み
    $data = array(':us_id' => $_SESSION['user_id']);
    // クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    // クエリ実行成功の場合（最悪userテーブルのみ削除成功していれば良しとする）
    if($stmt1){
      // セッション削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します。');
      header("Location:index.php");
    }else{
      debug('クエリが失敗しました。');
      $err_msg['common'] = MSG07;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '退会';
require('head.php');
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>
  <main>
    <div class="login-wrapper">
      <div class="form-wrapper">

        <form action="" class="form cancel-form" method="post">
          <h2 class="form-ttl">修正まだ！！退会</h1>
          <div class="area-msg">
            <?php
             if(!empty($err_msg['common'])) echo $err_msg['common'];
           ?>
          </div>
          <div class="">
            <input type="submit" name="submit" value="退会する">
          </div>
        </form>
      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
