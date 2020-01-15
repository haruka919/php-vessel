<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード変更ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth_shop.php');

//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

//post送信された場合
if(!empty($_POST)){
  debug('post送信があります。');
  debug('post情報：'.print_r($_POST,true));

  // 変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  // 未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 古いパスワードのチェック
    validPass($pass_old, 'pass_old');
    // 新しいパスワードのチェック
    validPass($pass_new, 'pass_new');

    // 古いパスワードとDBパスワードを照合（DBに入っているデータと同じであれば、半角英数字チェックや最大文字チェックは行わなくても問題ない）
    if(!password_verify($pass_old, $userData['password'])){
      $err_msg['pass_old'] = MSG13;
    }

    // 新しいパスワードと古いパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG14;
    }
    // パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // 例外処理
      try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if($stmt){
          // debug('クエリ成功。');
          $_SESSION['msg_success'] = SUC01;

          // メール送信
          $username = ($userData['username']) ? $userData['username'] : '名無し';
          $from = 'info@vessel.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知 | VESSEL';
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。

EOT;
          sendMail($from, $to, $subject, $comment);

          header("Location:mypage.php"); //マイページへ
        // }else{
        //   debug('クエリに失敗しました。');
        //   $err_msg['common'] = MSG07;
        }

      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード変更';
require('head.php');
?>

<body>

  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>

  <main>
    <div class="contents-wrapper">
      <div class="contents-outer as_logined">

        <!-- 入力フォーム -->
        <section class="loginedForm">
          <div class="form-wrapper">
            <form action="" class="form" method="post">
              <h2 class="form-ttl">修正まだ！！パスワード変更</h1>
              <div class="area-msg">
                <?php
                echo getErrMsg('common');
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
                古いパスワード
                <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pass_old');
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
                新しいパスワード
                <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>">
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pass_new');
                ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
                新しいパスワード（再入力）
                <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>">
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pass_new_re');
                ?>
              </div>
              <div class="">
                <input type="submit" name="" value="変更する">
              </div>
            </form>
          </div>
        </section>

        <!-- ログイン後サイドバー -->
        <?php
        require('sidebar_mypage_shop.php');
        ?>

      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
