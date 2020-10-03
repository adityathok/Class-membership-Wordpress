(function($){
    
    var justgLocation = {
    	storeCountry: function () {
    		if (!justgLocation.getCountry().length) {
    			$.getJSON(justg_params.json.country_url, function (data) {
    				data.sort(function (a, b) {
    					return (a.country_name > b.country_name) ? 1 : ((b.country_name > a.country_name) ? -1 : 0);
    				});
    				Lockr.set(justg_params.json.country_key, data);
    			});
    		}
    	},
    	getCountry: function (search, searchMethod) {
    		var items = Lockr.get(justg_params.json.country_key);
    		if (!items || typeof items === 'undefined') {
    			return [];
    		}
    
    		if (search && search === Object(search)) {
    			return justgLocation.searchLocation(items, search, searchMethod);
    		}
    
    		return items;
    	},
    	storeProvince: function () {
    		if (!justgLocation.getProvince().length) {
    			$.getJSON(justg_params.json.province_url, function (data) {
    				data.sort(function (a, b) {
    					return (a.province_name > b.province_name) ? 1 : ((b.province_name > a.province_name) ? -1 : 0);
    				});
    				Lockr.set(justg_params.json.province_key, data);
    			});
    		}
    	},
    	getProvince: function (search, searchMethod) {
    		var items = Lockr.get(justg_params.json.province_key);
    		if (!items || typeof items === 'undefined') {
    			return [];
    		}
    
    		if (search && search === Object(search)) {
    			return justgLocation.searchLocation(items, search, searchMethod);
    		}
    
    		return items;
    	},
    	storeCity: function () {
    		if (!justgLocation.getCity().length) {
    			$.getJSON(justg_params.json.city_url, function (data) {
    				data.sort(function (a, b) {
    					return (a.city_name > b.city_name) ? 1 : ((b.city_name > a.city_name) ? -1 : 0);
    				});
    				Lockr.set(justg_params.json.city_key, data);
    			});
    		}
    	},
    	getCity: function (search, searchMethod) {
    		var items = Lockr.get(justg_params.json.city_key);
    		if (!items || typeof items === 'undefined') {
    			return [];
    		}
    
    		if (search && search === Object(search)) {
    			return justgLocation.searchLocation(items, search, searchMethod);
    		}
    
    		return items;
    	},
    	storeSubdistrict: function () {
    		if (!justgLocation.getSubdistrict().length) {
    			$.getJSON(justg_params.json.subdistrict_url, function (data) {
    				data.sort(function (a, b) {
    					return (a.subdistrict_name > b.subdistrict_name) ? 1 : ((b.subdistrict_name > a.subdistrict_name) ? -1 : 0);
    				});
    				Lockr.set(justg_params.json.subdistrict_key, data);
    			});
    		}
    	},
    	getSubdistrict: function (search, searchMethod) {
    		var items = Lockr.get(justg_params.json.subdistrict_key);
    		if (!items || typeof items === 'undefined') {
    			return [];
    		}
    
    		if (search && search === Object(search)) {
    			return justgLocation.searchLocation(items, search, searchMethod);
    		}
    
    		return items;
    	},
    	searchLocation: function (items, search, searchMethod) {
    		if (searchMethod === 'filter') {
    			return items.filter(function (item) {
    				return justgLocation.isLocationMatch(item, search);
    			});
    		}
    
    		return items.find(function (item) {
    			return justgLocation.isLocationMatch(item, search);
    		});
    	},
    	isLocationMatch: function (item, search) {
    		var isItemMatch = true;
    		for (var key in search) {
    			if (!item.hasOwnProperty(key) || String(item[key]).toLowerCase() !== String(search[key]).toLowerCase()) {
    				isItemMatch = false;
    			}
    		}
    		return isItemMatch;
    	}
    };
    
    justgLocation.storeCountry(); // Store custom country data to local storage.
    justgLocation.storeProvince(); // Store custom province data to local storage.
    justgLocation.storeCity(); // Store custom city data to local storage.
    justgLocation.storeSubdistrict(); // Store custom subdistrict data to local storage.
    
    var justgBackend = {
    	loadForm: function () {
    	    var provinceVal = $('.alamat-provinsi').data('value');
    		var provinceData = justgLocation.getProvince();
    		if (provinceData.length) {
    			for (var i = 0; i < provinceData.length; i++) {
    			    var selected = (provinceData[i].province_id==provinceVal)?'selected':'';
    				$('.alamat-provinsi').append('<option value="'+provinceData[i].province_id+'" '+selected+'>'+provinceData[i].province+'</option>');
    			}
    		}
    		
    	},
    	loadFormCity: function () {
    	    $('.alamat-kota').find('option').remove();
        	var provinceSelected = ($('.alamat-provinsi').val().length)?$('.alamat-provinsi').val():$('.alamat-provinsi').data('value');
    	    if (provinceSelected.length) {
        		var provinceData = justgLocation.getProvince({ province_id: provinceSelected });
        		var cityData = justgLocation.getCity({ province_id: provinceData.province_id }, 'filter');
        		
        	    var cityVal = $('.alamat-kota').data('value');
        		if (cityData.length) {
        			for (var i = 0; i < cityData.length; i++) {
        			    var selected = (cityData[i].city_id==cityVal)?'selected':'';
        				$('.alamat-kota').append('<option value="'+cityData[i].city_id+'" '+selected+'>'+cityData[i].city_name+'</option>');
        			}
        		}
    	    } else {
    	        $('.alamat-kota').append('<option value="">Pilih Kota</option>');
    	    }
    	},
    	loadFormSubdistrict: function () {
    	    $('.alamat-kecamatan').find('option').remove();
    		var citySelected = ($('.alamat-kota').val().length)?$('.alamat-kota').val():$('.alamat-kota').data('value');
    		if (citySelected.length) {
        		var cityData = justgLocation.getCity({ city_id: citySelected });
        		var subdistrictVal = $('.alamat-kecamatan').data('value');
        		if (cityData) {
        			var subdistrictData = justgLocation.getSubdistrict({ city_id: cityData.city_id }, 'filter');
        			if (subdistrictData) {
        				for (var i = 0; i < subdistrictData.length; i++) {
        				    var selected = (subdistrictData[i].subdistrict_id==subdistrictVal)?'selected':'';
                            $('.alamat-kecamatan').append('<option value="'+subdistrictData[i].subdistrict_id+'" '+selected+'>'+subdistrictData[i].subdistrict_name+'</option>');
        				}
        			}
        		}
    	    } else {
    	        $('.alamat-kecamatan').append('<option value="">Pilih Kecamatan</option>');
    	    }
    	},
    };
    
    if ($(".alamat-provinsi")[0]){
        justgBackend.loadForm();
        justgBackend.loadFormCity();
        justgBackend.loadFormSubdistrict();
    }
    function setloadForm(){
        var text = $('.alamat-provinsi option:selected').text();
        $('.alamat-provinsi-name').val(text);
        var text = $('.alamat-kota option:selected').text();
        $('.alamat-kota-name').val(text);
        var text = $('.alamat-kecamatan option:selected').text();
        $('.alamat-kecamatan-name').val(text);
    }
    $(document).on('change','.alamat-provinsi', function(){
        justgBackend.loadFormCity();
        justgBackend.loadFormSubdistrict();
        setloadForm();
    });
    $(document).on('change','.alamat-kota', function(){
        justgBackend.loadFormSubdistrict();
        setloadForm();
    });
    $(document).on('change','.alamat-kecamatan', function(){
        setloadForm();
    });
    
    $(document).on('change','.checkUsername', function(){
        var el = $(this).val();
        if (/\s/.test(el)) {
            $('#usernameHelp').html( $('<span class="alert alert-danger d-block py-1">Tulis tanpa spasi</span>') );
            document.getElementById("submit-register").disabled = true;
        } else {
            $('#usernameHelp').html( $('<span>Username</span>') );
            document.getElementById("submit-register").disabled = false;
        }
    });
    
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
    
    $(document).on('click','.geolocation', function(){
        
        swal("Pastikan aplikasi memiliki ijin mengakses lokasi !");
        
    	var aa = $(this).data('latitude');
    	var bb = $(this).data('longitude');
    	var ab = $(this).data('frame');
    	var cc = ab+'-map';
    	$('#'+ab).html('<div id="'+cc+'"></div>');
    	
    	navigator.geolocation.getCurrentPosition(function(data){
    	    $(aa).val(data.coords.latitude);
    	    $(bb).val(data.coords.longitude);
    	    $('#'+ab).removeClass('d-none');
    	    $('#'+cc).height(350);
        
             // Creating map options
             var mapOptions = {
                center: [data.coords.latitude, data.coords.longitude],
                zoom: 15,
             }
             // Creating a map object
             var map = new L.map(cc, mapOptions);
             
             // Creating a Layer object
             var layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
             
             // Adding layer to the map
             map.addLayer(layer);
             
             // Creating a marker
             var marker = L.marker([data.coords.latitude, data.coords.longitude], {
                draggable: true
            });
             
             // Adding marker to the map
             marker.addTo(map);
             marker.on('dragend', function (e) {
        	    $(aa).val(marker.getLatLng().lat);
        	    $(bb).val(marker.getLatLng().lng);
            });
             
    	});
    });
    $(document).on('click','.resetgeolocation', function(){
    	var x = $(this).data('latitude');
    	var y = $(this).data('longitude');
    	var z = $(this).data('frame');
	    $(x).val('');
	    $(y).val('');
	    $(z).addClass('d-none');
    });
    
})(jQuery);
