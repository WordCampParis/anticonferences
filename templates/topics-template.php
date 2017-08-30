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

	<?php elseif ( anticonferences_topics_closed() ) : ?>

		<p class="no-comments"><?php _e( 'La période de proposition des sujets est terminée.', 'anticonferences' ); ?></p>

	<?php else : ?>

		<p class="no-comments"><?php _e( 'Aucun sujet n\'a été proposé jusqu\'à présent. Soyez le premier à en déposer un !', 'anticonferences' ); ?></p>

	<?php endif; ?>

</div><!-- #comments -->
