<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード再発行メール送信ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証はなし

//==============================
// 画面処理
//==============================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  // 変数にpost情報を代入
  $email = $_POST['email'];

  // 未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力チェックOK');

    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // EmailがDBに登録されている場合
        if($stmt && array_shift($result)){
          debug('クエリ成功。DB登録あり。');
          $_SESSION['msg_success'] = SUC03;

          $auth_key = makeRandKey(); //認証キーを作成

          // メールを送信
          $from = 'info@vessel.com';
          $to = $email;
          $subject = '【パスワード再発行認証】';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記URLにて認証キーをご入力頂くとパスワードが再発行されます。
パスワード再発行認証キー入力ページ：
認証キー：{$auth_key}
※認証キーの有効期限は30分となります。
EOT;
          sendMail($from, $to, $subject, $comment);

          // 認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60*30); //現在時刻より30分後のUNIXタイムスタンプを入れる
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          header("Location:passRemindRecieve.php"); //認証キー入力ページへ
        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されましたが入力されました。');
          $err_msg['common'] = MSG07;
        }
      } catch(Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード再発行メール送信';
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
        <form action="" class="form" method="post">
          <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
          <div class="area-msg">
            <?php
             if(!empty($err_msg['common'])) echo $err_msg['common'];
             ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <div class="area-msg">
            <?php
             if(!empty($err_msg['email'])) echo $err_msg['email'];
             ?>
          </div>
          <div class="">
            <input type="submit" name="" value="送信する">
          </div>
        </form>
      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
