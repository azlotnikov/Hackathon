function addEvent(place_id, header, description, event_type) {
    var last_id = null;
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "processEvent",
            md: 'ins',
            data: {
                header: header,
                description: description,
                event_type: event_type,
                place_id: place_id
//            due_date: due_date
            }
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result) {
                    last_id = data.last_id;
                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        async: false,
        dataType: 'json'
    });
    return last_id;
}

function editEvent(eid, header, description, event_type) {
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "processEvent",
            md: 'upd',
            data: {
                eid: eid,
                header: header,
                description: description,
                event_type: event_type
//                place_id: place_id
//            due_date: due_date
            }
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result) {

                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        dataType: 'json'
    });
}