{extends file='page.tpl'}
{block name='links' append}
   <link href="/css/header.css" rel="stylesheet"/>
   <link href="/css/footer.css" rel="stylesheet"/>
   <link href="/css/forms.css" rel="stylesheet"/>
   <script src="/js/kinectjs.js"></script>
   <script src="/js/geometry.js"></script>
   <script src="/js/event.js"></script>
   <link href="/css/map.css" rel="stylesheet"/>
{/block}
{block name='div.main'}
   {include file="header.tpl"}
   <div id="layers">
      <label for="show_events_all">All events</label><input type="radio" name="events_layer" id="show_events_all" checked/>
      <label for="show_events_party">Party</label><input type="radio" name="events_layer" id="show_events_party"/>
      <label for="show_events_service">Service</label><input type="radio" name="events_layer" id="show_events_service"/>
      <label for="show_events_leisure">Leisure</label><input type="radio" name="events_layer" id="show_events_leisure"/>
   </div>
   <section id="field">
      <div id="container"></div>
   </section>
   <script src="/js/map.js"></script>
   <div id="event_form">
      <form>
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
{/block}