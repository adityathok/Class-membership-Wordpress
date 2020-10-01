<?php

function at_register() {
    ob_start();
    
    if (!is_user_logged_in()) :
        
        if(!isset($_POST['user_login']) && !isset($_POST['user_email']) && !isset($_POST['user_pass']) ){ 
        ?>
           <form action="" method="POST">
              <div class="form-group">
                <label for="username">Username</label>
                <input type="username" name="user_login" class="form-control checkUsername" id="username" placeholder="Username" aria-describedby="usernameHelp" required>
                <small id="usernameHelp" class="form-text text-muted">username/id anda, tulis tanpa spasi</small>
              </div>
              <div class="form-group">
                <label for="first_name">Nama</label>
                <input type="text" name="first_name" class="form-control" id="first_name" placeholder="Nama" required>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="user_email" class="form-control" id="email" placeholder="Alamat Email" required>
              </div>
              <div class="form-group">
                <label for="pass">Password</label>
                <input type="password" name="user_pass" class="form-control" id="pass" placeholder="Password" required>
              </div>
              <button type="submit" id="submit-register" class="btn btn-info my-2">Daftar</button>
              <a href="<?= home_url();?>/login" class="btn btn-outline-dark btn-sm my-2 ml-2">Login</a>
            </form> 
        <?php
        } else {
            $proses = Member::tambahMember($_POST);
            echo $proses['message'];
            echo $proses['success']==false?'<span class="btn btn-outline-dark btn-sm" onClick="window.history.back();">Kembali</span>':'';
            echo $proses['success']==true?'<a class="btn btn-outline-dark btn-sm" href="'.home_url().'/login">Login</a>':'';
        }
    else :
        echo '<div class="alert alert-info">Anda sudah login, masuk ke <a href="'.get_author_posts_url(get_current_user_id()).'"><i class="fa fa-user"></i> Profil</a></div>';
    endif;
    return ob_get_clean();
}
add_shortcode('register-form','at_register');
