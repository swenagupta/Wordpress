<?php
/**
 * The loop that displays a single post.
 *
 * The loop displays the posts and the post content. See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop-single.php.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.2
 */
?>
<?php $cat=get_categories();
if(in_array(724,wp_get_post_categories($post->ID)))
{
if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="nav-above" class="navigation">
					<div class="nav-previous"><?php previous_post_link( '%link', '&larr; Previous Article' ); ?></div>
					<div class="nav-next"><?php next_post_link( '%link', 'Next Article &rarr;' ); ?></div>
				</div><!-- #nav-above -->

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-meta">
                   
						<?php twentyten_posted_on(); ?>
					</div><!-- .entry-meta -->
                    <div>
                    </div>
					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->




					<div class="entry-utility">
                    
						
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>
                        
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->

				

				<?php comments_template( '', true ); ?>

<?php endwhile;}
else
{?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="nav-above" class="navigation">
					<div class="nav-previous"><?php previous_post_link( '%link', '&larr; Previous Article' ); ?></div>
					<div class="nav-next"><?php next_post_link( '%link', 'Next Article &rarr;' ); ?></div>
				</div><!-- #nav-above -->

				<div id="post-<?php the_ID(); ?>" <?php echo 'class="' . join( ' ', get_post_class1( $class, $post_id ) ) . '"'; ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-meta">
                   
						<?php twentyten_posted_on(); ?>
					</div><!-- .entry-meta -->
                    <div>
                    </div>
					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->




					<div class="entry-utility">
                    
						
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>
                        
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->

				

				<?php comments_template( '', true ); ?>

<?php endwhile; }// end of the loop. ?>
