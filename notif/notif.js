jQuery(function($) {
  function infonotif(){
      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: "action=getnotif",  
        success:function(data) {
                if($(".menu-notif .badge-menu").length === 0 && data!=='0') {
                    $(".menu-notif.menu-item > a").append('<span class="badge-menu">'+data+'</span>');
                } else if($(".menu-notif .badge-menu").length !== 0 && data==='0') {
                    $(".menu-notif.menu-item > a .badge-menu").remove();
                } else {
                   $(".menu-notif .badge-menu").html(data); 
                }
        }  
      });
	}
	setInterval(infonotif, 5000);
	$(document).on('click', '.sudah-dibaca', function() {
	    $(this).html('<i class="fa fa-spinner fa-pulse"></i>');
	    var id      = $(this).attr('data-id');
		jQuery.ajax({
            type    : "POST",
            url     : ajaxurl,
            context : this,
            data    : {action:'readnotif', dataid:id },  
            success :function(data) {
                $(this).html('<i class="fa fa-check text-success"></i>');
        },
        });
	});
	$(document).on('click', '.hapus-notif', function() {
	    $(this).html('<i class="fa fa-spinner fa-pulse"></i>');
	    var id      = $(this).attr('data-id');
		jQuery.ajax({
            type    : "POST",
            url     : ajaxurl,
            context : this,
            data    : {action:'deletenotif', dataid:id },  
            success :function(data) {
                $(this).html('<i class="fa fa-check text-success"></i>');
        },
        });
	});
});
