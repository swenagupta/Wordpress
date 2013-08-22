<?php
/**
 * The template for displaying Category Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
$cat=$_GET['cat'];
if($cat==608)			/*freeze frame*/
{
include('category-freeze-frame.php');
}
else if($cat==612)		/*PCP*/
{
include('category-point-counterpoint.php');
}
else if($cat==724)		/*PCP conv*/
{
include('category-point-counterpoint-conv.php');
}
else
{
get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<h1 class="page-title"><?php
					printf( '<span>' . single_cat_title( '', false ) . '</span>' );   /*Swena*/
				?></h1>
				<?php
					$category_description = category_description();
					if ( ! empty( $category_description ) )
						echo '<div class="archive-meta">' . '</div>';

				/* Run the loop for the category page to output the posts.
				 * If you want to overload this in a child theme then include a file
				 * called loop-category.php and that will be used instead.
				 */
				get_template_part( 'loop', 'category' );
				?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php } ?>