<header class="header">
  <div class="header-inner">
    <h1>
      <a href="index.php"><img class="sitelogo" src="img/sitelogo.png" alt="サイトロゴ"></a>
    </h1>
    <nav class="header-nav">
      <ul class="btns">
        <?php
          if(empty($_SESSION['user_id'])){
        ?>
          <li class="btn as_btn"><a href="signup.php">新規登録</a></li>
          <li class="btn"><a href="login.php">ログイン</a></li>
        <?php
          } else {
        ?>
          <li class="btn as_btn"><a href="mypage.php">マイページ</a></li>
          <li class="btn"><a href="logout.php">ログアウト</a></li>
        <?php
          }
        ?>
      </ul>
    </nav>
  </div>
</header>
