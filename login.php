<?php

///Shortcode Login [login-form]
function at_login() {
    ob_start();
    
    if (!is_user_logged_in()) :
        
        $args = array(
        	'echo'           => true,
        	'remember'       => true,
        	'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        	'form_id'        => 'loginform',
        	'id_username'    => 'user_login',
        	'id_password'    => 'user_pass',
        	'id_remember'    => 'rememberme',
        	'id_submit'      => 'wp-submit',
        	'label_username' => 'Username or Email ',
        	'label_password' => 'Password',
        	'label_remember' => 'Remember Me',
        	'label_log_in'   => 'Log In',
        	'value_username' => '',
        	'value_remember' => true
        );
        echo '<div class="container-fluid form-login p-0">';
            wp_login_form( $args ); 
            echo '<div class="text-left">';
                echo '<div><a href="'.wp_lostpassword_url().'" title="Lost Password">Lupa Password ?</a></div>';
                echo '<div id="user_register" class="align-bottom d-inline-block"><a class="ml-2 btn btn-sm btn-outline-dark" href="'.home_url().'/register" title="Lost Password">Daftar</a></div>';
            echo '</div>';
        echo '</div>';
        
        ?>
        <script>
            (function($){
                $('#user_login,#user_pass').addClass('form-control');
                $('#wp-submit').addClass('btn btn-info');
                $('.login-remember').addClass('d-none');
                $('#user_register').insertAfter('#wp-submit');
            })(jQuery);
        </script>
        <?php
    else :
        echo '<div class="alert alert-info">Anda sudah login, masuk ke <a href="'.get_author_posts_url(get_current_user_id()).'"><i class="fa fa-user"></i> Profil</a></div>';
    endif;
    return ob_get_clean();
}
add_shortcode('login-form','at_login');
