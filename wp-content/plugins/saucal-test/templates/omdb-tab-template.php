<?php
/**
 * Template for the OMDB Tab
 */

// Include WordPress header

?>

<div class="omdb-tab-content">
    <h2><?php _e('OMDB Tab', 'saucal-test'); ?></h2>

    <?php 
        if ( is_active_sidebar( 'omdb-tab-area' ) ) {
            dynamic_sidebar( 'omdb-tab-area' );
        } 
    ?>

</div>

<?php

