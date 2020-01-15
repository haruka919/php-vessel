<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「(店舗用)プロフィール編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth_shop.php');

//==============================
// 画面処理
//==============================
// DBからユーザーデータを取得
$dbFormData = getAuthor($_SESSION['author_id']);
// debug('取得した店舗情報：'.print_r($dbFormData,true));

//post送信された場合
if(!empty($_POST)){
  debug('post送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES, true));

  $name = (!empty($_POST['name'])) ? $_POST['name'] : '';
  $ename = (!empty($_POST['ename'])) ? $_POST['ename'] : '';
  $email = (!empty($_POST['email'])) ? $_POST['email'] : '';
  $pic = ( !empty($_FILES['pic']['name']) ) ? uploadImg($_FILES['pic'],'pic') : '';
  $_POST['pic'] = $pic;
  // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;

  // DBの情報と入力情報が異なる場合にバリデーションチェックを行う
  if($dbFormData['name'] !== $name){
    // 名前の最大文字数チェック
    validMaxLen($name, 'name');
  }
  if($dbFormData['ename'] !== $ename){
    // 名前の最大文字数チェック
    validMaxLen($ename, 'ename');
  }
  if($dbFormData['email'] !== $email){
    // emailの最大文字数チェック
    validMaxLen($email, 'email');
    if(empty($err_msg['email'])){
      validEmailDupShop($email);
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
      $sql = 'UPDATE author SET name = :name, ename = :ename, email = :email, pic = :pic WHERE id = :a_id';
      $data = array(':name' => $name, ':ename' => $ename, ':email' => $email,':pic' => $pic, ':a_id' =>$dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        debug('クエリ成功。');
        $_SESSION['msg_success'] = SUC02;

        debug('マイページへ遷移します。');
        header("Location:mypage_shop.php"); //マイページへ
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
    require('header_shop.php');
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
              <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
                店舗名
                <input type="text" class="input-m" name="name" value="<?php echo getFormData('name'); ?>">
              </lab el>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['name'])) echo $err_msg['name'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['ename'])) echo 'err'; ?>">
                店舗名(ローマ字)
                <input type="text" class="input-m" name="ename" value="<?php echo getFormData('ename'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['ename'])) echo $err_msg['ename'];
                 ?>
              </div>
              <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                メールアドレス
                <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
              </label>
              <div class="area-msg">
                <?php
                 if(!empty($err_msg['email'])) echo $err_msg['email'];
                 ?>
              </div>
              店舗アイコン
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
        require('sidebar_mypage_shop.php');
        ?>


      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
