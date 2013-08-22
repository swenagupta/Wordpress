<?php

// outputs the love it link
function li_love_link($love_text = null, $loved_text = null) {

	global $user_ID, $post;

	// only show the link when user is logged in and on a singular page
	if(is_user_logged_in()) {

		ob_start();
	
		// retrieve the total love count for this item
		$love_count = li_get_love_count($post->ID);
		
		// our wrapper DIV
		echo '<div class="love-it-wrapper">';
		
			$love_text = is_null($love_text) ? __('Love It', 'love_it') : $love_text;
			$loved_text = is_null($loved_text) ? __('You have loved this', 'love_it') : $loved_text;
			
			// only show the Love It link if the user has NOT previously loved this item
			if(!li_user_has_loved_post($user_ID, get_the_ID())) {
				echo '<a href="#" class="love-it" data-post-id="' . get_the_ID() . '" data-user-id="' .  $user_ID . '">' . '<img src="http://localhost/wordpress-3.5/wordpress/wp-content/plugins/love-it/heart.png" />'.'</a> (<span class="love-count">' . $love_count . '</span>)';
			} else {
				// show a message to users who have already loved this item
				echo '<span class="loved">' . '<img src="heart.png" />' . ' (<span class="love-count">' . $love_count . '</span>)</span>';
			}
		
		// close our wrapper DIV
		echo '</div>';
		
		// append our "Love It" link to the item content.
		$link = ob_get_clean();
	}
	return $link;
}

// adds the Love It link and count to post/page content automatically
function li_display_love_link($content) {
	if(is_singular() && is_user_logged_in()) {
		$content .= li_love_link();
	}
	return $content;
}
add_filter('the_content', 'li_display_love_link', 100);