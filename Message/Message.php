<?php

/* 
 * This is the Class for notifikasi.
 * Style and Class use Bootsrap 4
 */
 
Class Message {
    
    public $wpdb;
    public $tablename;
    
    function __construct($tablename='message'){
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
            sender varchar(255) NOT NULL,
            receiver varchar(255) NOT NULL,
            status text NOT NULL,
            body longtext NOT NULL,
            date datetime NOT NULL,
            detail longtext NOT NULL,
            PRIMARY KEY  (id)
        );  
        ";
        dbDelta($sql);
    }
    
    /// add new message with User ID
    public function add($sender=null,$receiver=null,$body=null,$detail=null){
        if($sender && $receiver && $body ) {
            $this->wpdb->insert($this->tablename, array(
                'sender'        => $sender,
                'receiver'      => $receiver,
                'body'          => $body,
                'detail'        => $detail,
                'status'        => 'unread',
                'date'          => date('Y-m-d G:i:s', current_time( 'timestamp', 0 )),
              )
        	);
        }
    }
    
    /// change status notif by ID
    public function read($id=null) {
        $this->wpdb->update($this->tablename, array(
            'status'        => 'read',
            ),array(
                  'id'      => $id,
            )
    	);
    }
    
    /// deleted notif by ID
    public function delete($id=null) {
        $this->wpdb->delete($this->tablename, array(
            'id' => $id,
          )
    	);
    }    
    
    /// count notif by User ID
    public function notif($userid=null) {
        if($userid):
            $data = $this->wpdb->get_var("SELECT COUNT(*) FROM $this->tablename WHERE user_id = '$userid' AND status = 'unread'");
            echo $data?$data:'';
        endif;
    }
    
    /// get message by sender ID & receiver ID
    public function getmessage($sender=null,$receiver=null) {
        if($sender&&$receiver):
            $array          = [];
            $tabels         = $this->wpdb->get_results ( "SELECT * FROM $this->tablename WHERE (sender = '".$sender."' AND receiver = '".$receiver."') OR (sender = '".$receiver."' AND receiver = '".$sender."') ORDER BY date DESC");
            if($tabels):
                foreach( $tabels as $data) {
        			$tgl    = $data->date;
        			$tgal[] = date("d-n-Y", strtotime($tgl));
        		}
        		$tgs = array_unique($tgal);
        		function date_sort($a, $b) {
                    return strtotime($a) - strtotime($b);
                }
                usort($tgs, "date_sort");
                
                $array['date']      = $tgs;
                $array['result']    = $tabels;
            endif;
            
            return $array;
            
        endif;
    }
    
    /// get list message by User ID
    public function listmessage($userid) {
        if($userid):
            $getlistuser    = $this->wpdb->get_results ( "SELECT DISTINCT sender,receiver FROM $this->tablename WHERE sender = '$userid' OR receiver = '$userid' ORDER BY date DESC");
            
            $arraylistuser  = [];
            foreach ($getlistuser as $datauser) {
                foreach ($datauser as $duser ) {
                    if($userid!=$duser) {
                        $arraylistuser[] = $duser;
                    }
                }
            }
            $arrayuser  = (array_unique($arraylistuser));
            
            ///all data
            $result     = [];
            foreach($arrayuser as $user):
        			$unread                             = $this->wpdb->get_var("SELECT COUNT(*) FROM $this->tablename WHERE sender = '".$user."' AND receiver = '$userid' AND status = 'unread' ORDER BY id DESC");
        			$lastdate                           = $this->wpdb->get_results ( "SELECT * FROM $this->tablename WHERE (sender = '".$user."' AND receiver = '$userid') OR (sender = '".$userid."' AND receiver = '$user') ORDER BY date DESC LIMIT 1");
        			$result['result'][$user]['unread']  = $unread; 
        			$result['result'][$user]['date']    = $lastdate[0]; 
            endforeach;
            
            $result['user']   = $arrayuser; 
            
            return $result;
        endif;
    }
    
}

//run function for create table Message
$Message = new Message();

function push_message($sender,$receiver,$body,$detail){
    $Message  = new Message();
    $Message->add($sender,$receiver,$body,$detail);
}

