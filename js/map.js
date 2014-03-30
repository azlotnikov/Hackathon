var UPDATE_INTERVAL = 5000; //new info interval
var iconsTimer;
var inits = [false, false, false] //for image loading

var eventsObjects = {
   'service': {
      id: 1,
      offsetX: -29,
      offsetY: 29,
      color: '#37b0fa'
   },
   'party': {
      id: 2,
      offsetX: 0,
      offsetY: -25,
      color: '#e42d3a'
   },
   'leisure': {
      id: 3,
      offsetX: 29,
      offsetY: 29,
      color: '#419829'
   }
};

var min_scale = 0.1,  //минимальный масштаб карты
   max_scale = 1.2,    //максимальный мастштаб карты
   bigCircleRadius = 40,   //радиус большого кружка события
   rus_names = {1 : 'Услуги', 2 : 'Мероприятия', 3 : 'Досуг'},
   littleCircleRadius = 10;   //радиус маленького кружка события

function Map() {               //самый главный объект карта
   this.scale        = 0.7;         //начальный масштаб
   this.places       = {};          //массив всех мест: ключ - id места
   this.events       = {};          //массив всех событий
//  this.activePlace  = {};
   this.cachedEvents = {};
   this.stage        = new Kinetic.Stage({        //канвас
      container: 'container',
      width: $(document).width() - 100,
      height: $(document).height() - 100,
      draggable: true
   });
}

function loadIcons() {
   eventsObjects["service"].imageIcon = new Image();
   eventsObjects["service"].imageIcon.onload = function () {
      inits[0] = true;
   };
   eventsObjects["service"].imageIcon.src = '/img/icon_service.png';
//
   eventsObjects["party"].imageIcon = new Image();
   eventsObjects["party"].imageIcon.onload = function () {
      inits[1] = true;
   };
   eventsObjects["party"].imageIcon.src = '/img/icon_party.png';
//
   eventsObjects["leisure"].imageIcon = new Image();
   eventsObjects["leisure"].imageIcon.onload = function () {
      inits[2] = true;
   };
   eventsObjects["leisure"].imageIcon.src = '/img/icon_leisure.png';
}

Map.prototype.initLvl2 = function () {
   if (!(inits[0] && inits[1] && inits[2])){
      return;
   }
   map.getInitInfo();                             //получение информации обо всем на карте ajax
   setInterval(map.getNewInfo, UPDATE_INTERVAL);

   var layer = new Kinetic.Layer(),
      imageObj = new Image();

   imageObj.onload = function () {

      var imageMap = new Kinetic.Image({
         x: 1,
         y: 1,
         image: imageObj
      });

      map.imageLayer = new Kinetic.Layer();
      map.placesLayer = new Kinetic.Layer();
      map.eventsLayer = new Kinetic.Layer();

      map.stage.add(map.imageLayer);
      map.stage.add(map.placesLayer);
      map.stage.add(map.eventsLayer);

      map.changeScale(0.6); //!TODO set to default ?

      map.imageLayer.add(imageMap);
      map.imageLayer.draw();

      layer.draw();
      map.initPlaces();       //нарисовать скрытый слой с местами
      handleEventsLayers();   //определяет какие события из чекбоксов нарисовать

      imageMap.on("click", function () {  //скрыть формочку при клике на пустое место
         $("#event_form").hide();
         $('#events_info').hide();
      });
   };

   imageObj.src = '/img/map.jpg';
   clearInterval(iconsTimer);
};

Map.prototype.init = function (floor) {
   loadIcons();
   map.floor = floor;
   iconsTimer = setInterval(map.initLvl2, 100);
};

Map.prototype.getInitInfo = function () {
   var $this = this;
   $.ajax({
      type: 'POST',
      url: '/scripts/handlers/handler.Map.php',
      data: {
         action: "getInitInfo",
         floor: map.floor,
         hostel: '1'
      },
      success: function (data) {
         if (data.hasOwnProperty('result')) {
            if (data.result) {
               $this.places = data.data.places;
               $this.events = data.data.events;
               $this.availablePlaces = data.data.available_places;
            } else {
               alert(data.message);
            }
         } else {
            alert('Unknown error!');
         }
      },
      dataType: 'json',
      async: false
   });
};

Map.prototype.getNewInfo = function () {
   var $this = this;
   $.ajax({
      type: 'POST',
      url: '/scripts/handlers/handler.Map.php',
      data: {
         action: "getNewInfo",
         last_updated_date: map.lastUpdatedDate
      },
      success: function (data) {
         console.log(data);
         if (data.hasOwnProperty('result')) {
            if (data.result) {
               map.CacheUpdate(data.data.deleted, data.data.created);
            } else {
               alert(data.message);
            }
        }
      },
      dataType: 'json'
   });
};

