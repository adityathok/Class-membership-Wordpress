<?php
/**
 * Belajar OOP, mohon maaf.
 * Basic Member Class for Wordpress Memership
 */

 
class Member {
    
    public static $metakey = [
        'user_login'    => [
            'type'      => 'text',
            'title'     => 'Username',
            'desc'      => 'Username untuk login',
            'required'  => true,
        ],
        'first_name'    => [
            'type'      => 'text',
            'title'     => 'Nama',
            'desc'      => 'Nama Lengkap',
            'required'  => false,
        ],
        'user_email'    => [
            'type'      => 'email',
            'title'     => 'Email',
            'desc'      => '',
            'required'  => true,
        ],
        'nohp'     => [
            'type'      => 'text',
            'title'     => 'Nomor Handphone',
            'desc'      => '',
            'required'  => false,
        ],
        'alamat'        => [
            'type'      => 'textarea',
            'title'     => 'Detail Alamat',
            'desc'      => '',
            'required'  => false,
        ],
        'lokasi'        => [
            'type'      => 'geolocation',
            'title'     => 'Lokasi',
            'desc'      => 'dapatkan lokasi, untuk lebih akurat aktifkan GPS',
            'required'  => false,
        ],
	'bio'            => [
	     'type'      => 'textarea',
	     'title'     => 'Bio',
	     'required'  => false,
	],
        'user_pass'     => [
            'type'      => 'password',
            'title'     => 'Password',
            'required'  => true,
        ],
        'kodepos'       => [
            'type'      => 'text',
            'title'     => 'Kode Pos',
            'desc'      => '',
            'required'  => false,
        ],
    ];

    public static function tambahMember($args) {
        // print_r($args['user_login']);
        $password   = isset($args['user_pass'])&&!empty($args['user_pass'])?$args['user_pass']:'';
        $username   = isset($args['user_login'])&&!empty($args['user_login'])?$args['user_login']:'';
        $email      = isset($args['user_email'])&&!empty($args['user_email'])?$args['user_email']:'';
        $role       = isset($args['role'])&&!empty($args['role'])?$args['role']:'subscriber';
        $success    = false;
        if ( !$username ):
            $message = '<div class="alert alert-danger">Maaf, username wajib diisi.</div>';
        elseif ( username_exists($username) ):
            $message = '<div class="alert alert-danger">Maaf, username sudah digunakan.</div>';
        elseif ( empty($email) ):
            $message = '<div class="alert alert-danger">Maaf, format email salah.</div>';
        elseif ( email_exists($email) ):
            $message = '<div class="alert alert-danger">Maaf, email sudah terdaftar.</div>';
        else:
            $userdata = array(
                'user_pass'     => $password,
                'user_login'    => $username,
                'user_email'    => $email,
                'role'          => $role,
            );
            $new_user = wp_insert_user( $userdata );
            
            foreach ($args as $id => $value) {
                if($id!='user_login' || $id!='user_pass' || $id!='user_email' || $id!='role') {
                    add_user_meta($new_user, $id, $value);
                }
            }
            
            $message = '<div class="alert alert-success"><strong>'.$username.'</strong> Berhasil ditambahkan</div>';
            $succses = true;
            
        endif;
	    
        $result             = [];
        $result['message']  = $message;
        $result['success']  = $success;
        
        return $result;
    }
    
