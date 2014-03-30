{extends file='html.tpl'}
{block name='title' append} - Админ-панель{/block}
{block name='links' append}
  <link href="/css/admin.css" rel="stylesheet" />
  <link href="/css/main.css" rel="stylesheet" />
  <script src="/js/admin.js"></script>
  <script src="/js/kinectjs.js"></script>
{/block}
{block name='page'}
<div id="wrap">
  <header>
    <nav>
      <ul>
        <li><a href="/admin/map">Карта</a></li>
        <li><a href="/admin/change_pass">Сменить пароль</a></li>
        <li><a href="/admin/logout">Выход</a></li>
      </ul>
    </nav>
  </header>
  <div id="top_block">
  </div>
   <div id="floor_select_section">
      <label for="floor_select">Этаж: </label>
      {*TODO generate floors ids*}
      <select id="floor_select">
         <option value="1">Этаж 1</option>
         <option value="1">Этаж 2</option>
         <option value="1">Этаж 3</option>
         <option value="1">Этаж 4</option>
         <option value="1" selected>Этаж 5</option>
         <option value="1">Этаж 6</option>
         <option value="1">Этаж 7</option>
         <option value="1">Этаж 8</option>
      </select>
   </div>
   <div id="container"></div>
   <script>
      var stage = new Kinetic.Stage({        //канвас
         container: 'container',
         width: $(document).width() - 100,
         height: $(document).height() - 100,
         draggable: true
      });

      var layer = new Kinetic.Layer(),
              imageObj = new Image();

      imageObj.onload = function () {

         var imageMap = new Kinetic.Image({
            x: 1,
            y: 1,
            image: imageObj
         });

         layer.add(imageMap);

         stage.add(layer);

         layer.scaleX(0.6);
         layer.scaleY(0.6);

         layer.draw();
      };

      imageObj.src = '/img/map.jpg';

   </script>
</div>
{/block}
