<?php
/*
 * Template Name: Listing Offices
 * Description: A Page Template where we listing out offices
 */

get_header();

    if( have_posts() ):
        while( have_posts() ): the_post();
            the_content();
        endwhile;
    endif;
    ?>

	<div class="container">
		<div class="row">

			<div class="col-sm-3">
				<div class="search-sidebar">

					<div id="location-filter">
					    <?php echo get_location_filters(); ?> 
					</div>

					<div id="branch-filter">
					    <?php echo get_branch_filters(); ?>
					</div>

				</div>
			</div>

			<div class="col-sm-9">
				<div class="row">
					<div class="col-md-12">
						<div id="filter-results">
						
						<!-- The Result will go here -->

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<?php 
get_footer();