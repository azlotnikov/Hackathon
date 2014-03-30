<!DOCTYPE html>
<html>
<head>
   <title>Map</title>
   <meta charset="utf-8" />
   <script src="/js/kinectjs.js"></script>
   <script src="/js/jquery.js"></script>
   <script src="/js/event.js"></script>
   <link href="/css/map.css" rel="stylesheet" />

</head>
<body>
<section id="field">
   <div id="container"></div>
</section>
<script src="/js/map.js"></script>
<div id="eventAddForm">
   <input id="event_header" value="Заголовок"/>
   <select id="event_type">
      <option value="1" selected>Услуги</option>
      <option value="2">Вечеринки</option>
      <option value="3">Досуг</option>
   </select>
   <textarea id="event_description">
      Описание
   </textarea>
   <input id="event_place_id" hidden="hidden" />
   <input id="event_add" type="button" value="Добавить"/>
</div>
</body>
</html>