    public static function updateMember($args) {
        
        $user_id = $args['ID']?$args['ID']:'';
        $message = '';
        
        if($user_id) {
        	/* Update user password. */
    		if ( !empty($args['user_pass'] ) ) {
    			wp_update_user( array( 'ID' => $user_id, 'user_pass' => esc_attr( $args['user_pass'] ) ) );
    		}
    		
    		/* Update user information. */
    		if ( !empty( $args['user_email'] ) ){
    			if (!is_email(esc_attr( $args['user_email'] ))) {
    				$message .= '<div class="alert alert-warning">Format email yang anda masukkan salah. Silahkan coba lagi.</div>';
    			//} elseif(email_exists(esc_attr( $args['user_email'] )) && (esc_attr( $args['user_email'] ) != $user_info->user_email ) ) {
    			//	$message .= '<div class="alert alert-warning">Email yang anda masukkan sudah dipakai user lain. Silahkan coba lagi.</div>';
    			} else {
    				wp_update_user( array ('ID' => $user_id, 'user_email' => esc_attr( $args['user_email'] )));
    			}
    		}
    		
    		//update meta user
    		foreach ($args as $id => $value) {
    			if (!($id=="user_pass" || $id=="user_email" || $id=="user_login")) {
			    //if not upload file
			    if ($id!='-upload-file') {
				    update_user_meta( $user_id, $id, $value);
			    } else {
				//print_r($value);
				foreach($value as $idfile => $val ) {
				    //upload file
				    $attachment_id = media_handle_upload( $idfile, 1 );
				    //delete previous file 
				    if (get_user_meta($user_id, $idfile,true)) {
							wp_delete_attachment( get_user_meta($user_id, $idfile,true) );
						}
						//update to meta user
				    update_user_meta( $user_id, $idfile, $attachment_id);
				}
			    }
    			}
    		}
    		
    		$message .= '<div class="alert alert-success">Data Berhasil diupdate</div>';
    		
        } else {
            $message .= 'ID User kosong.<br>';
        }
        
        return $message;
		
    }
    
    public static function hapusMember($user_id=null) {
        if($user_id && get_userdata( $user_id )) {
            ///deleted data user
            wp_delete_user( $user_id );
            return true;
        } else {
            return false;
        }
    }
    
