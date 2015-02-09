<?php
get_header();
?>
		<div class="container">
			
			<header>
				<h3><?php single_cat_title() ?></h3>
			</header>
		
			<div class="posts">
				
				<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
					
				<?php if ( is_sticky() ): ?>
				<article  id='<?php the_ID(); ?>' <?php post_class('sticky'); ?> >
					<?php else: ?>
				<article  id='<?php the_ID(); ?>' <?php post_class(); ?> >
					<?php endif; ?>
					<h4><a href="<?php $links = get_post_custom_values( 'link' );echo $links[0];// the_permalink() ?>"><?php the_title() ?></a></h4>
					<?php the_excerpt() ?>
				</article>
				
				<?php endwhile; ?>
				
			</div>
			
			<div class="pagination">
				
				<?php	
				 global $wp_query;
					$total = $wp_query->max_num_pages;
					// only bother with the rest if we have more than 1 page!
					if ( $total > 1 )  {
					     // get the current page
					     if ( !$current_page = get_query_var('paged') )
					          $current_page = 1;
					     // structure of "format" depends on whether we're using pretty permalinks
					     $format = get_option('permalink_structure')?'page/%#%/':'&page=%#%';
					     echo paginate_links(array(
					          'base' => get_pagenum_link(1) . '%_%',
					          'format' => $format,
					          'current' => $current_page,
					          'total' => $total,
					          'mid_size' => 4,
					          'type' => 'plain'
					     ));
						}
				 ?>
				 
			</div>	
			<?php endif; ?>
		</div>
<?php get_footer()?>