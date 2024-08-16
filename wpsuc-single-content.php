<?php

if ( ! defined( 'ABSPATH' ) ) exit;

 get_header(); ?>

<div class="wpsuc-main">
<?php if(is_user_logged_in()){
    $user = wp_get_current_user();

    $userlogin = $user->user_login;
    $username_post = sanitize_text_field(get_post_meta($post->ID, 'username', true));

    // Verify user
    if($userlogin === $username_post){?>

        <h1><?php the_title(); ?></h1>
        <?php the_content();

    } else { ?>
        <h1><?php esc_html_e('You are not allowed to see this content', 'wpsuc')?></h1>
        <div class="button-container">
            <a class="wpsuc-btn" href="<?php echo esc_url(home_url()); ?>"><?php esc_html_e('Home', 'wpsuc')?></a>
        </div>
  <?php  }
} else { ?>
    <h1><?php esc_html_e('You are not allowed to see this content, please login to see it', 'wpsuc')?></h1>
    <div class="button-container">
        <a class="wpsuc-btn" href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Login', 'wpsuc') ?></a>
    </div>
<?php } ?>

</div>

<?php get_footer(); ?>