$(function(){
   $('.get_more').click(function() {
      var $this = $(this);
      $.post(
         "/scripts/handlers/handler.Event.php",
         {
            user_id: $this.attr('data-user-id'),
            cur_amount: $this.attr('data-amount'),
            event_type: $this.attr('data-event')
         },
         function(data) {
            if (data.result) {
               checkBtn($this, data.data.length);
               var text = '';
               for (var e = 0; e < data.data.length; e++) {
                  var eventData = data.data[e];
                  text += '<article>';
                  text += '<img src="' + (eventData.users_photo_id ? '/scripts/uploads/' + eventData.users_photo_id + '_s.jpg' : '/img/avatar_small.jpg') + '" class="avatar" /><div class="right_info">';
                  text += '<div class="header"><h1><a href="/profile/?user_id=' + eventData.users_id + '">' + eventData.users_name + ' ' + eventData.users_surname + ' (' + 
                          eventData.places_number + '):</a></h1>';
                  text += '<date>' + eventData.events_creation_date + '</date></div>';
                  text += '<h2>' + eventData.events_header + '</h2>';
                  if ($this.attr('data-event') == 2) {
                     text += '<span class="due_date">Дата начала: <date>' + eventData.events_due_date + '</date></span>';
                  }
                  text += '<p>' + eventData.events_description + '</p>';
                  text += '</div></article>';
               }
               $this.before(text);
               $this.attr('data-amount', parseInt($this.attr('data-amount')) + data.data.length);
            } else {
               alert(data.message); //как-то красиво выводить
            }
         }, "json"
      );
      return false;
   });

   function checkBtn($btn, lastInputCount) {
      if (parseInt(lastInputCount) < parseInt($btn.attr('data-loaded-amount'))) {
         $btn.hide();
         return false;
      }
      return true;
   }

   $('.get_more').each(function(){
      var $this = $(this);
      checkBtn($this, $this.attr('data-amount'));
   });

});