Map.prototype.CacheUpdate = function (_deleted, _created) {
   var eIdx;
   if (_deleted.length == 0 && _created.length == 0){
      return;
   }
   map.lastUpdatedDate = getCurrentDate();
    
   for (eIdx in _deleted) {
      var eType = parseInt(_deleted[eIdx].events_event_type);
      var eId = parseInt(_deleted[eIdx].events_id);
      if (!map.events[eType])
         continue;
      if (map.events[eType][eId]) {
         delete map.events[eType][eId];
      }
      var e;
      for (e in map.cachedEvents[eType]) {
         if (map.cachedEvents[eType][e] == eId) {
            delete map.cachedEvents[eType][e];
            break;
         }
      }
   }
   var eventTypeId;
   for (eventTypeId in _created) {
      var eventTypeIdInt = parseInt(eventTypeId);
      if (_created[eventTypeIdInt]) {
         var k;
         for (k in _created[eventTypeIdInt]) {
            var eventId = parseInt(_created[eventTypeIdInt][k].events_id);
            map.events[eventTypeIdInt][eventId] = _created[eventTypeIdInt][k];    
         }            
      }
   }
   handleEventsLayers();
};


Map.prototype.initPlaces = function () {
   this.placesLayer.removeChildren();
   var p;
   for (p in this.availablePlaces) {
      var poly = new Kinetic.Line({
         points: this.availablePlaces[p].places_polygon.split(','),
         strokeWidth: 4,
         opacity: 0.8,
         shadowColor: "black",
         shadowEnabled: true,
         shadowOpacity: 0.8,
         shadowOffsetX: -3,
         shadowOffsetY: 3,
         closed: true
      });
      poly.placeId = this.availablePlaces[p].places_id;
      poly.on('mouseover', function () {
         this.setStroke('white');
         map.placesLayer.draw();
      });
      poly.on('mouseout', function () {
         this.setStroke('');
         map.placesLayer.draw();
      });
      poly.on('click', function (e) {
         var mousePos = map.stage.getPointerPosition();
         $('#event_place_id').val(this.placeId);
         if ($('#events_info:visible')) {
            $('#events_info').hide();
         }
         $('#event_form').show('fast').css({left: mousePos.x + $("#container").position().left, top: mousePos.y + $("#container").position().top});
      });
      this.placesLayer.add(poly);
   }
   this.placesLayer.draw();
};

Map.prototype.drawEventsNumbers = function () {
   var p;
   var e;
   for (p in this.places) {
      for (e in eventsObjects) {
         if (this.places[p].circles.length == 0) {
            continue;
         }
            // alert(this.places[p])
         if (this.places[p].circles[eventsObjects[e].id] < 2) {
            continue;
         }
         var center = getCenter(polygonFromString(this.places[p].places_polygon));

         center.x += eventsObjects[e].offsetX;
         center.y += eventsObjects[e].offsetY;

         var x = center.x + parseInt(bigCircleRadius / 2) + 3;
         var y = center.y - parseInt(bigCircleRadius / 2) - 3;

         var circle = new Kinetic.Circle({
            x: x, //!
            y: y,
            radius: littleCircleRadius,
            fill: 'purple',
            opacity: 0.9,
            strokeEnabled: false
         });
//
         this.eventsLayer.add(circle);


         var circleText = new Kinetic.Text({
            x: x - ((this.places[p].circles[eventsObjects[e].id] > 9) ? 8 : 4),
            y: y - 7,
            text: this.places[p].circles[eventsObjects[e].id],
            fontSize: 15,
            fontFamily: 'Calibri',
            fill: 'white'
         });
//
         this.eventsLayer.add(circleText);
      }
   }
   this.eventsLayer.draw();
};


function handleEventsLayers() {
   map.clearEvents(); //удаление всего связанного с событиями
   $('#layers input[name="events_layer"]:checked').each(function () {
      map.addEvents($(this).attr('id').split('_')[2]); //например event_view_service --  
   });
   map.renderEvents();
}

Map.prototype.clearEvents = function () {
   this.eventsLayer.removeChildren();
   this.zerosEventsCirclesForPlaces();  //создает и обнуляет счетчики кружочков событий
};

