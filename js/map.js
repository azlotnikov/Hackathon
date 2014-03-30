//Array.prototype.unique = function () {
//    var a = this.concat();
//    for (var i = 0; i < a.length; ++i) {
//        for (var j = i + 1; j < a.length; ++j) {
//            if (a[i] === a[j])
//                a.splice(j--, 1);
//        }
//    }
//
//    return a;
//};


var eventsObjects = {
    'service': {
        id: 1,
        offsetX: -23,
        offsetY: 21,
        color: '#37b0fa'
    },
    'party': {
        id: 2,
        offsetX: 0,
        offsetY: -20,
        color: '#e42d3a'
    },
    'leisure': {
        id: 3,
        offsetX: 23,
        offsetY: 21,
        color: 'pink'
    }
};


var min_scale          = 0.3,  //минимальный масштаб карты
    max_scale          = 1,    //максимальный мастштаб карты
    bigCircleRadius    = 40,   //радиус большого кружка события
    littleCircleRadius = 10;   //радиус маленького кружка события

function Map() {               //самый главный объект карта
    this.scale = 0.7;         //начальный масштаб
    this.places = {};          //массив всех мест: ключ - id места
    this.events = {};          //массив всех событий
//  this.activePlace  = {};
    this.cachedEvents = {};
    this.stage = new Kinetic.Stage({        //канвас
        container: 'container',
        width: $(document).width() - 100,
        height: $(document).height() - 100,
        draggable: true
    });
}

function loadIcons() {
    eventsObjects["service"].imageIcon = new Image();
    eventsObjects["service"].imageIcon.onload = function () {};
    eventsObjects["service"].imageIcon.src = '/img/icon_party.png';
//
    eventsObjects["party"].imageIcon = new Image();
    eventsObjects["party"].imageIcon.onload = function () {};
    eventsObjects["party"].imageIcon.src = '/img/icon_party.png';
//
    eventsObjects["leisure"].imageIcon = new Image();
    eventsObjects["leisure"].imageIcon.onload = function () {};
    eventsObjects["leisure"].imageIcon.src = '/img/icon_party.png';
}

Map.prototype.init = function () {
    this.getInitInfo();                             //получение информации обо всем на карте ajax

    var layer    = new Kinetic.Layer(),
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

        map.imageLayer.add(imageMap);
        map.imageLayer.draw();

        layer.draw();
        map.initPlaces();       //нарисовать скрытый слой с местами
        loadIcons();
        handleEventsLayers();   //определяет какие события из чекбоксов нарисовать

        imageMap.on("click", function () {  //скрыть формочку при клике на пустое место
            $("#event_form").hide();
            $('#events_info').hide();
        });
    };

    imageObj.src = '/img/map.jpg';



};

Map.prototype.getInitInfo = function () {
    var $this = this;
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "getInitInfo",
            floor: "1"
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result) {
                    $this.places = data.data.places;
                    $this.events = data.data.events;
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

Map.prototype.initPlaces = function () {
    this.placesLayer.removeChildren();
    var p;
    for (p in this.places) {
        var poly = new Kinetic.Line({
            points: this.places[p].places_polygon.split(','),
            strokeWidth: 6,
            opacity: 0.8,
            closed: true
        });
        poly.placeId = this.places[p].places_id;
        poly.on('mouseover', function () {
            this.setStroke('red');
            map.placesLayer.draw();
        });
        poly.on('mouseout', function () {
            this.setStroke('');
            map.placesLayer.draw();
        });
        poly.on('click', function (e) {
            var mousePos = map.stage.getPointerPosition();
            $('#event_place_id').val(this.placeId);
            $('#event_form').show('fast').css({left: mousePos.x + $("#container").position().left, top: mousePos.y + $("#container").position().top});
            //map.activePlace = this;
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
                x: x - 4,
                y: y - 7,
                text: this.places[p].circles[eventsObjects[e].id],
                fontSize: 17,
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
        var center = getCenter(polygonFromString(this.places[placeId].places_polygon));
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

        circle.on('mousedown', function () {
//            alert(JSON.stringify(map.cachedEvents));
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
                        data: events
                    },
                    success: function (data) {
//                        alert(JSON.stringify(data));
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
                text += '<a href="#">'+ eventData.events_header + '</a><br>';
                text += eventData.events_description + '<br>';
                text += eventData.events_creation_date + '<br>';
                text += '<a href="#">'+ eventData.users_name + ' ' + eventData.users_surname + '</a><br>';
            }
            var mousePos = map.stage.getPointerPosition();
//            var mousePos = {x: 10, y: 10};
            $('#events_info_data').html(text);
            $('#events_info').show('fast').css({left: mousePos.x + $("#container").position().left, top: mousePos.y + $("#container").position().top});

        });

        this.eventsLayer.add(circle);
        var icon = new Kinetic.Image({
            x: center.x - 25,
            y: center.y - 25,
            image: eventsObjects[eventType].imageIcon
        });

        this.eventsLayer.add(icon);

    }
};

/*
 function getPos(el) {
 for (var lx = 0, ly = 0;
 el != null;
 lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
 return {x: lx, y: ly};
 }


 Map.prototype.changeScale = function (new_scale) {
 if (new_scale > min_scale && new_scale < max_scale) {
 var d = document.getElementById('field');
 var canvasPos = getPos(d);
 var absPos = this.stage.getAbsolutePosition();
 //        var mousePos = map.stage.getPosition();

 var smallCalc = (this.stage.width / 2 - absPos.x - canvasPos.x) / this.scale;
 var smallCalcY = (this.stage.height / 2 - absPos.y - canvasPos.y) / this.scale;

 var endCalc = (this.stage.width / 2 - canvasPos.x) - new_scale * smallCalc;
 var endCalcY = (this.stage.height / 2 - canvasPos.y) - new_scale * smallCalcY;

 this.stage.setPosition(endCalc, endCalcY);
 this.imageLayer.scaleX(new_scale);
 this.imageLayer.scaleY(new_scale);

 this.placesLayer.scaleX(new_scale);
 this.placesLayer.scaleY(new_scale);

 this.eventsLayer.scaleX(new_scale);
 this.eventsLayer.scaleY(new_scale);

 this.imageLayer.draw();
 this.placesLayer.draw();
 this.eventsLayer.draw();

 var eventForm = $('#event_form');
 if (eventForm.css('display') != 'none') {
 //            var x = map.activePlace.x;
 //            var y = map.activePlace.y;
 //            console.log('x: ' + x);
 //            console.log('y: ' + y);
 //            eventForm.css({left: x, top: y});
 }

 this.scale = new_scale;
 }
 };
 */


$(function () {
    map.init();

    $('#layers input[name="events_layer"]').change(handleEventsLayers);


    $('#event_form_close').click(function () {
        $('#event_form').hide();
    });

    $('#events_info_close').click(function () {
        $('#events_info').hide();
    });

    $('#event_datetime').datetimepicker({
        lang: 'ru',
        format: 'd.m.Y H:i'
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
            event_id = addEvent(place_id, $('#event_header').val(), $('#event_description').val(), event_type);
        map.events[event_type][event_id] = {
            events_id: event_id,
            events_place_id: place_id
        };
        //TODO render events of this type if not
        handleEventsLayers();
        return false;
    });

});

var map = new Map();