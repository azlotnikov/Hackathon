$(function(){
   $('.getMore').click(function() {
      var $this = $(this);
      $.post(
         "/scripts/handlers/handler.Event.php",
         {
            user_id: $this.attr('data-user-id'),
            cur_amount: $this.attr('data-amount'),
            event_type: $this.attr('data-event')
         },
         function(data) {
            alert(data);
            //append to div with id $this.attr('data-id')
            if (data.result) {
            } else {
               alert(data.message); //как-то красиво выводить
            }
         }
         // , "json"
      );
      return false;
   });
});