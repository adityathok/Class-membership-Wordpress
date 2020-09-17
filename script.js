jQuery(function($) {
    function readURL(input,target) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          $('.'+ target).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
    $(document).on('change', '.imgchange', function(e) {
        var target = $(this).attr('class-target');
        readURL(this,target);
    });
    function showPosition(position) {
      x.innerHTML = "Latitude: " + position.coords.latitude + 
      "<br>Longitude: " + position.coords.longitude;
    }
    $(document).on('click','.geolocation', function(){
    	var x = $(this).data('latitude');
    	var y = $(this).data('longitude');
    	var z = $(this).data('frame');
    	navigator.geolocation.getCurrentPosition(function(data){
    	    $(x).val(data.coords.latitude);
    	    $(y).val(data.coords.longitude);
    	    $(z).removeClass('d-none');
    	    $(z +' iframe').attr('src', 'https://maps.google.com/maps?q='+data.coords.latitude+', '+data.coords.longitude+'&z=15&output=embed');
    	});
    });
    $(document).on('click','.resetgeolocation', function(){
    	var x = $(this).data('latitude');
    	var y = $(this).data('longitude');
    	var z = $(this).data('frame');
	    $(x).val('');
	    $(y).val('');
	    $(z).addClass('d-none');
	    $(z +' iframe').attr('src', '');
    });
});
