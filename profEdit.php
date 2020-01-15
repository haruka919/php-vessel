<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフィール編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// 画像処理（ドットインストール）
// define('THUMBNAIL_WIDTH', 400);
// define('IMAGES_DIR', __DIR__ . '/images');
// define('THUMBNAIL_DIR', __DIR__ . '/thumbs');// if(!function_exists('imagecreatetruecolor')){
//   echo 'GD not installed';
//   exit;
// }
// require('imageUploader.php');
// フルパスでの使い方（名前空間）
// $uploader = new \MyApp\imageUploader();
// あらかじめ定義する方法（名前空間）
// use MyApp\imageUploader;
// $uploader = new imageUploader();

//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbFormData,true));

//post送信された場合
if(!empty($_POST)){
  debug('post送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES, true));

  // $uploader->upload();

  // 変数にユーザー情報を代入
  $username = $_POST['username'];
  $tel = $_POST['tel'];
  $zip = $_POST['zip'];
  $addr = $_POST['addr'];
  $age = (!empty($_POST['age'])) ? $_POST['age'] : 0;
  $email = $_POST['email'];
  $pic = ( !empty($_FILES['pic']['name']) ) ? uploadImg($_FILES['pic'],'pic') : '';
  $_POST['pic'] = $pic;
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;



  // DBの情報と入力情報が異なる場合にバリデーションチェックを行う
  if($dbFormData['username'] !== $username){
    // 名前の最大文字数チェック
    validMaxLen($username, 'username');
  }
  if($dbFormData['tel'] !== $tel){
    // tel形式チェック
    validTel($tel, 'tel');
  }
  if($dbFormData['zip'] !== $zip){
    validZip($zip, 'zip');
  }
  if((int)$dbFormData['age'] !== $age){
    // 年齢の最大文字数チェック
    validMaxLen($age, 'age');
    // 年齢の半角数字チェック
    validNumber($age, 'age');
  }
  if($dbFormData['email'] !== $email){
    // emailの最大文字数チェック
    validMaxLen($email, 'email');
    if(empty($err_msg['email'])){
      validEmailDup($email);
    }
    // emailの形式チェック
    validEmail($email, 'email');
    // emailの未入力チェック
    validRequired($email, 'email');
  }

  if(empty($err_msg)){
    debug('バリデーションOKです。');

    // 例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :u_name, tel = :tel, zip = :zip, addr = :addr, age = :age, email = :email, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $username, ':tel' => $tel, ':zip' => $zip, ':addr'=> $addr, ':age' => $age , ':email' => $email, ':pic' => $pic, ':u_id' =>$dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        debug('クエリ成功。');
        $_SESSION['msg_success'] = SUC02;

        debug('マイページへ遷移します。');
        header("Location:mypage.php"); //マイページへ
      }else{
        debug('クエリに失敗しました。');
        $err_msg['common'] = MSG08;
      }
    } catch(Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール編集';
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
            <form action="" class="form" method="post" enctype="multipart/form-data">
              <h2 class="form-ttl">プロフィール編集</h1>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['common'])) echo $err_msg['common'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
                名前
                <input type="text" class="input-m" name="username" value="<?php echo getFormData('username'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['username'])) echo $err_msg['username'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
                TEL
                <input type="text" class="input-m" name="tel" value="<?php echo getFormData('tel'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['tel'])) echo $err_msg['tel'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
                郵便番号
                <input type="text" class="input-m" name="zip" value="<?php echo getFormData('zip'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['zip'])) echo $err_msg['zip'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['addr'])) echo 'err'; ?>">
                住所
                <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['addr'])) echo $err_msg['addr'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
                年齢
                <input type="number" class="input-s" name="age" value="<?php echo getFormData('age'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['age'])) echo $err_msg['age'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                メールアドレス
                <input type="text" name="email" value="<?php echo
                getFormData('email'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['email'])) echo $err_msg['email'];
                 ?>
              </div>
              プロフィール画像
              <label class="imgDrop-inner as_profEdit <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="input-file">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                  ドラッグ&ドロップ
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                 ?>
              </div>
              <div class="">
                <input type="submit" name="" value="登録する">
              </div>
            </form>
          </div>
        </section>

        <!-- ログイン後サイドバー -->
        <?php
        require('sidebar_mypage.php');
        ?>


      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
