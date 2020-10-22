<?php
/**
 * Belajar OOP, mohon maaf.
 * Basic Frontpost Class for Wordpress Memership
 */

 
class Frontpost {
    
    public static $metakey = [
        'places'        => [
            'type'      => 'taxonomy',
            'title'     => 'Kategori Tempat',
            'desc'      => '',
            'required'  => false,
        ],
        'post_title'    => [
            'type'      => 'text',
            'title'     => 'Nama',
            'desc'      => 'Nama tempat',
            'required'  => true,
        ],
        '_thumbnail_id'=> [
            'type'      => 'featured',
            'title'     => 'Gambar',
            'desc'      => 'Foto Utama',
            'required'  => true,
        ],
        'post_content'  => [
            'type'      => 'textarea',
            'title'     => 'Deskripsi',
            'desc'      => '',
            'required'  => false,
        ],
        'nowa'          => [
            'type'      => 'text',
            'title'     => 'Nomor Whatsapp',
            'desc'      => '',
            'required'  => true,
        ],
        'nohp'          => [
            'type'      => 'text',
            'title'     => 'Nomor Handphone',
            'desc'      => '',
            'required'  => true,
        ],
        'alamat'        => [
            'type'      => 'alamat',
            'title'     => 'Detail Alamat',
            'desc'      => '',
            'required'  => true,
        ],
        'alamat-lengkap'=> [
            'type'      => 'textarea',
            'title'     => 'Alamat Lengkap',
            'desc'      => '',
            'required'  => false,
        ],
        // 'post_category' => [
        //     'type'      => 'category',
        //     'title'     => 'Kategori',
        //     'desc'      => '',
        //     'required'  => false,
        // ],
        'lokasi'        => [
            'type'      => 'geolocation',
            'title'     => 'Lokasi',
            'desc'      => 'dapatkan lokasi, untuk lebih akurat aktifkan GPS',
            'required'  => true,
        ],
    ];
    
    public static function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // trim
        $text = trim($text, '-');
        
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // lowercase
        $text = strtolower($text);
        
