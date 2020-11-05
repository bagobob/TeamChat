$(document).ready(function(){

    $(document).on('click', '.view_data', function(){
     var etu = $(this).attr("id");
     $.ajax({
      url:"../php/SelectActivité.php",
      method:"POST",
      data:{etu:etu},
      success:function(data){
       $('#up').html(data);
       $('#modal_update_user').modal('show');
      }
     });
    });
   });