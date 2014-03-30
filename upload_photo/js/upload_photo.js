$(function() {
  $btnUpload = $('button.upload');
  $data = JSON.parse($btnUpload.attr('data'));

  $('span._width').text($data.width);
  $('span._height').text($data.height);
  $('span._size').text(($data.maxSize / 1024 / 1024).toFixed(2));

  $resize = {};

  new AjaxUpload($btnUpload, {
    action: '/scripts/uploadimage.php',
    name: 'uploadimage',
    data: $data,
    onSubmit: function(file, ext) {
      /*if ($btnUpload = $('button.upload').siblings('ul').children('li').length >= $photosCount) {
        alert('Нельзя загрузить больше чем ' + $photosCount + ' фотографий!');
        return false;
      }*/
      if (!(ext && /^(jpg|jpeg)$/.test(ext))) {
        // extension is not allowed
        alert('Это разрешение не поддерживается. Только JPG.');
        return false;
      }
    },
    onComplete: function(file, response) {
      $response = JSON.parse(response);
      $fileTmpName = $response.file_tmp;
      if ($response.result) {

        $data.fileName = $response.file;

        $('#upload_photo').hide();
        $('#resize_photo img.src_image').attr('src', '/scripts/uploads/' + $response.file + '.jpg');

        $imgWidth = $data.width;
        $imgHeight = $data.height;
        $imgOwnerWidth = $response.width;
        $imgOwnerHeight = $response.height;

        $x1 = Math.floor(($imgOwnerWidth - $imgWidth) / 2);
        $y1 = Math.floor(($imgOwnerHeight - $imgHeight) / 2);
        $x2 = parseInt($x1) + parseInt($imgWidth);
        $y2 = parseInt($y1) + parseInt($imgHeight);

        $resize.x1 = $x1;
        $resize.y1 = $y1;
        $resize.x2 = $x2;
        $resize.y2 = $y2;

        $('#resize_photo img.src_image').imgAreaSelect({
          handles: true,
          persistent: true,
          minWidth: $imgWidth,
          minHeight: $imgHeight,
          aspectRatio: $imgWidth + ':' + $imgHeight,

          x1: $x1,
          y1: $y1,
          x2: $x2,
          y2: $y2,

          onSelectChange: function (img, selection) {

            $resize.x1 = selection.x1;
            $resize.y1 = selection.y1;
            $resize.x2 = selection.x2;
            $resize.y2 = selection.y2;

          }
        });
        $('#resize_photo').show();
        /*$buttonId = this._settings.data.buttonId;
        $sizes = this._settings.data.sizes;
        $count = this._settings.data.count;
        $fileName = $response.file;
        $fileTmpName = $response.file_tmp;
        $makeMain = "";
        if (this._settings.data.makeMain == true) {
          $makeMain = '<div><input type="radio" name="make_main" value="' + $fileName + '" /><label for="make_main">Сделать главной</label></div>';
        }
        $.post(
          "/scripts/rename.php",
          {
            file: $fileName,
            sizes: $sizes
          },
          function(data){
            $array[$buttonId].siblings('ul').append('<li><a href="/scripts/uploads/' + $fileName + '_b.jpg"><img src="/scripts/uploads/' + $fileName + '_s.jpg" /></a><button class="x" data="' + $fileName + '">x</button>' + $makeMain + '</li>');
            checkDisable();
          }
        );*/
      } else {
        alert('Файл ' + $fileTmpName + ' не может быть загружен. ' + $response.message);
      }
    }
  });

  $('#resize_photo button.go_end').click(function() {
    $.extend($resize, $data);
    $.post(
      "/scripts/resize.php",
      $resize,
      function(data) {
        document.location.replace(window.referer);
        /*if (data.result) {
          alert('ok');
        } else {
          alert(data.message);
        }*/
      }//,
      //"json"
    );
  });
});