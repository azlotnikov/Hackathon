{extends file='html.tpl'}
{block name='title' append} - Админ-панель{/block}
{block name='links' append}
  <link href="/css/admin.css" rel="stylesheet" />
  <link href="/css/main.css" rel="stylesheet" />
  <script src="/js/admin.js"></script>
   <script>
      $(function(){
         var canv        = document.getElementById("canv"),
             ctx         = canv.getContext('2d'), // Контекст
             isDraw      = false,
             coords      = [],
             startP      = {},
             pic         = new Image();
         canv.width      = 4146;
         canv.height     = 2482;
         startP.x    = null;
         startP.y    = null;
         pic.src         = '/img/map.jpg';
         pic.onload = function() {
            ctx.drawImage(pic, 0, 0);
         }
         ctx.strokeStyle = "red";
         ctx.lineWidth = 5;
         canv.onclick = function(e) {
            var rect = canv.getBoundingClientRect();
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
            coords.push(x + ',' + y);

            if (isDraw) {
               ctx.lineTo(x, y);
               ctx.stroke();
            } else {
               ctx.beginPath();
               ctx.moveTo(x, y);
               startP.x = x;
               startP.y = y;
               isDraw = true;
            }
         }
         $("#next").click(function() {
            ctx.lineTo(startP.x, startP.y);
            ctx.stroke();
//            coords = [];
            startP.x = null;
            startP.y = null;
            isDraw = false;
            var num = prompt('Номер');
            $.ajax({
               type: 'POST',
               url: '/scripts/handlers/handler.Admin.php',
               data: {
                  number: num,
                  polygon: coords.join(","),
                  place_type: $('#place_type').val(),
                  floor: $('#floor_select').val(),
                  hostel: '1'
               }
            });
            ctx.clearRect(0, 0, canv.width, canv.height);
            ctx.drawImage(pic, 0, 0);
            coords = [];
         });
      });
   </script>
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
         <option value="2">Этаж 2</option>
         <option value="3">Этаж 3</option>
         <option value="4">Этаж 4</option>
         <option value="5" selected>Этаж 5</option>
         <option value="6">Этаж 6</option>
         <option value="7">Этаж 7</option>
         <option value="8">Этаж 8</option>
      </select>
      <select id="place_type">
         <option value="1">Жилая комната</option>
         <option value="2">Тех. помещение</option>
         <option value="3">Холл</option>
      </select>
      <button id="next">Сохранить</button>
   </div>
   <canvas id="canv">Обновите браузер</canvas>
   <div id="coords"></div>
   <button id="next">Next</button>
</div>
{/block}
