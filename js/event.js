function addEvent(description, type) {
    $.ajax({
        type: 'POST',
        url: '/scripts/handlers/handler.Map.php',
        data: {
            action: "addEvent",
            description: description,
            type: type
        },
        success: function (data) {
            if (data.hasOwnProperty('result')) {
                if (data.result == 'true') {

                } else {
                    alert(data.message);
                }
            } else {
                alert('Unknown error!');
            }
        },
        contentType: 'application/json'
    });
}