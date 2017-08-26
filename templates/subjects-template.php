<?php
/**
 * Subjects template.
 *
 * @since  1.0.0
 */
?>

<div id="comments" class="comments-area">

	<?php anticonferences_subject_form(); ?>

	<?php if ( have_comments() ) : ?>

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'avatar_size' => 100,
					'style'       => 'ol',
					'short_ping'  => true,
					'type'        => 'ac_subject'
				) );
			?>
		</ol>

	<?php else : ?>

		<p class="no-comments"><?php _e( 'La période de proposition des sujets est terminée.', 'anticonferences' ); ?></p>

	<?php endif; ?>

</div><!-- #comments -->