    public static function formMember($args=null,$action=null,$arraymeta=null) {
        
        $role       = isset($args['role'])?$args['role']:'subscriber';
        $arraymeta  = !empty($arraymeta)?$arraymeta:self::$metakey;
        $user_info  = $action=='edit'&&!empty($args['ID'])?get_userdata($args['ID']):'';
        
        ///Input data
        if(isset($_POST['inpudata']) && $action=='add') {
            echo self::tambahMember($_POST);
        }
        ///edit data
        if(isset($_POST['inpudata']) && $action=='edit') {
	    if(isset($_FILES)) {
                $_POST['-upload-file'] = $_FILES;
            }
            echo self::updateMember($_POST);
        }
        
        echo '<form name="input" method="POST" id="formMember" action="">';
        
            echo '<input type="hidden" id="role" value="'.$role.'" name="role">';
            
            ///edit data
            if( $action=='edit') {
                echo '<input type="hidden" id="id" value="'.$args['ID'].'" name="ID" readonly>';
            }
            
            //Loop
        	foreach ($arraymeta as $idmeta => $fields ) {
        	    
        		echo '<div class="form-group fields-'.$idmeta.'">';	
        		    $reqstar = (isset($fields['required']) && $fields['required']==true)?'*':'';

        			if (isset($fields['required']) && $fields['required']==true) { $req = 'required'; } else { $req = ''; }
        			if (isset($fields['readonly']) && $fields['readonly']==true) { $read = 'readonly'; } else { $read = ''; }
        			
        			//get value
        			if ($action=='edit') { 
        			    $value = get_user_meta( $args['ID'], $idmeta , true );
        			} elseif (isset($fields['default']) && ($action=='add')) { 
        			    $value = $fields['default']; 
        			} else { 
        			    $value = '';
        			}
        			
        			$condition  = '';
        			$condition2 = '';
        			
        		     	///jika edit dan user_login
				 if($idmeta=='user_login' && $action=='edit') {
				    echo '<div id="'.$idmeta.'" class="form-control" readonly>Username : '.$user_info->user_login.'</div>';
				    $condition  .= '1';
				    $condition2 .= '1';
				 } 
				 ///jika operator dan rayon atau jika tambah anggota keluarga
				 if((current_user_can('operator') && $idmeta=='rayon') || ($idmeta=='rayon' && !empty($idKK))) {
				    $idnya      = !empty($idKK)?$idKK:get_current_user_id();
				    $rayonOP    = get_user_meta( $idnya, 'rayon' , true );
				    echo '<input type="text" class="form-control" id="rayon" value="'.$rayonOP.'" name="rayon" readonly>';
				    $condition .= '1';
				 }
				 ///jika edit dan user_email
				 if($idmeta=='user_email' && $action=='edit') {
				    $condition  .= '1';
				    $condition2 .= '1';
				 }
				 ///jika edit dan user_pass
				 if($idmeta=='user_pass' && $action=='edit') {
				    $condition  .= '1';
				    $condition2 .= '1';
				 }
				 ///jika editpass dan bukan user_pass
				 if($idmeta!='user_pass' && $action=='editpass') {
				    $condition  .= '1';
				    $condition2 .= '1';
				 }  
            		 
            	    //show label             		    
                    if ($fields['type']!=='hidden' && empty($condition2)) {
                        echo '<label for="'.$idmeta.'" class="font-weight-bold">'.$fields['title'].$reqstar.'</label>';
                    }
                    
                    //show field
            		 if (empty($condition)) {
            			
            			//type input text
            			if ($fields['type']=='text') {
            				echo '<input type="text" id="'.$idmeta.'" value="'.$value.'" class="form-control" name="'.$idmeta.'" placeholder="'.$fields['title'].'" '.$req.' '.$read.'>';
            			}
            			//type input textarea
            			if ($fields['type']=='textarea') {
            				echo '<textarea id="'.$idmeta.'" class="form-control" name="'.$idmeta.'" '.$req.' '.$read.'>'.$value.'</textarea>';
            			} 
            			//type input email
            			else if ($fields['type']=='email') {
            				echo '<input type="email" id="'.$idmeta.'" value="'.$value.'" pattern="[^ @]*@[^ @]*" class="form-control" name="'.$idmeta.'" placeholder="'.$fields['title'].'" '.$req.' '.$read.'>';
            			} 
            			//type input date
            			else if ($fields['type']=='date') {
            				echo '<input type="date" id="'.$idmeta.'" value="'.$value.'" class="form-control datepicker" name="'.$idmeta.'" '.$req.' '.$read.'>';
            			}  
            			//type input password
            			else if ($fields['type']=='password') {
            				echo '<input type="password" id="'.$idmeta.'" class="form-control" value="'.$value.'" name="'.$idmeta.'" '.$req.'>';
            			} 
            			//type input option
            			else if ($fields['type']=='option') {
            				echo '<select id="'.$idmeta.'" class="form-control" name="'.$idmeta.'" '.$req.'>';
            					foreach ($fields['option'] as $option1 => $option2 ) {
            					    $option1 = is_numeric($option1)?$option2:$option1;
            						echo '<option value="'.$option1.'"';
            						if ($value==$option1) { echo 'selected';}
            						echo '>'.$option2.'</option>';
            					}
            				echo '</select>';
            			}  
				 
				 
            			//type input alamat
            			else if ($fields['type']=='alamat') {
        			        $provinsi       = isset($value[0][0])?$value[0][0]:'';
        			        $provinsiname   = isset($value[0][1])?$value[0][1]:'';
        			        $kota           = isset($value[1][0])?$value[1][0]:'';
        			        $kotaname       = isset($value[1][1])?$value[1][1]:'';
        			        $kecamatan      = isset($value[2][0])?$value[2][0]:'';
        			        $kecamatanname  = isset($value[2][1])?$value[2][1]:'';
				       //provinsi
				      echo '<div>';
                			    echo '<label for="'.$idmeta.'-provinsi" class="col-form-label">Provinsi</label>';
                				echo '<select id="'.$idmeta.'-provinsi" class="form-control alamat-provinsi" name="'.$idmeta.'[0][]" data-value="'.$provinsi.'" '.$req.'>';
                					echo '<option value="">Pilih Provinsi</option>';
                				echo '</select>';
                				echo '<input type="hidden" id="'.$idmeta.'-provinsi-name" class="alamat-provinsi-name" name="'.$idmeta.'[0][]" value="'.$provinsiname.'" >';
            				echo '</div>';
            				//kota
            				echo '<div>';
                			    echo '<label for="'.$idmeta.'-kota" class="col-form-label">Kota</label>';
                				echo '<select id="'.$idmeta.'-kota" class="form-control alamat-kota" name="'.$idmeta.'[1][]"  data-value="'.$kota.'" '.$req.'>';
                					echo '<option value="">Pilih Kota</option>';
                				echo '</select>';
                				echo '<input type="hidden" id="'.$idmeta.'-kota-name" class="alamat-kota-name" name="'.$idmeta.'[1][]" value="'.$kotaname.'" >';
            				echo '</div>';
            				//kecamatan
            				echo '<div>';
                			    echo '<label for="'.$idmeta.'-kecamatan" class="col-form-label">Kecamatan</label>';
                				echo '<select id="'.$idmeta.'-kecamatan" class="form-control alamat-kecamatan" name="'.$idmeta.'[2][]" data-value="'.$kecamatan.'" '.$req.'>';
                					echo '<option value="">Pilih Kecamatan</option>';
                				echo '</select>';
                				echo '<input type="hidden" id="'.$idmeta.'-kecamatan-name" class="alamat-kecamatan-name" name="'.$idmeta.'[2][]" value="'.$kecamatanname.'" >';
            				echo '</div>';
            			}
            			                        
            			//type input geolocation
            			if ($fields['type']=='geolocation') {
            			    echo '<div>';
            			        $latitude   = isset($value[0])?$value[0]:'';
            			        $longitude  = isset($value[1])?$value[1]:'';
                			    echo '<span class="btn btn-sm btn-info geolocation" data-latitude="#'.$idmeta.'-1" data-longitude="#'.$idmeta.'-2" data-frame="'.$idmeta.'-frame"><i class="fa fa-globe" aria-hidden="true"></i> Dapatkan '.$fields['title'].'</span>';
                			    echo '<span class="btn btn-sm btn-danger resetgeolocation ml-2" data-latitude="#'.$idmeta.'-1" data-longitude="#'.$idmeta.'-2" data-frame="#'.$idmeta.'-frame">Reset</span>';
                				echo '<input type="hidden" id="'.$idmeta.'-1" value="'.$latitude.'" name="'.$idmeta.'[]">';
                				echo '<input type="hidden" id="'.$idmeta.'-2" value="'.$longitude.'" name="'.$idmeta.'[]">';
                				$frame = !empty($latitude)&&!empty($longitude)?'':'d-none';
                				echo '<div id="'.$idmeta.'-frame" class="'.$frame.' my-3">';
                				    if(!empty($latitude)&&!empty($longitude)): ?>
                				        <div id="<?= $idmeta;?>-frame-map"></div>
                				        <script>
                				            (function($){
                				            $( document ).ready(function() {
                				                 $('#<?= $idmeta;?>-frame-map').height(350);
                                                 var mapOptions = {
                                                    center: [<?= $latitude;?>, <?= $longitude;?>],
                                                    zoom: 15,
                                                 }
                                                 var map = new L.map('<?= $idmeta;?>-frame-map', mapOptions);
                                                 var layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
                                                 map.addLayer(layer);
                                                 var marker = L.marker([<?= $latitude;?>, <?= $longitude;?>], {
                                                    draggable: true
                                                });
                                                 marker.addTo(map);
                                                 marker.on('dragend', function (e) {
                                            	    $('#<?= $idmeta;?>-1').val(marker.getLatLng().lat);
                                            	    $('#<?= $idmeta;?>-2').val(marker.getLatLng().lng);
                                                });
                				            });
                				            })(jQuery);
                				        </script>
                				    <?php
                				    endif;
                				echo '</div>';
            				echo '</div>';
            			}
				 
				//type input file
            			if ($fields['type']=='file') {
            			    
            				if($value && wp_get_attachment_url($value)) {
            				    echo '<a href="'.wp_get_attachment_url($value).'" target="_blank" class="d-block my-2"><i class="fa fa-file fa-2x"></i></a>';
            				}
            				
            				echo '<input type="file" id="'.$idmeta.'" class="form-control-file" name="'.$idmeta.'" '.$req.' '.$read.'>';
            			}
            			
            			//type input hidden
            			if ($fields['type']=='hidden') {
            				echo '<input type="hidden" id="'.$idmeta.'" value="'.$value.'" name="'.$idmeta.'">';
            			}
            		
            			if (isset($fields['desc'])&&!empty($fields['desc'])) {
            				echo '<small class="text-secondary text-muted">*'.$fields['desc'].'</small>';				
            			}
        	        }
        		echo '</div>';
        	}
        	//END Loop
        	
    	    echo '<div class="text-right my-3"><button name="inpudata" type="submit" class="btn btn-info simpanUserbaru1"><i class="fa fa-floppy-o" aria-hidden="true"></i> Simpan</button></div>';
	    echo '</form>';	
    }
    
