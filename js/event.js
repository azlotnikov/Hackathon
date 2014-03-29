function addEvent(place_id, header, description, event_type) {
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