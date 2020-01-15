<?php
//==============================
// ログイン認証・自動ログアウト
//==============================
// ログインしている場合
if( !empty($_SESSION['login_date_shop'])){
  debug('ログイン済みのユーザーです。');

  // 現在日時が最終ログイン日時＋有効期限を超えていた場合
  if( ($_SESSION['login_date_shop'] + $_SESSION['login_limit_shop']) < time()){
    debug('ログイン有効期限オーバーです。');

    // セッションを削除（ログアウトする）
    session_destroy();
    // ログインページへ
    header("Location:login_shop.php");
  }else{
    debug('ログイン有効期限以内です。');
    // 最終ログイン日時を現在日時に更新
    $_SESSION['login_date_shop'] = time();

   // 現在実行中のスクリプトファイルがlogin.phpの場合だけマイページに遷移するようにする
   //繰り返しリダイレクトが行われるため
   if(basename($_SERVER['PHP_SELF']) === 'login_shop.php'){
     debug('マイページへ遷移します。');
     header("Location:mypage_shop.php"); //マイページへ
   }
  }
}else{
  debug('未ログインユーザーです。');
  if(basename($_SERVER['PHP_SELF']) !== 'login_shop.php'){
    header("Location:login_shop.php"); //ログインページへ
  }
}

?>
