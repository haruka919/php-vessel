<footer class="footer fn_footer">
  <p class="copyright">©vessel All Rights Reserved.</p>
</footer>


<script src="js/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
  $(function(){
    var $ftr = $('.fn_footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight()){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'});
    }
    // メッセージ表示
    var $fnShowMsg = $('.fn-showMsg');
    var msg = $fnShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $fnShowMsg.slideToggle('slow');
      setTimeout(function(){ $fnShowMsg.slideToggle('show');},5000);
    }

    // 画像ライブプレビュー
    var $imgDrop = $('.imgDrop-inner');
    var $fileInput = $('.input-file');
    $imgDrop.on('dragover', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $imgDrop.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $imgDrop.css('border', 'none');
      var file = this.files[0],
          $img = $(this).siblings('.prev-img'),
          fileReader = new FileReader();
      fileReader.onload = function(event){
        $img.attr('src', event.target.result).show();
      };
      fileReader.readAsDataURL(file);
    });

    // お気に入り登録・削除
    var $like,
        likeProductId;
    $like = $('.fn-clike-like') || null;
    likeProductId = $like.data('productid') || null;
    // 数値の0はfalseと判定されてしまう。product_idが0の場合もありえるので、0もtrueとする場合にはunderfinedとnullを判定する。
    if(likeProductId !== undefined && likeProductId !== null){
      $like.on('click', function(){
        var $this = $(this);
        $.ajax({
          type: "POST",
          url: "ajaxLike.php",
          data: { productId : likeProductId}
        }).done(function( data ){
          console.log('Ajax Success');
          // クラス属性をつけ外しする
          $this.toggleClass('active');
        }).fail(function( msg ){
          console.log('Ajax Error');
        });
      });
    }

    // topページスライダー
    $('.fn_slider').slick({
      infinite: true,
      slidesToShow: 2,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 2000,
      arrows: false
    });

    // // 掲示板
    // $('.fn_scroll-bottom').animate({scrollTop: $('.fn_scroll-bottom')[0].scrollHeight}, 'fast');
    // });
  });
</script>
</body>
</html>
