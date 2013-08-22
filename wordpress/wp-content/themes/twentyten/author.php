<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php
	/* Queue the first post, that way we know who
	 * the author is when we try to get their name,
	 * URL, description, avatar, etc.
	 *
	 * We reset this later so we can run the loop
	 * properly with a call to rewind_posts().
	 */
	if ( have_posts() )
		the_post();
?>

<h1 class="page-title author"><?php printf( "<span class='vcard'><a class='url fn n' href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "' title='" . esc_attr( get_the_author() ) . "' rel='me'>" . get_the_author() . "</a></span>" . " Profile" ); ?></h1>				
<?php
// If a user has filled out their description, show a bio on their entries.
?>
					<div id="entry-author-info">
						<div id="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
						</div><!-- #author-avatar -->
                        <?php
if(get_query_var('author_name')) :
$curauth = get_userdatabylogin(get_query_var('author_name'));
else :
$curauth = get_userdata(get_query_var('author'));
endif;
?>
<div class="postauthor">
<br /><br /><h4><a href="<?php echo $curauth->user_url; ?>">
<?php echo "<b>".$curauth->first_name."</b>"; ?> <?php echo "<b>".$curauth->last_name."</b>"; ?></a></h4>
<?php echo "<table><tr><td><b>Bio: </b></td><td>" .$curauth->description; ?></td></tr>
<?php echo "<tr><td><b>Display Name: </b></td><td>" .$curauth->display_name;?></td></tr>
<?php echo "<tr><td><b>First Name: </b></td><td>" .$curauth->first_name;?></td></tr>
<?php echo "<tr><td><b>Last Name: </b></td><td>" .$curauth->last_name;?></td></tr>
<?php echo "<tr><td><b>Nick Name: </b></td><td>" .$curauth->nickname;?></td></tr>
<?php echo "<tr><td><b>Email: </b></td><td>" .$curauth->user_email;?></td></tr>
<?php echo "<tr><td><b>Login: </b></td><td>" .$curauth->user_login;?></td></tr>
<?php echo "<tr><td><b>User Registered: </b></td><td>" .$curauth->user_registered;?></td></tr>
<?php echo "<tr><td><b>Website: </b></td><td>" .$curauth->user_url;?></td></tr>
</table>
</div>
											</div><!-- #entry-author-info -->

<h1 class="page-title author"><?php printf( __( 'Author Archives: %s', 'twentyten' ), "<span class='vcard'><a class='url fn n' href='" . get_author_posts_url( get_the_author_meta( 'ID' ) ) . "' title='" . esc_attr( get_the_author() ) . "' rel='me'>" . get_the_author() . "</a></span>" ); ?></h1>

<?php
	/* Since we called the_post() above, we need to
	 * rewind the loop back to the beginning that way
	 * we can run the loop properly, in full.
	 */
	rewind_posts();

	/* Run the loop for the author archive page to output the authors posts
	 * If you want to overload this in a child theme then include a file
	 * called loop-author.php and that will be used instead.
	 */
	 get_template_part( 'loop', 'author' );
?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
