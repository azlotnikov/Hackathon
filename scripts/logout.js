$(function() {
   $('header li#logout a').click(function() {
      $.post("/scripts/logout.php", {},
         function(data) {
            location.reload(true);
         });
      return false;
   });
});