    ///Tampil profil
    public static function lihatMember($user_id=null) {
        if(!empty($user_id) && !empty(get_userdata( $user_id ))):
            
            $userdata   = get_userdata( $user_id );
            $arraymeta  = self::$metakey;
            
            echo '<table class="table">';
        	foreach ($arraymeta as $idmeta => $fields) {
        		$value = get_user_meta($user_id,$idmeta,true);
        		if ($idmeta=="user_login") {
        		    $user_info = get_userdata($user_id);
        		    echo '<tr><td class="font-weight-bold">'.$fields['title'].'</td><td>'.$user_info->user_login.'</td></tr>';
        		}	
        		if (!($idmeta=="user_pass" || $idmeta=="user_email" || $idmeta=="user_login")) {
        			echo '<tr class="fields-'.$idmeta.'">';	
        				echo '<td class="font-weight-bold">'.$fields['title'].'</td>';
				
        				if ($fields['type']=='option') {
        					foreach ($fields['option'] as $option1 => $option2 ) {
        						if ($value==$option1) { echo '<td>'.$option2.'</td>';}
        					}
						
					} else if($fields['type']=='geolocation')  {
					    $latitude   = isset($value[0])?$value[0]:'';
					    $longitude  = isset($value[1])?$value[1]:'';
        				    echo '<td>';
            				    if(!empty($latitude)&&!empty($longitude)): ?>
                				        <div id="<?= $idmeta;?>-frame-map"></div>
                				        <script>
                				            (function($){
                				            $( document ).ready(function() {
                				                 $('#<?= $idmeta;?>-frame-map').height(350);
                                                 var mapOptions = {
                                                    center: [<?= $latitude;?>, <?= $longitude;?>],
                                                    zoom: 15,
                                                 }
                                                 var map = new L.map('<?= $idmeta;?>-frame-map', mapOptions);
                                                 var layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
                                                 map.addLayer(layer);
                                                 var marker = L.marker([<?= $latitude;?>, <?= $longitude;?>]);
                                                 marker.addTo(map);
                				            });
                				            })(jQuery);
                				        </script>
                			     <?php
                			     endif;
					     echo '</td>';
						
					} else if($fields['type']=='file')  {
            				    $file = ($value && wp_get_attachment_url($value))?'<a href="'.wp_get_attachment_url($value).'" target="_blank" class="d-block my-2"><i class="fa fa-file fa-2x"></i></a>':'';
        				    echo '<td>'.$file.'</td>';
						
    					} else if($fields['type']=='alamat')  {
						$provinsi       = isset($value[0][0])?$value[0][0]:'';
						$provinsiname   = isset($value[0][1])?$value[0][1]:'';
						$kota           = isset($value[1][0])?$value[1][0]:'';
						$kotaname       = isset($value[1][1])?$value[1][1]:'';
						$kecamatan      = isset($value[2][0])?$value[2][0]:'';
						$kecamatanname  = isset($value[2][1])?$value[2][1]:'';
            				    	echo '<td>'.$kecamatanname.', '.$kotaname.', '.$provinsiname.'</td>';
        				}  else  {
        					echo '<td>'.$value.'</td>';
        				}	
				
        			echo '</tr>';
        		}
        	}
        	echo '</table>';
            
        endif;
    }
    
    
    
}
