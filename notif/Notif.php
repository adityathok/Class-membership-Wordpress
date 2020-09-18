<?php
/* 
 * This is the Class for notifikasi.
 * Style and Class use Bootsrap 4
 */
 
Class Notif {
    
    public $wpdb;
    public $tablename;
    
    function __construct($tablename='notifikasi'){
        global $wpdb;
        $this->wpdb = $wpdb; 
        $this->tablename = $wpdb->prefix.$tablename;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $this->create_notif_table();
    }
    
    public function create_notif_table(){
        $sql = "CREATE TABLE IF NOT EXISTS $this->tablename
        (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id varchar(255) NOT NULL,
            status varchar(255) NOT NULL,
            body text NOT NULL,
            link text NOT NULL,
            PRIMARY KEY  (id)
        );  
        ";
        dbDelta($sql);
    }
    
    /// add new notif to User ID
    public function add($userid=null,$body=null,$status=null,$link=null){
        if($userid && $body ) {
            $this->wpdb->insert($this->tablename, array(
                'user_id'        => $userid,
                'status'         => $status,
                'body'           => $body,
                'link'           => $link,
              )
        	);
        }
    }
    
    /// change status notif by ID
    public function read($id=null) {
        $this->wpdb->update($this->tablename, array(
            'status'        => '1',
            ),array(
                  'id'      => $id,
            )
    	);
    }
    
    /// deleted notif by ID
    public function delete($id=null) {
        $this->wpdb->delete($this->tablename, array(
            'id'          => $id,
          )
    	);
    }    
    
    /// count notif by User ID
    public function count($id=null) {
        $data = $this->wpdb->get_var("SELECT COUNT(*) FROM $this->tablename WHERE user_id = '$id' AND status = '0'");
        echo $data;
    }
    
    /// get all notif by User ID
    public function getNotifUser($id=null) {
        $notifs = $this->wpdb->get_results("SELECT * FROM $this->tablename WHERE user_id = '$id' ORDER BY id DESC");
        //output
        if($notifs){
            echo '<ul class="list-group">';
                foreach ($notifs as $notif){
                    
                    $status = $notif->status=='0'?'unread':'read text-muted bg-light';
                    $body   = $notif->link?'<a class="d-block" href="'.$notif->link.'">'.$notif->body.'</a>':$notif->body;
                    echo '<li class="list-group-item px-0 '.$status.'">';
                        echo '<div class="row mx-0">';
                            echo '<div class="col-10">'.$body.'</div>';
                            echo '<div class="col-2 text-right">';
                                if($notif->status=='0') {
                                    echo '<a class="text-succes sudah-dibaca px-1" data-id="'.$notif->id.'" ><i class="fa fa-envelope-open-o" aria-hidden="true"></i></a>';
                                }
                                echo '<a class="text-danger hapus-notif px-1" data-id="'.$notif->id.'" ><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                            echo '</div>';
                        echo '</div>';
                    echo '</li>';
                }
            echo '</ul>';
        } else {
           echo '<div class="alert alert-secondary"><i class="fa fa-info-circle text-info" aria-hidden="true"></i> Tidak ada notifikasi</div>'; 
        }
        
    }
    
}

//run function for create table notif
$Notif = new Notif();

function pushnotif($userid,$body,$status,$link){
    $Notif  = new Notif();
    $Notif->add($userid,$body,$status,$link);
}

/*
*Ajax Notification
*/
//read notif
add_action('wp_ajax_readnotif', 'readnotif_ajax');
function readnotif_ajax() {
    $id     = isset($_POST['dataid']) ? $_POST['dataid'] : '';
    $Notif  = new Notif();
    $Notif->read($id);
	echo 'Pesan';
    wp_die();
}
//deleted notif
add_action('wp_ajax_deletenotif', 'deletenotif_ajax');
function deletenotif_ajax() {
    $id = isset($_POST['dataid']) ? $_POST['dataid'] : '';
    $Notif = new Notif();
    $Notif->delete($id);
	echo 'Pesan';
    wp_die();
}

//get notif
add_action('wp_ajax_getnotif', 'getnotif_ajax');
function getnotif_ajax() {
    $iduser = get_current_user_id();
    $Notif = new Notif();
    echo $Notif->count($iduser);
    wp_die();
}

// Shortcode [notifikasi]
function show_notifikasi() {
    $iduser = get_current_user_id();
    $Notif  = new Notif();
    $html   = $Notif->getNotifUser($iduser);
    return $html;
}
add_shortcode( 'notifikasi', 'show_notifikasi' );

