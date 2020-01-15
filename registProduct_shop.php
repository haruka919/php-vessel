<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「商品出品登録ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth_shop.php');


// 画像処理（ドットインストール）
define('THUMBNAIL_WIDTH', 400);
define('IMAGES_DIR', __DIR__ . '/images');
define('THUMBNAIL_DIR', __DIR__ . '/thumbs'); if(!function_exists('imagecreatetruecolor')){
  echo 'GD not installed';
  exit;
}
require('imageUploader.php');

// フルパスでの使い方（名前空間）
// $uploader = new \MyApp\imageUploader();
// あらかじめ定義する方法（名前空間）
$uploader = new \MyApp\ImageUploader();

//==============================
// 画面処理
//==============================
//
// 画面表示用データ取得
//==============================
// GETデータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProductShop($_SESSION['author_id'], $p_id) : '';
// 新規登録画面が編集か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからカテゴリデータを取得
$dbAuthorData = getAuthorData();
$dbCategoryData = getCategory();
debug('商品ID：'.$p_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));
debug('店舗データ：'.print_r($dbAuthorData,true));
debug('カテゴリデータ：'.print_r($dbCategoryData,true));

// パラメータ改ざんチェック
//==============================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないので、マイページへ遷移させる。
if(!empty($p_id) && empty($dbFormData)){
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:mypage_shop.php");//マイページへ
}
// post送信時処理
//==============================
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST送信：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES, true));

  // 変数にユーザー情報を代入
  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $copy = $_POST['copy'];
  $comment = $_POST['comment'];
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
  $pic1 = (!empty($_FILES['pic1']['name'])) ? $uploader->upload($_FILES['pic1'],'pic1') : '';
  // var_dump($pic1);
  $_POST['pic1'] = $pic1;
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2'):'';
  // var_dump($pic2);
  $_POST['pic2'] = $pic2;
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $_POST['pic3'] = $pic3;
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;


  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if(empty($dbFormData)){
    // 未入力チェック（商品名）
    validRequired($name, 'name');
    // 未入力チェック（キャッチコピー）
    validRequired($copy, 'copy');
    // 未入力チェック（金額）
    validRequired($price, 'price');
    // 最大文字数チェック（詳細）
    validRequired($comment, 'comment');
    // セレクトボックスチェック（カテゴリ）
    validSelect($category, 'category_id');
    // 半角数字チェック
    validNumber($price, 'price');
  }else{
    if($dbFormData['name'] !== $name){
      // 未入力チェック
      validRequired($name, 'name');
      // 最大文字数チェック
      validMaxLen($name, 'name');
    }
    if($dbFormData['category_id'] !== $category){
      // セレクトボックスチェック
      validSelect($category, 'category');
    }
    if($dbFormData['comment'] !== $comment){
      // 最大文字数チェック
      validMaxLen($comment, 'comment', 500);
    }
    if($dbFormData['copy'] !== $copy){
      // 未入力チェック（キャッチコピー）
      validRequired($copy, 'copy');
      // 最大文字数チェック
      validMaxLen($copy, 'copy');
    }
    if($dbFormData['price'] != $price){
      // 未入力チェック（金額）
      validRequired($price, 'price');
      // 半角数字チェック
      validNumber($price, 'price');
    }
  }
  if(empty($err_msg)){
    debug('バリデーションOKです。');

    // 例外処理
    try {
      //DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      // 編集画面はupdate文、新規登録画面はinsert文
      if($edit_flg){
        debug('DB更新です。');
        $sql = 'UPDATE product SET name = :name, category_id = :category, copy = :copy, comment = :comment, price = :price, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE author_id = :a_id AND id = :p_id';
        $data = array(':name' => $name, ':author' => $author, ':category' => $category, ':copy' => $copy, ':comment' => $comment, ':price' => $price, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3,':a_id' => $_SESSION['author_id'],
        ':p_id' => $p_id);
      }else{
        debug('DB新規登録です。');
        $sql = 'INSERT INTO product (name, category_id, copy, comment, price, pic1, pic2, pic3, author_id, create_date ) values (:name, :category, :copy, :comment, :price, :pic1, :pic2, :pic3, :a_id, :date)';
        $data = array(':name' => $name, ':category' => $category, ':copy' => $copy, ':comment' => $comment, ':price' => $price, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3,':a_id' => $_SESSION['author_id'],
        ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL：'.$sql);
      debug('流し込みデータ：'.print_r($data, true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功
      if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します。');
        header("Location:mypage_shop.php");//マイページへ
      }
    } catch (Exception $e){
      error_log('エラー発生:'. $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>
<?php
$siteTitle = (!$edit_flg) ? '商品出品登録':'商品編集';
require('head.php');
?>

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
              <h2 class="form-ttl">
                <?php echo (!$edit_flg) ? '商品を出品する' : '商品を編集する'; ?>
              </h2>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
              </div>

              <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
                商品名
                <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['name'])) echo $err_msg['name'];
                ?>
              </div>

              <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
                カテゴリ
                <select class="categoryList" name="category_id">
                  <option value="0" <?php if(getFormData('category_id') == 0){ echo 'selected';} ?>>選択してください</option>
                  <?php
                      foreach ($dbCategoryData as $key => $val) {
                  ?>
                    <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id']){ echo 'selected'; } ?>>
                      <?php echo $val['name']; ?>
                    </option>
                  <?php
                      }
                  ?>
                </select>
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['category_id'])) echo $err_msg['category_id'];
                ?>
              </div>

              <label class="<?php if(!empty($err_msg['copy'])) echo 'err'; ?>">
                キャッチコピー
                <input type="text" name="copy" value="<?php echo getFormData('copy'); ?>">
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['copy'])) echo $err_msg['copy'];
                ?>
              </div>

              <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
                詳細
                <textarea name="comment" value=""><?php echo getFormData('comment'); ?></textarea>
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['comment'])) echo $err_msg['comment'];
                ?>
              </div>

              <label class="<?php if(!empty($err_msg['price'])) echo 'err'; ?>">
                金額
                <div class="inputPrice-wrapper">
                  <input class="inputPrice" type="text" name="price" placeholder="50,000" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>"><span>円</span>
                </div>
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['price'])) echo $err_msg['price'];
                ?>
              </div>

              <div class="imgDrop-wrapper">
                <div class="imgDrop">
                  画像1
                  <label class="imgDrop-inner <?php if(!empty($err_msg['price'])) echo 'err'; ?>">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                    <input type="file" name="pic1" class="input-file">
                    <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>">
                      ドラッグ＆ドロップ
                  </label>
                  <div class="area-msg">
                    <?php
                    if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                    ?>
                  </div>
                </div>
                <div class="imgDrop">
                  画像2
                  <label class="imgDrop-inner <?php if(!empty($err_msg['price'])) echo 'err'; ?>">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                    <input type="file" name="pic2" class="input-file">
                    <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
                      ドラッグ＆ドロップ
                  </label>
                  <div class="area-msg">
                    <?php
                    if(!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                    ?>
                  </div>
                </div>
                <div class="imgDrop">
                  画像3
                  <label class="imgDrop-inner <?php if(!empty($err_msg['price'])) echo 'err'; ?>">
                    <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                    <input type="file" name="pic3" class="input-file">
                    <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
                      ドラッグ＆ドロップ
                  </label>
                  <div class="area-msg">
                    <?php
                    if(!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                    ?>
                  </div>
                </div>
              </div>
              <div class="">
                <input type="submit" name="" value="<?php echo (!$edit_flg) ? '出品する':'更新する'; ?>">
              </div>
            </form>

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
