<?php
/**
 * Topics template.
 *
 * @since  1.0.0
 */
?>

<div id="comments" class="comments-area">

	<?php anticonferences_topics_toolbar(); ?>

	<div id="respond-container">
		<?php anticonferences_topic_form(); ?>
	</div>

	<?php if ( have_comments() ) : ?>

		<ol class="comment-list">
			<?php
				wp_list_comments( array(
					'avatar_size' => 100,
					'style'       => 'ol',
					'short_ping'  => true,
					'type'        => 'ac_topic',
					'walker'      => new AC_Walker_Topic,
				) );
			?>
		</ol>

		<div id="support-container">
			<?php anticonferences_support_form(); ?>
		</div>

		<?php the_comments_pagination( anticonferences_get_topics_pagination_labels() ); ?>

	<?php elseif ( anticonferences_topics_closed() ) : ?>

		<p class="no-comments"><?php esc_html_e( 'Time to suggest new topics is over.', 'anticonferences' ); ?></p>

	<?php else : ?>

		<p class="no-comments"><?php esc_html_e( 'No topics were published yet. Be the first to publish one!', 'anticonferences' ); ?></p>

	<?php endif; ?>

</div><!-- #comments -->
