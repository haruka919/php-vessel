<?php
//==============================
// ログイン認証・自動ログアウト
//==============================
// ログインしている場合
if( !empty($_SESSION['login_date'])){
  debug('ログイン済みのユーザーです。');

  // 現在日時が最終ログイン日時＋有効期限を超えていた場合
  if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限オーバーです。');

    // セッションを削除（ログアウトする）
    session_destroy();

    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
      $_SESSION['return_url'] = $_SERVER['HTTP_REFERER'];
      $_SESSION['return_url']= str_replace('http://', '', $_SESSION['return_url']);
      debug('前ページのurlを代入：'.print_r($_SESSION['return_url'],true));
      debug('未ログインユーザーです。');
      header("Location:login.php"); //ログインページへ
    }

  }else{
    debug('ログイン有効期限以内です。');
    // 最終ログイン日時を現在日時に更新
    $_SESSION['login_date'] = time();

   // 現在実行中のスクリプトファイルがlogin.phpの場合だけマイページに遷移するようにする
   //繰り返しリダイレクトが行われるため
   if(basename($_SERVER['PHP_SELF']) === 'login.php'){
     debug('マイページへ遷移します。');
     header("Location:mypage.php"); //マイページへ
   }
 }
}else{
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    $_SESSION['return_url'] = $_SERVER['HTTP_REFERER'];
    $_SESSION['return_url']= str_replace('http://', '', $_SESSION['return_url']);
    debug('前ページのurlを代入：'.print_r($_SESSION['return_url'],true));
    debug('未ログインユーザーです。');
    header("Location:login.php"); //ログインページへ
  }
}

?>
