{extends file='page.tpl'}
{block name='links' append}
   <link href="/css/header.css" rel="stylesheet"/>
   <link href="/css/footer.css" rel="stylesheet"/>
   <link href="/css/forms.css" rel="stylesheet"/>
   <link href="/css/events.css" rel="stylesheet"/>
   <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.css"/ >
   <link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.10.4.custom.css"/ >
   <script src="/js/map_utils.js"></script>
   <script src="/js/kinectjs.js"></script>
   <script src="/js/geometry.js"></script>
   <script src="/js/event.js"></script>
   <script src="/js/jquery.datetimepicker.js"></script>
   <script src="/js/jquery-ui-1.10.4.custom.js"></script>

   <link href="/css/map.css" rel="stylesheet"/>
{/block}
{block name='div.main'}
   {include file="header.tpl"}
   <div class="center">
      <div id="layers">
         <input type="checkbox" name="events_layer" id="show_events_party" checked/><label for="show_events_party">Мероприятия</label>
         <input type="checkbox" name="events_layer" id="show_events_service" checked/><label for="show_events_service">Услуги</label>
         <input type="checkbox" name="events_layer" id="show_events_leisure" checked/><label for="show_events_leisure">Досуг</label>
      </div>
      <div id="slider" style="width: 300px;"></div>
      <div id="floor_select_section" style="display: none">
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
      </div>
   </div>
   <section id="field">
      <div id="container"></div>
   </section>
   <script src="/js/map.js"></script>
   <div id="event_form">
      <form action="/">
         <button id="event_form_close" type="button">x</button>
         <div class="form_block">
            <label for="event_header">Заголовок</label>
            <input id="event_header" name="event_header" value="Событие"/>
         </div>
         <div class="form_block">
            <label for="event_type">Событие</label>
            <select id="event_type" name="event_type">
               <option value="1" selected>Услуги</option>
               <option value="2">Вечеринки</option>
               <option value="3">Досуг</option>
            </select>
		</div>
         <div class="form_block event_datetime"style="display:none">
            <label for="event_datetime">Дата начала</label>
            <input type="text" id="event_datetime" name="event_datetime"/>
         </div>
         <div class="form_block">
            <label for="event_description">Описание</label>
            <textarea id="event_description" name="event_description">Описание</textarea>
         </div>
         <input id="event_place_id" hidden="hidden"/>

         <div class="buttons">
            <button id="event_add">Добавить</button>
         </div>
      </form>
   </div>
   <div id="events_info" class="events on_map">
      <button id="events_info_close" type="button">x</button>
      <h1 class="top"></h1>
      <div class="events_info_data"></div>
   </div>
{/block}