Map.prototype.zerosEventsCirclesForPlaces = function () {
   var p;
   var e;
   for (p in this.places) {
      this.places[p].circles = [];
      for (e in eventsObjects) {
         this.places[p].circles[eventsObjects[e].id] = 0;
      }
   }
};

Map.prototype.renderEvents = function () {
   this.drawEventsNumbers();
   this.eventsLayer.draw();
};

function eventOnClick() {
   var p;
   var events = [];
   var cachedEvents = [];
   for (p in map.events[this.eventTypeId]) {
      if (map.events[this.eventTypeId][p].events_place_id == this.placeId) {
         if (map.events[this.eventTypeId][p].events_id in map.cachedEvents) {
            cachedEvents.push(map.events[this.eventTypeId][p].events_id);
         } else {
            events.push(map.events[this.eventTypeId][p].events_id);
         }
      }
   }
   if (events.length > 0) {
      $.ajax({
         type: 'POST',
         url: '/scripts/handlers/handler.Map.php',
         data: {
            action: "getEventInfo",
            data: events,
            floor: map.floor
         },
         success: function (data) {
            if (data.hasOwnProperty('result')) {
               if (data.result) {
                  map.cachedEvents = $.extend(map.cachedEvents, data.data);
               } else {
                  alert(data.message);
               }
            } else {
               alert('Unknown error!');
            }
         },
         dataType: 'json',
         async: false
      });
   }

   events = events.concat(cachedEvents);
   var e;
   var text = '';
   var eventData;
   for (e = 0; e < events.length; e++) {
      eventData = map.cachedEvents[events[e]];
      text += '<article>';
      text += '<img src="' + (eventData.users_photo_id ? '/scripts/uploads/' + eventData.users_photo_id + '_s.jpg' : '/img/avatar_small.jpg') + '" class="avatar" /><div class="right_info">';
      text += '<div class="header"><h1><a href="/profile/?user_id=' + eventData.users_id + '">' + eventData.users_name + ' ' + eventData.users_surname + ':</a></h1>';
      text += '<date>' + eventData.events_creation_date + '</date></div>';
      text += '<h2>' + eventData.events_header + '</h2>';
      if (this.eventTypeId == 2) {
         text += '<span class="due_date">Дата начала: <date>' + eventData.events_due_date + '</date></span>';
      }
      text += '<p>' + eventData.events_description + '</p>';
      text += '</div></article>';
   }
   var mousePos = map.stage.getPointerPosition();
//            var mousePos = {x: 10, y: 10};
   $('div.events_info_data').html(text);
   $('#events_info > h1').text(rus_names[this.eventTypeId]);
   if ($('#event_form:visible')) {
      $('#event_form').hide();
   }
   $('#events_info').show('fast').css({left: mousePos.x + $("#container").position().left, top: mousePos.y + $("#container").position().top});
}


Map.prototype.addEvents = function (eventType) {   //строка типа party
   eventTypeId = eventsObjects[eventType].id;
   var events = this.events[eventTypeId];
   var e;
   for (e in events) {
      var placeId = events[e].events_place_id;
      if (this.places[placeId].circles[eventTypeId] > 0) {
         this.places[placeId].circles[eventTypeId]++;
         continue;
      }
      this.places[placeId].circles[eventTypeId]++;
      var e1;
      var e2;
      var center = getCenter(
         polygonFromString(this.places[placeId].places_polygon)
      );
      center.x += eventsObjects[eventType].offsetX;
      center.y += eventsObjects[eventType].offsetY;
      var circle = new Kinetic.Circle({
         x: center.x, //!
         y: center.y,
         radius: bigCircleRadius,
         fill: eventsObjects[eventType].color,
         opacity: 0.9,
         shadowColor: "black",
         shadowEnabled: true,
         shadowOpacity: 0.7,
         shadowOffsetX: -5,
         shadowOffsetY: 5,
         strokeEnabled: false
      });


      circle.eventId = e;
      circle.eventTypeId = eventTypeId;
      circle.placeId = placeId;

      circle.on('click', eventOnClick);

      this.eventsLayer.add(circle);
      var icon = new Kinetic.Image({
         x: center.x - 25,
         y: center.y - 25,
         image: eventsObjects[eventType].imageIcon
      });

      icon.eventId = e;
      icon.eventTypeId = eventTypeId;
      icon.placeId = placeId;
      icon.on('click', eventOnClick);

      this.eventsLayer.add(icon);
   }
};


