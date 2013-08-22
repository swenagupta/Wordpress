<?php
/**
 * The template for displaying Tag Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">
<div id="entry-author-info">
						<div id="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
						</div><!-- #author-avatar -->
                        <?php
if(get_query_var('tag')) :
$curauth = get_userdatabylogin(get_query_var('tag'));
/*else :
$curauth = get_userdata(get_query_var('author'));*/
endif;
?>
<div class="postauthor">
<h4><a href="<?php echo $curauth->user_url; ?>">
<?php echo $curauth->first_name; ?> <?php echo $curauth->last_name; ?></a></h4>
<p><?php echo $curauth->description; ?></p>
<p><?php echo $curauth->display_name;?></p>
<p><?php echo $curauth->first_name;?></p>
<p><?php echo $curauth->last_name;?></p>
<p><?php echo $curauth->nickname;?></p>
<p><?php echo $curauth->user_email;?></p>
<p><?php echo $curauth->user_login;?></p>
<p><?php echo $curauth->user_registered;?></p>
<p><?php echo $curauth->user_url;?></p>
</div>
						<div id="author-description">
							<h2><?php printf( __( 'About %s', 'twentyten' ), get_the_author() ); ?></h2>
							<?php the_author_meta( 'description' ); ?>
						</div><!-- #author-description	-->
					</div><!-- #entry-author-info -->
				<h1 class="page-title"><?php
					printf( __( 'Author Archives: %s', 'twentyten' ), '<span>' . single_tag_title( '', false ) . '</span>' );
				?></h1>

<?php
/* Run the loop for the tag archive to output the posts
 * If you want to overload this in a child theme then include a file
 * called loop-tag.php and that will be used instead.
 */
 get_template_part( 'loop', 'tag' );
?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
