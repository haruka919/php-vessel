<?php
  //名前空間を定義する(MyAppという名前空間を作る)
  namespace MyApp;

  class imageUploader{

    private $_imageFileName;
    public function upload($file, $key){
      debug('doFILE情報：'.print_r($file,true));
      try{
        // error check
        $this->_validateUpload($file);

        // type check
        $type = $this->_validateImageType($file);

        // save
        $path = $this->_save($file, $type);

        chmod($path, 0644);

        return $path;

        // create thumbnail
      }catch(\Exception $e){
        echo $e->getMessage();
        exit;
      }
    }
    public function _save($file, $type){
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

      if(!move_uploaded_file($file['tmp_name'], $path)){
        throw new \RuntimeException('ファイル保存時にエラーが発生しました');
      }

      return $path;
    }
    public function _validateImageType($file){
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new \RuntimeException('画像形式が未対応です。');
      }
      return $type;
    }

    private function _validateUpload($file){
      if(isset($file['error']) && is_int($file['error'])){
        try{
          switch($file['error']){
            case UPLOAD_ERR_OK: //ok
              break;
            case UPLOAD_ERR_NO_FILE: //ファイル未選択
              throw new \RuntimeException('ファイルが選択されていません');
            case UPLOAD_ERR_INI_SIZE: //php.ini定義の最大サイズが超過した場合
            case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズが超過した場合
              throw new \RuntimeException('ファイルサイズが大きすぎます');
            default:  //その他の場合
              throw new \RuntimeException('その他のエラーが発生しました');
          }
        }catch(\RuntimeException $e){

          debug($e->getMessage());
          global $err_msg;
          $err_msg[$key] = $e->getMessage();
        }
      }
    }

  }
?>