function getPos(el) {
   for (var lx = 0, ly = 0;
      el != null;
      lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
   return {x: lx, y: ly};
}


Map.prototype.changeScale = function (new_scale) {
    if (new_scale > min_scale && new_scale < max_scale) {
//        var d = document.getElementById('field');
//        var canvasPos = getPos(d);
//        var absPos = this.stage.getAbsolutePosition();
////        var mousePos = {x: this.stage.width / 2, y: this.stage.height / 2};
////
////        alert(this.stage.getWidth);
//        var smallCalc = (this.stage.getWidth / 2 - absPos.x - canvasPos.x) / this.scale;
//        var smallCalcY = (this.stage.getHeight / 2 - absPos.y) / this.scale;
//
//        var endCalc = (this.stage.getWidth / 2 - canvasPos.x) - new_scale * smallCalc;
//        var endCalcY = (this.stage.getHeight / 2 - - canvasPos.y) - new_scale * smallCalcY;

//        this.stage.setPosition(endCalc, endCalcY);
//        alert(JSON.stringify(this.eventsLayer.scale));
      this.imageLayer.scaleX(new_scale);
      this.imageLayer.scaleY(new_scale);

      this.placesLayer.scaleX(new_scale);
      this.placesLayer.scaleY(new_scale);

      this.eventsLayer.scaleX(new_scale);
      this.eventsLayer.scaleY(new_scale);

//        alert(JSON.stringify(this.eventsLayer.scale));

      this.imageLayer.draw();
      this.placesLayer.draw();
      this.eventsLayer.draw();

//        var eventForm = $('#event_form');
//        if (eventForm.css('display') != 'none') {
////            var x = map.activePlace.x;
////            var y = map.activePlace.y;
////            console.log('x: ' + x);
////            console.log('y: ' + y);
////            eventForm.css({left: x, top: y});
//        }

      this.scale = new_scale;
   }
};

function clearEventForm() {
   $('#event_header').val('');
   $('#event_description').val('');
}

$(function () {
   map.init($('#floor_select').val());
   map.lastUpdatedDate = getCurrentDate();

   $('#layers input[name="events_layer"]').change(handleEventsLayers);
   $("#slider").slider({value: map.scale,
      min: min_scale,
      max: max_scale,
      range: false,
      step: 0.1,
      slide: function (event, ui) {
         map.changeScale(ui.value);
         handleEventsLayers();
      }});

   $('#floor_select').change(function () {
      map.init($('#floor_select').val());
      handleEventsLayers();
      clearEventForm();
   });

   $('#event_form_close').click(function () {
      $('#event_form').hide();
   });

   $('#events_info_close').click(function () {
      $('#events_info').hide();
   });

   $('#event_datetime').datetimepicker({
      lang: 'ru',
      format: 'Y-m-d H:i:s'
   });

   $('#event_type').change(function () {
      if ($(this).val() == '2') {
         $('#event_datetime').show();
      } else {
         $('#event_datetime').hide();
      }
   });

   $('#event_form form').submit(function () {
      var event_type = $('#event_type').find('option:selected').val(),
      place_id = $('#event_place_id').val(),
      event_id = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type, $('#event_datetime').val());
      if (!(event_type in map.events)) {
         map.events[event_type] = [];
      }
      map.events[event_type][event_id] = {
         events_id: event_id,
         events_place_id: place_id
      };
        //TODO render events of this type if not
      handleEventsLayers();
      clearEventForm();
      $('#event_form').hide();
      return false;
   });
});
   // $('#event_form_close').click(function () {
   //    $('#event_form').hide();
   // });

   // $('#events_info_close').click(function () {
   //    $('#events_info').hide();
   // });

   // $('#event_datetime').datetimepicker({
   //    lang: 'ru',
   //    format: 'Y-m-d H:i:s'
   // });

   // $('#event_type').change(function () {
   //    if ($(this).val() == '2') {
   //       $('#event_datetime').show();
   //    } else {
   //       $('#event_datetime').hide();
   //    }
   // });

   // $('#event_form form').submit(function () {
   //    var event_type = $('#event_type').find('option:selected').val(),
   //    place_id = $('#event_place_id').val(),
   //    event_id = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type, $('#event_datetime').val());
   //    if (!(event_type in map.events)) {
   //       map.events[event_type] = [];
   //    }
   //    map.events[event_type][event_id] = {
   //       events_id: event_id,
   //       events_place_id: place_id
   //    };
   //      //TODO render events of this type if not
   //    handleEventsLayers();
   //    clearEventForm();
   //    $('#event_form').hide();
   //    return false;
   // });
// });
// }

var map = new Map();