        if (empty($text)) {
        return 'n-a';
        }
        return $text;
    }
    
    public static function submitPost($args) {
        
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        
        $message    = [];
        $success    = '';
        
        if($args) {
        	$my_post['post_title']      = $args['post_title'];
        	$my_post['post_content']    = $args['post_content'];
        	$my_post['post_status']     = 'publish'; 
        	$my_post['post_type']       = $args['post_typea'];
        	$my_post['post_author']     = $args['post_author'];
        	$my_post['post_slug']       = self::slugify($args['post_title']);
        	
        	if(isset($args['ID'])&&$args['ID']):
        	  $my_post['ID']            = $args['ID'];  
        	endif;
        	
        	if(isset($args['post_category'])&&$args['post_category']):
        	  $my_post['post_category'] = $args['post_category'];  
        	endif;
        	
    	    $pid = wp_insert_post( $my_post, true );
    	    
    	    //save meta post
    	    foreach ($args as $id => $value) {
                if($id!='post_title' || $id!='post_content' || $id!='post_category' || $id!='taxonomy' || $id!='-upload-file' ) {
                    update_post_meta($pid, $id, $value);
                }
                //taxonomy
                if($id=='taxonomy'){
                    foreach($value as $tax => $tag ) {
                        wp_set_post_terms($pid, $tag, $tax);
                    }
                }
                //-upload-file
                if($id=='-upload-file'){
                    foreach($value as $idfile => $val ) {
			    if(isset($val['name'])&&$val['name']):
			    //upload file
			    $attachment_id = media_handle_upload( $idfile, $pid);
			    //delete previous file 
			    if (get_post_meta($pid, $idfile,true)) {
				wp_delete_attachment( get_post_meta($pid, $idfile,true) );
			    }
		    	    //update to meta user
			    update_post_meta( $pid, $idfile, $attachment_id);
			    endif;
                    }
                }
            }
    	    $message[] = '<div class="alert alert-success">Berhasil</div>';
        } else {
            $message[] = '<div class="alert alert-danger">Parameter Kosong</div>';
        }
        
        
        $result             = [];
        $result['message']  = $message;
        $result['success']  = $success;
        
        return $result;
		
    }
    
    public static function hapusPost($id=null) {
        if($id) {
	    ///get all attachment by id post
            $args = array(
        		'post_type'     => 'attachment',
        		'post_status'   => null,
        		'post_parent'   => $post->ID,
        		); 
        	$attachments = get_posts($args);
            if(count($attachments) > 1) {
                foreach ($attachments as $attachment ) {
        			//delete attachment
        			wp_delete_attachment( $attachment->ID );
                } 
            }
            ///deleted data post
            wp_delete_post( $id, true );
            return true;
        } else {
            return false;
        }
    }
    
    public static function formPost($args=null,$action=null,$arraymeta=null) {
        
        $post_author    = isset($args['ID'])&&!empty(get_post_field('post_author',$args['ID']))?get_post_field('post_author',$args['ID']):get_current_user_id();
        $arraymeta      = !empty($arraymeta)?$arraymeta:self::$metakey;
        
        $antispam       = true;
        
        //check antispam
        if(isset($_POST['g-recaptcha-response']) && empty($_POST['g-recaptcha-response'])) {
            $antispam   = false;
        }
        
        ///submit data
        if(isset($_POST['inpudata']) && $antispam==true) {
	        if(isset($_FILES)&&!empty($_FILES)) {
                $_POST['-upload-file'] = $_FILES;
            }
            $result = self::submitPost($_POST);
            echo implode(" ",$result['message']);
        } else if(isset($_POST['inpudata']) && $antispam==false) {
            echo '<div class="alert alert-danger">Please verify Antispam</div>';
        }
        
        echo '<form name="input" method="POST" id="formPost" action="" enctype="multipart/form-data">';
        
            echo '<input type="hidden" id="post_author" value="'.$post_author.'" name="post_author">';
            
            ///edit data
            if( $action=='edit' && $args['ID']) {
                echo '<input type="hidden" id="id" value="'.$args['ID'].'" name="ID" readonly>';
            }
            
            ///post type
            if( isset($args['post_type']) && $args['post_type']) {
                echo '<input type="hidden" id="id" value="'.$args['post_type'].'" name="post_typea" readonly>';
            }
            
            //Loop
        	foreach ($arraymeta as $idmeta => $fields ) {
        	    
        		echo '<div class="form-group fields-'.$idmeta.'">';	
        		    $reqstar = (isset($fields['required']) && $fields['required']==true)?'*':'';

        			if (isset($fields['required']) && $fields['required']==true) { $req = 'required'; } else { $req = ''; }
        			if (isset($fields['readonly']) && $fields['readonly']==true) { $read = 'readonly'; } else { $read = ''; }
        			
        			//get value
        			if ($action=='edit') { 
        			    $value = get_post_meta( $args['ID'], $idmeta , true );
        			} elseif (isset($fields['default']) && ($action=='add')) { 
        			    $value = $fields['default']; 
        			} else { 
        			    $value = '';
        			}
        			
        			$condition  = '';
        			$condition2 = '';

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
            			//type input number
            			if ($fields['type']=='number') {
            				echo '<input type="number" id="'.$idmeta.'" value="'.$value.'" class="form-control" name="'.$idmeta.'" placeholder="'.$fields['title'].'" '.$req.' '.$read.'>';
            			}
            			//type input textarea
            			if ($fields['type']=='textarea') {
            				echo '<textarea id="'.$idmeta.'" class="form-control" name="'.$idmeta.'" '.$req.' '.$read.'>'.$value.'</textarea>';
            			} 
            			//type input editor
            			if ($fields['type']=='editor') {
            				wp_editor( $value, $idmeta );
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
            			
            			//type input category
            			else if ($fields['type']=='category') {
            				echo '<select id="'.$idmeta.'" class="form-control" name="'.$idmeta.'" '.$req.'>';
                				$categories = get_categories( 
                                    array(
                            			'orderby' => 'name',
                            			'parent'  => 0,
                            			'hide_empty' => 0,
                            		) 
                            	);
                            	
                            	$val = isset($value[0])?$value[0]:'';
                			    if(isset($args['ID'])&&$args['ID']):   
                                    $taxonomy_ids     = wp_get_object_terms($args['ID'], array($idmeta),  array("fields" => "ids"));
                                    $val              = $taxonomy_ids;
                                endif;
                                
            					foreach ($categories as $category ) {
            					    $seletc = in_array($category->term_id,$val)?'selected':'';
            						echo '<option value="'.$category->term_id.'" '.$seletc.'>'.$category->name.'</option>';
            						//child
            						$taxonomies         = array('taxonomy'=>'category');
            						$categories_child   = array('child_of'=> $category->term_id); 
            						$terms              = get_terms($taxonomies, $categories_child);
            						if (sizeof($terms)>0){
                            			foreach ( $terms as $term ) {
                            			    $seletc = in_array($term->term_id,$val)?'selected':'';
                            			    echo '<option value="'.$term->term_id.'" '.$seletc.'>&nbsp;&nbsp;'.$term->name.'</option>';
                            			}
            						}
            					}
            				echo '</select>';
            			}
            			
            			//type input taxonomy
            			else if ($fields['type']=='taxonomy') {
            			    
            			    $val = isset($value[0])?$value[0]:'';
            			    if(isset($args['ID'])&&$args['ID']):   
                                $taxonomy_ids     = wp_get_object_terms($args['ID'], array($idmeta),  array("fields" => "ids"));
                                $val              = $taxonomy_ids;
                            endif;
            			    
            				echo '<select id="'.$idmeta.'" class="form-control" name="taxonomy['.$idmeta.'][]" '.$req.'>';
            						//taxonomy
            						$categories = get_categories( array('taxonomy' => $idmeta,'hide_empty'=> false,));
            						if (sizeof($categories)>0){
                            			foreach ( $categories as $category ) {
                            			    $seletc = in_array($category->term_id,$val)?'selected':'';
                            			    echo '<option value="'.$category->term_id.'" '.$seletc.'>'.$category->name.'</option>';
                    						//child
                    						$taxonomies         = array('taxonomy'=>$idmeta);
                    						$categories_child   = array('child_of'=> $category->term_id); 
                    						$terms              = get_terms($taxonomies, $categories_child);
                    						if (sizeof($terms)>0){
                                    			foreach ( $terms as $term ) {
                                    			    $seletc = in_array($term->term_id,$val)?'selected':'';
                                    			    echo '<option value="'.$term->term_id.'" '.$seletc.'>&nbsp;&nbsp;'.$term->name.'</option>';
                                    			}
                    						}
                            			}
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
                				echo '<input type="hidden" id="'.$idmeta.'-1" value="'.$latitude.'" name="'.$idmeta.'[]" '.$req.'>';
                				echo '<input type="hidden" id="'.$idmeta.'-2" value="'.$longitude.'" name="'.$idmeta.'[]" '.$req.'>';
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
            			
            			//type input featured
            			else if ($fields['type']=='featured') {
            			    
            				$src = ($value && wp_get_attachment_url($value))?wp_get_attachment_image_src($value, 'thumbnail')[0]:'https://dummyimage.com/200x200/d6d6d6/ffffff&text=no+image';
            				echo '<div class="file-upload img-frame mb-2"><img class="prevg ganti-'.$idmeta.'" src="'.$src.'" width="80"/></div>';
            				echo '<input type="file" id="'.$idmeta.'" class="form-control-file imgchange" class-target="ganti-'.$idmeta.'" name="'.$idmeta.'" '.$req.' '.$read.'>';
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
            		
            		    	//type recaptcha
            			if ($fields['type']=='recaptcha' && !empty($fields['sitekey']) && !empty($fields['secret'])) {
            			    echo '<div class="'.$idmeta.' text-right">';
				    echo '<div id="'.$idmeta.'" class="g-recaptcha" data-callback="checkCaptcha" data-expired-callback="expiredCaptcha" data-sitekey="'.$fields['sitekey'].'"></div>';
				    echo '<div id="msg'.$idmeta.'"> </div>';
				    echo '</div>';
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
    public static function lihatPost($idpost=null) {
        
        if(!empty($idpost) && !empty(get_post_field( $idpost ))):
            
            $arraymeta  = self::$metakey;
            
            echo '<table class="table table-lihatPost">';
        	foreach ($arraymeta as $idmeta => $fields) {
        	    
        		$value = get_post_meta($idpost,$idmeta,true);
        		
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
        	echo '</table>';
            
        endif;
    }
    
    
    
}


