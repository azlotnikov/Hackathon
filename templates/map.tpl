<!DOCTYPE html>
<html>
<head>
   <title>Map</title>
   <meta charset="utf-8" />
   <script src="/js/kinetic-v5.1.0.min.js"></script>
   <script src="/js/jquery.js"></script>
   <script src="/js/event.js"></script>

</head>
<body>
<div id="container"></div>
<script src="/js/map.js"></script>
<div id="eventAddForm" style="width: 200px; height: 200px; border: 1px black solid; position: absolute; display: none;">
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