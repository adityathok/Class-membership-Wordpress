<?php
/* 
 * This is the Class for poin.
 * Style and Class use Bootsrap 4
 * 2 type for poin = 'plus' and 'minus'
 */
 
Class Poin {
    
    public $wpdb;
    public $tablename;
    public $year;
    public $today;
    
    function __construct($tablename='poin',$year=null){
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $this->wpdb         = $wpdb; 
        $this->tablename    = $wpdb->prefix.$tablename;
        $this->year         = date("Y");
        $this->today        = date("Y-m-d G:i:s");
        $this->create_notif_table();
    }
    
    public function create_notif_table(){
        $sql = "CREATE TABLE IF NOT EXISTS $this->tablename
        (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            id_user varchar(255) NOT NULL,
            type varchar(114) NOT NULL,
            detail text NOT NULL,
            poin varchar(114) NOT NULL,
            date datetime NOT NULL,
            PRIMARY KEY  (id)
        );  
        ";
        dbDelta($sql);
    }
    
    /// add new notif to User ID
    public function add($userid=null,$type=null,$detail=null,$poin=null){
        if($userid && $detail ) {
            
            //insert into database
            $this->wpdb->insert($this->tablename, array(
                'id_user'   => $userid,
                'type'      => $type,
                'detail'    => $detail,
                'poin'      => $poin,
                'date'      => date("Y-m-d G:i:s"),
              )
        	);
        	
        	//update data to usermeta
        	$countpoin = $this->countYear($userid);
        	$count     = $countpoin >= 0 ?$countpoin:0;
        	update_user_meta( $userid, 'poin', $countpoin );
        	
        }
    }
    
    /// deleted notif by ID
    public function delete($id=null) {
        $this->wpdb->delete($this->tablename, array(
            'id'          => $id,
          )
    	);
    }    
    
    /// count poin total by User ID
    public function countTotal($id=null) {
        $dataplus       = $this->wpdb->get_results("SELECT sum(poin) as result_value FROM $this->tablename WHERE id_user = '$id' AND type = 'plus'");
        $dataminus      = $this->wpdb->get_results("SELECT sum(poin) as result_value FROM $this->tablename WHERE id_user = '$id' AND type = 'minus'");
        $countplus      = $dataplus[0]->result_value;
        $countminus     = $dataminus[0]->result_value;
        $result         = $countplus-$countminus;
        return $result;
    } 
    
    /// count poin total this year by User ID
    public function countYear($id=null) {
        $dataplus       = $this->wpdb->get_results("SELECT sum(poin) as result_value FROM $this->tablename WHERE id_user = '$id' AND YEAR(date) = $this->year AND type = 'plus'");
        $dataminus      = $this->wpdb->get_results("SELECT sum(poin) as result_value FROM $this->tablename WHERE id_user = '$id' AND YEAR(date) = $this->year AND type = 'minus'");
        $countplus      = $dataplus[0]->result_value;
        $countminus     = $dataminus[0]->result_value;
        $result         = $countplus-$countminus;
        return $result;
    }
    
    /// get all poin by User ID
    public function getPoinUser($id=null) {
        $getdata = $this->wpdb->get_results("SELECT * FROM $this->tablename WHERE id_user = '$id' AND YEAR(date) = $this->year ORDER BY id DESC");
        //output
        if($getdata){
            ?>
            <div class="table-responsive my-3">
              <table class="table">
                  <thead class="thead-dark">
                    <tr>
                      <th scope="col">Tanggal</th>
                      <th scope="col">Detail</th>
                      <th scope="col">Type</th>
                      <th scope="col">Poin</th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $getdata as $data ): ?>
                      <tr>
                          <td><?php echo $data->date; ?></td>
                          <td><?php echo json_decode($data->detail, true)['caption']; ?></td>
                          <td><?php echo $data->type; ?></td>
                          <td><strong><?php echo $data->poin; ?></strong></td>
                      </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
            </div>
            <?php
        } else {
           echo '<div class="alert alert-secondary"><i class="fa fa-info-circle text-info" aria-hidden="true"></i> Tidak ada notifikasi</div>'; 
        }
        
    }
    
    
}

//run function for Poin
$Poin = new Poin();

//function to get poin by id user
function get_poin($userid){
    $getpoin  = get_user_meta( $userid, 'poin',true );
    if(empty($getpoin) || $getpoin < 0) {
        $result = 0;
    } else {
        $result = $getpoin;
    }
    return $result;
}



