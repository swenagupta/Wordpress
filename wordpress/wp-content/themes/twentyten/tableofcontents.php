<?php
/*
Template Name: Table Of Contents
*/
?>
<?php get_header('editorial'); ?>

		<div id="container">
			<div id="content" role="main">
<h1 class="page-title author">Table Of Contents</h1>
			<?php
			/* Run the loop to output the page.
			 * If you want to overload this in a child theme then include a file
			 * called loop-page.php and that will be used instead.
			 */
			/*get_template_part( 'loop', 'page' );*/
			?>
<?php
            // get all the categories from the database
            $cats = get_categories(); 
 
 $edition=$_GET['edition']; ?>
<div id="toc">
<?php
                // loop through the categries
                foreach ($cats as $cat) {
                    // setup the cateogory ID
                    $cat_id= $cat->term_id;
					if($cat_id!=1)
					{
                    // Make a header for the cateogry
					
                    
                    // create a custom wordpress query
                    $query=new WP_Query(array('category__in' => array( $cat_id ) , 
					'edition'=>$edition));
					if($query->have_posts())
					{
					echo "<br/><h2>".$cat->name."</h2>";
					}
                    // start the wordpress loop!
                    if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
                        <?php // create our link now that the post is setup ?>
                        <a href="<?php the_permalink();?>"><?php the_title(); ?></a>
                        <br/>
 
                    <?php endwhile; endif; // done our wordpress loop. Will start again for each category ?>
                    <?php } ?>
                <?php } // done the foreach statement ?>

</div>			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>


