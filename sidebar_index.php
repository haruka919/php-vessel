<section class="sidebar">
  <div class="search-wrapper category-wrapper">
    <h2 class="searchTtl">カテゴリー</h2>
    <ul class="searchList">
      <?php
        foreach ($dbCategoryData as $key => $val) {
      ?>
        <li class="searchList-item category">
          <a href="index.php?c_id=<?php echo $val['id']; ?>">
            <img src="<?php echo $val['pic']; ?>" alt=""/>
            <h3 class="category-ttl"><?php echo $val['name']; ?></h3>
          </a>
        </li>
      <?php
        }
      ?>
    </ul>
  </div>
  <div class="search-wrapper author-wrapper">
    <h2 class="searchTtl">作り手</h2>
    <ul class="searchList">
      <?php
        foreach ($dbAuthorData as $key => $val) {
      ?>
        <li class="searchList-item author"><a href="index.php?a_id=<?php echo $val['id']; ?>">&gt; <?php echo $val['name']; ?></a></li>
      <?php
        }
      ?>
    </ul>
  </div>
</section>
