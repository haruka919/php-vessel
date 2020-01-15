<?php

// 共通変数・関数ファイルを読み込み
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「トップページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETパラメータを取得
// -------------------
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// 作りて
$author = (!empty($_GET['a_id'])) ? $_GET['a_id'] : '';
// パラメータに不正な値が入っているかチェック
if(!is_int((int)$currentPageNum)){
  error_log('エラー発生：指定ページに不正な値が入りました。');
  header("Location:index.php"); //トップページへ
}
// 表示件数
$listSpan = 12;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら0、2ページ目なら12
// DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $author, $category);
// DBから作り手データを取得
$dbAuthorData = getAuthorData();
// DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
$getparam = appendGetParam(array('p'));
$option = str_replace('?', '&', $getparam);

// 選択中のカテゴリー名を取得する
$dbAuthorName = getAuthorName($author);

debug('現在のページ：'.$currentPageNum);
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>
<?php
$siteTitle = 'HOME';
require('head.php');
?>
<body>

  <!-- ヘッダー -->
  <?php
    require('header.php');
  ?>

  <main>
    <section class="kv">
      <ul class="fn_slider">
        <?php
         foreach ($dbProductData['data'] as $key => $val):
        ?>
          <li class="slider-item"><img class="kv-img" src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>"></li>
        <?php
          endforeach;
        ?>
      </ul>
      <div class="heading">
        <?php
           if(!empty($dbAuthorName['name'])){
        ?>
        <h1 class="heading-ttl"><?php echo sanitize($dbAuthorName['name']); ?></h1>
        <?php
          } else {
        ?>
        <h1 class="heading-ttl">おすすめ品</h1>
        <?php
          }
        ?>
        <?php
           if(!empty($dbAuthorName['ename'])){
        ?>
        <span class="heading-subTtl"><?php echo sanitize($dbAuthorName['ename']); ?></span>
        <?php
          } else {
        ?>
        <span class="heading-subTtl">OSUSUME</span>
        <?php
          }
        ?>
      </div>
    </section>
    <div class="contents-wrapper">
      <div class="contents-outer">

        <!-- indexのサイドバー -->
        <?php
        require('sidebar_index.php');
        ?>

        <section class="item-wrapper">
          <div class="item-search">
            <p><?php echo $currentMinNum+1; ?><span>～</span><?php echo $currentMinNum+count($dbProductData['data']); ?><span>件（全</span><?php echo sanitize($dbProductData['total']); ?><span>件）</span></p>
          </div>

          <div class="items-wrapper">
            <div class="items">

              <!-- items始まり -->
              <?php
               foreach ($dbProductData['data'] as $key => $val):
              ?>
              <div class="item">
                <div class="item-author">
                  <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                    <?php echo $val['author']; ?></a>
                </div>
                <div class="item-inner">
                  <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>">
                    <span class="item-badge" data-bgcolor="<?php echo $val['category_id']; ?>"><?php echo $val['category']; ?></span>
                    <figure>
                      <img class="item-pic" src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                    </figure>
                    <div class="item-txtWrapper">
                      <div class="item-txtInner">
                        <h2 class="item-ttl"><?php echo sanitize($val['name']); ?></h2>
                        <span class="item-price"><?php echo sanitize(number_format($val['price'])); ?>円</span>
                      </div>
                      <p class="item-desc"><?php echo sanitize($val['copy']); ?></p>
                    </div>
                  </a>
                </div>
              </div>

              <?php
                endforeach;
              ?>


            </div>
            <!-- itemsおわり -->

            <!-- ページネーション始まり -->
            <?php pagination($currentPageNum, $dbProductData['total_page'], $option); ?>

          </div>
          <!-- itemsWrapperおわり -->
        </section>
      </div>
    </div>
  </main>

  <!-- フッター -->
  <?php
      require('footer.php');
  ?>
