<?php
$to         = isset($_GET['to'])?$_GET['to']:'';
$post       = isset($_GET['post'])?$_GET['post']:'';
$message    = isset($_GET['message'])?$_GET['message']:'';

//class Message;
$Message = new Message();

//send message
if(!empty($_POST['sender'])&&!empty($_POST['receiver'])&&!empty($_POST['message'])) {
    $array                  = [];
    $array['sender']        = get_userdata($_POST['sender'])->user_login;
    $array['receiver']      = get_userdata($_POST['receiver'])->user_login;
    $array['post']          = $_POST['post'];
    $array['post-title']    = get_the_title($_POST['post']);
    $detail                 = json_encode($array);
    $Message->add($_POST['sender'],$_POST['receiver'],$_POST['message'],$detail);
}

?>

<?php 
if($to) {
    $namato             = get_user_meta($to, 'first_name', true)?get_user_meta($to, 'first_name', true):get_user_meta($to, 'nickname', true);
    $avatar_to          = get_user_meta($to,'profile_image',true) && wp_get_attachment_image_url(get_user_meta($to,'profile_image',true))?wp_get_attachment_image_url(get_user_meta($to,'profile_image',true)):get_avatar_url($to);
    ?>
    <div class="alert alert-light w-100 d-block border mb-2 px-1">
        <a href="<?= get_author_posts_url(get_current_user_id())?>?page=chat"><i class="fa fa-angle-left fa-2x mx-2 text-muted" aria-hidden="true"></i></a>
        <a href="<?= get_author_posts_url(get_current_user_id())?>?page=chat&to=<?= $to; ?>"><img class="img-100 rounded-circle ml-2" src="<?= $avatar_to; ?>" alt="" width="35px" height="35px"/></a>
        <div class="font-weight-bold mx-2 d-inline-block"><?= $namato; ?></div>
    </div>
    
    <div id="message-boc" class="message-inner">
        <?php
        $dataall = $Message->getmessage(get_current_user_id(),$to);
        if(isset($dataall['date'])&&!empty($dataall['result'])):
            $nmrtgl = 1;
    		foreach ($dataall['date'] as $tg ) {
    		    echo '<div class="date-row" data-tanggal="'.$tg.'" data-urut="'.$nmrtgl.'">';
        		    echo '<div class="tgl-bubble text-center my-2 text-muted"><small>'.$tg.'</small></div>';
        		    
        		    echo '<div class="chat-date-row d-flex flex-column-reverse">';
            		    foreach( $dataall['result'] as $data) {
            		        
                			$id         = $data->id;
                			$pengirim   = $data->sender;
                			$penerima   = $data->receiver;
                			$status     = $data->status;
                			$tgl        = $data->date;
                			$jam        = date("H:i", strtotime($tgl));
                			$tglku      = date("d-n-Y", strtotime($tgl));
                			$detail     = json_decode($data->detail,true);
                			$post_msg   = isset($detail['post'])&&!empty(isset($detail['post']))?$detail['post']:'';
                			
                			//berdasarkan tanggal
                			if ($tglku == $tg) {
                    			//update status pesan
                    			if ((get_current_user_id() == $penerima) && ($status == 'unread') && ($status != 'deleted') ) {
                    				$Message->read($id);
                    			}
                    			if($status == 'deleted') {
                    			    //echo '<div id=boxchat-'.$data->id.' class="chat-bubble mb-3 ml-3 text-right"> <div class="badge alert-info font-weight-normal p-2">Pesan telah dihapus pengirim</div></div>';
                    			} else {
                    			    
                        			$class1 = ($pengirim==get_current_user_id())?'justify-content-end':'justify-content-start';
                        			$class2 = ($pengirim==get_current_user_id())?'sender alert-info mr-2':'receiver alert-secondary ml-2';
                        			
                    				echo '<div id=boxchat-'.$data->id.' class="d-flex boxchat '.$class1.'">';
                    				
                    				    ///show buble message
                        				echo '<div class="chat-bubble alert clearfix w-75 py-1 px-2 mb-2 '.$class2.'" data-date="'.$tgl.'">';
                    				
                        				    //show message include is post
                        				    if($post_msg):
                        				        echo '<a class="post-message card border-dark shadow-sm p-2 mb-2 d-block text-center mt-2" href="'.get_the_permalink($post_msg).'">';
                        				        echo get_the_post_thumbnail( $post_msg, 'thumbnail', array( 'class' => 'mx-auto d-block' ) );
                        				        echo get_the_title($post_msg);
                        				        echo '</a>';
                        				    endif;
                        				
                            				echo '<div class="body-message d-block">'.$data->body.'</div>';
                            				echo '<div class="info-message text-right">';
                                				if (get_current_user_id() == $pengirim) {
                                				    $clastatus = ($status=='read')?'text-success':'';
                                				    echo '<small class="text-secondary mx-1"><i class="fa fa-check '.$clastatus.'" aria-hidden="true"></i></small>';
                                				    // echo '<small class="badge badge-danger float-right font-weight-normal mx-1 hapuspesanchat" data-id="'.$id.'">hapus</small>';
                                				}
                            				    echo '<small class="clock-message">'.$jam.'</small>';
                            				echo '</div>';
                            			echo '</div>';
                    				echo '</div>';	
                    			}
                			}
                		}
                	echo '</div>';
                	
        		echo '</div>';
        	$nmrtgl++;
    		}
        endif;
        ?>
    </div>
    
    <form class="vchat w-100" id="formpesan" name="input" action="<?= get_author_posts_url(get_current_user_id())?>?page=chat&to=<?= $to; ?>" method="POST">
		<div class="form-group">
			<textarea name="message" id="form-pesan" class="form-control" rows="3"><?= $message; ?></textarea>
		</div>
		<div class="form-group text-right">
			<button title="Kirim" class="btn btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i> Kirim</button>
		</div>
		<input type="hidden" name="sender" value="<?= get_current_user_id(); ?>" />
		<input type="hidden" name="receiver" value="<?= $to; ?>" />
		<input type="hidden" name="post" value="<?= $post; ?>" />
	</form>
	
	<script>
	    jQuery(function($){
	        
            var objDiv = document.getElementById("message-boc");
            objDiv.scrollTop = objDiv.scrollHeight;
            objDiv.scrollIntoView();
            
            var dates = [];
            $('.chat-bubble').each(function(index, element) {
                var date = $(element).data('date');
                dates.push(date);
            });
            var maxDate = dates.reduce(function (a, b) { return a > b ? a : b; });
            console.log(maxDate);
	    });
	</script>
	
<?php } else {
    $datalist = $Message->listmessage(get_current_user_id());
    // print_r($datalist);
    if($datalist):
        echo '<div class="list-group list-group-flush">';
        foreach($datalist['result'] as $iduser => $data){
            
            $namalist   = get_user_meta($iduser, 'first_name', true)?get_user_meta($iduser, 'first_name', true):get_user_meta($iduser, 'nickname', true);
            $avalist    = get_user_meta($iduser,'profile_image',true) && wp_get_attachment_image_url(get_user_meta($iduser,'profile_image',true))?wp_get_attachment_image_url(get_user_meta($iduser,'profile_image',true)):get_avatar_url($iduser);
            
            echo '<a class="list-group-item px-0" href="'.get_author_posts_url(get_current_user_id()).'?page=chat&to='.$iduser.'">';
                echo '<div class="row">';
                    echo '<div class="col-2 col-md-1 pr-0 pr-md-2 align-self-center"><img class="img-100 rounded-circle" src="'.$avalist.'" alt="" /></div>';
                    echo '<div class="col-7 col-md-9 font-weight-bold align-self-center">';
                        echo $namalist;
                    	echo ($data['unread'])?'<div class="badge badge-success float-right font-weight-normal mx-1 jum-chat">'.$data['unread'].'</div>':'';
                    echo '</div>';
                    echo '<div class="col-3 col-md-2 align-self-center text-right"><small>'.date("H:i", strtotime($data['date']->date)).'</small></div>';
        		echo '</div>';
            echo '</a>';
            
        }
        echo '</div>';
    else:
        echo '<div class="alert alert-secondary">Tidak ada riwayat pesan</div>';
    endif;
} ?>
