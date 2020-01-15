<?php
//==============================
// ログ
//==============================
// ログを取るか
ini_set('log_errors','on');
// ログの出力ファイルを指定
ini_set('error_log', 'php.log');

//==============================
// デバッグ
//==============================
// デバッグフラグ
$debug_flg = false;
// デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}
//==============================
// セッション準備・セッション有効期限を延ばす
//==============================
// セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");//C:\Windows\Temp /var/tmp/
// ガーページコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の1の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
// ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましセキュリティ対策）
session_regenerate_id();

//==============================
// 画面表示処理開始ログ吐き出し関数
//==============================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理能力');
  debug('セッション変数：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期日日時タイムスタンプ'.( $_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//==============================
// 定数
//==============================
// エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Email形式で入力をしてください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '256文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '半角数字のみご利用いただけます');
define('MSG13', '古いパスワードが違います');
define('MSG14', '古いパスワードと同じです');
define('MSG15', '文字で入力してください');
define('MSG16', '正しくありません');
define('MSG17', '有効期限が切れています');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡を取りましょう！');

//==============================
// バリデーション関数
//==============================
// エラーメッセージ格納用の配列
$err_msg = array();

// バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
// バリデーション関数（email形式チェック）
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
// バリデーション関数（Email重複チェック）
function validEmailDup($email){
  global $err_msg;
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
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch(Exception $e) {
    error_log('エラー発生'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
// バリデーション関数（Email重複チェック）【出店用】
function validEmailDupShop($email){
  global $err_msg;
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM author WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch(Exception $e) {
    error_log('エラー発生'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
// バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
// バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
// バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
// バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
// 電話番号形式チェック
function validTel($str, $key){
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
// 郵便番号形式チェック
function validZip($str, $key){
  if(!preg_match("/^\d{7}$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}
// 半角数字チェック
function validNumber($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG12;
  }
}
// パスワードチェック
function validPass($str, $key){
  // 半角英数字チェック
  validHalf($str, $key);
  // 最大文字数チェック
  validMaxLen($str, $key);
  // 最小文字数チェック
  validMinLen($str, $key);
}
// selectboxチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG16;
  }
}
// エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}
//==============================
// ログイン認証
//==============================
function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }
  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}
//==============================
// データベース
//==============================
// DB接続関数
function dbConnect(){
  // DBへの接続準備
  $dsn = '';
  $user = '';
  $password = '';  //macはroot
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
// SQL実行関数
function queryPost($dbh, $sql, $data){
  // クエリー作成
  $stmt = $dbh->prepare($sql);
  // プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt->errorInfo(),true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}
function getUser($u_id){
  debug('ユーザー情報を取得します。');
  // 例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    // if($stmt){
    //   debug('クエリ成功。');
    // }else{
    //   debug('クエリに失敗しました。');
    // }
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生：'. $e->getMessage());
  }
  // クエリ結果のデータを返却
  // return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getProduct($u_id, $p_id){
  debug('商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID:'.$p_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProductShop($a_id, $p_id){
  debug('(店舗用)商品情報を取得します。');
  debug('店舗ID：'.$a_id);
  debug('商品ID:'.$p_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM product WHERE author_id = :a_id AND id = :p_id AND delete_flg = 0';
    $data = array(':a_id' => $a_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProductList($currentMinNum = 1, $author, $category, $span = 12){
  debug('商品情報を取得します');
  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文を作成
    $sql = 'SELECT id FROM product';
    if(!empty($author)) $sql .= ' WHERE author_id = '.$author;
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }
    // ページング用SQL文作成

    $sql = 'SELECT p.id, p.name, p.author_id, p.category_id, p.copy, p.comment, p.price, p.pic1, p.pic2, p.pic3, p.create_date, p.update_date, a.name AS author, c.name AS category
    FROM product AS p LEFT JOIN author AS a ON p.author_id = a.id LEFT JOIN category AS c ON p.category_id = c.id';
    if(!empty($author)) $sql .= ' WHERE author_id = '.$author;
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    $sql .= ' ORDER BY p.id DESC';
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL:'.$sql);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProductOne($p_id){
  debug('商品情報を取得します。');
  debug('商品ID：'.$p_id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT p.id, p.name, p.author_id, p.category_id, p.copy, p.comment, p.price, p.pic1, p.pic2, p.pic3, c.name, a.name AS author FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id LEFT JOIN
    author AS a ON p.author_id = a.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0 AND a.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProductSeries($a_id){
  debug('同じ作り手の商品を取得します。');
  debug('作り手ID：'.$a_id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成(同じ作りての商品を取得)
    $sql = 'SELECT pic1 ,name FROM product WHERE author_id = :a_id';
    $data = array(':a_id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMyPurchased($u_id){
  debug('自分が購入した商品情報を取得します。');
  debug('ユーザーID:'.$u_id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();

    // SQL文作成
    $sql = 'SELECT p.id, p.name, p.price, p.pic1 FROM product AS p RIGHT JOIN bord AS b ON p.id = b.product_id WHERE b.buy_user = :b_uid AND b.delete_flg = 0 ORDER BY b.create_date DESC';
    $data = array(':b_uid' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getSoldProduct($a_id){
  debug('売れた商品情報を取得します。');
  debug('ユーザーID:'.$a_id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();

    // SQL文作成
    $sql = 'SELECT p.id, p.name, p.price, p.pic1 FROM product AS p RIGHT JOIN bord AS b ON p.id = b.product_id WHERE b.sale_user = :s_uid AND b.delete_flg = 0 ORDER BY b.create_date DESC';
    $data = array(':s_uid' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
// function shuffle_assoc($list) {
//   if (!is_array($list)) return $list;
//   $keys = array_keys($list);
//   shuffle($keys);
//   $random = array();
//   foreach ($keys as $key)
//     $random[$key] = $list[$key];
//
//   return $random;
// }

function getMyProducts($a_id){
  debug('自分(店)が出品した商品情報を取得します。');
  debug('ユーザーID:'.$a_id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM product WHERE author_id = :a_id AND delete_flg = 0 ORDER BY create_date DESC';
    $data = array(':a_id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMsgsAndBord($id){
  debug('(共通)msg情報を取得します');
  debug('掲示板ID:'.$id);
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT m.id AS m_id, m.send_date, m.to_user, m.from_user, m.msg, m.delete_flg, b.sale_user, b.buy_user, b.product_id, b.create_date FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id ORDER BY m.send_date ASC';
    $data = array(':id' => $id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $result = $stmt->fetchAll();
      debug('msg情報のデータ：'.print_r($result, true));

    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMyMsgsAndBord($u_id){
  debug('（顧客）連絡掲示板情報を取得します');
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT b.id, b.sale_user, b.buy_user, b.product_id, a.name AS author FROM bord AS b LEFT JOIN author AS a ON b.sale_user = a.id WHERE b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    debug('(顧客用)掲示板データ[$rst]：'.print_r($rst, true));

    if(!empty($rst)){
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if($stmt){
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMyMsgsAndBordShop($a_id){
  debug('（出店者）連絡掲示板情報を取得します');
  // 例外処理
  try{
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
        $sql = 'SELECT b.id, b.sale_user, b.buy_user, b.product_id, u.username FROM bord AS b LEFT JOIN users AS u ON b.buy_user = u.id WHERE b.sale_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    debug('(店舗用)掲示板データ[$rst]：'.print_r($rst, true));

    if(!empty($rst)){
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
        debug('(店舗用)メッセージデータ：'.print_r($rst[$key]['msg'], true));
      }
    }
    if($stmt){
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//==============================
// 作り手情報（author）
//==============================
function getAuthorData(){
  debug('店舗情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM author';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getAuthor($a_id){
  debug('店舗情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM author WHERE id = :a_id';
    $data = array(':a_id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getCategory(){
  debug('カテゴリー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM category';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getAuthorName($a_id){
  debug('選択中の作り手名を取得します');
  debug('現在選択中の作り手ID:'.$a_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT name,ename FROM author WHERE id = :a_id';
    $data = array(':a_id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の1レコードを返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }

}
function isLike($u_id, $p_id){
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt->rowCount()){
      debug('お気に入りです。');
      return true;
    }else{
      debug('特に気に入っていません');
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getMyLike($u_id){
  debug('自分のお気に入りの情報を取得します。');
  debug('ユーザーID：'.$u_id);
  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM `like` AS l LEFT JOIN product AS p ON l.product_id = p.id WHERE l.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
//==============================
// メール送信
//==============================
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    // 文字化けしないように設定
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    // メールを送信(送信結果はtrueかfalseで返ってくる)
    $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
    // 送信結果を判定
    if($result){
      debug('メールを送信しました。');
    }else{
      debug('【エラー発生】メールの送信に失敗しました');
    }
  }
}
//==============================
// その他
//==============================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str, ENT_QUOTES);
}
// フォームの入力保持
// フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  // var_dump($_POST['name']);
  // if(isset($_POST['name'])) $_POST['name'] = ''; //店舗名
  // if(isset($_POST['ename'])) $_POST['ename'] =  ''; //店舗名(ローマ字)
  // if(isset($_POST['email'])) $_POST['email'] = '';;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        if(!isset($method[$str])){
          return sanitize($dbFormData[$str]);
        }
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}

// sessionを1回だけ取得できる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
// 認証キー生成
function makeRandKey($length = 8){
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for($i = 0; $i < $length; ++$i){
    $str .= $chars[mt_rand(0,61)];
  }
  return $str;
}
// ページング
  // $currentPageNum:現在のページ数
  // $totalPageNum:総ページ数
  // $link:検索用GETパラメータリンク
  // $pageColNum:ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページと同じかつ総ページ数が表示項目数以上なら、左にリンクを4個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の1ページ前なら、左にリンク3個、右に1個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク1個、右にリンク3個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に4個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    // それ以外は左に2個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="pagination-list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="pagination-list-item ';
        if($currentPageNum == $i){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1 ){
        echo '<li class="pagination-list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
// 画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}
// GETパラメータ付与
// $del_key:付与から取り除きたいGETパラメーターキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach ($_GET as $key => $val) {
      if(!in_array($key,$arr_del_key, true)){
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])){
    try{
      switch($file['error']){
        case UPLOAD_ERR_OK: //ok
          break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: //php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズが超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:  //その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new RuntimeException('画像形式が未対応です。');
      }

      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      debug($path);

      if(!move_uploaded_file($file['tmp_name'], $path)){
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }

      // 保存したファイルパスのパーミッションを変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    }catch(RuntimeException $e){

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

?>
