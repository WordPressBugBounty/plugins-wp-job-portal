<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * A job's description, as an excerpt inside a job card.
 *
 * This lived only in the Elegant Design add-on, which is the reason that
 * add-on had to fork the whole job card: the two cards showed the same nine
 * things except this one, and a template cannot be replaced by CSS while it is
 * the only place a piece of content exists.
 *
 * Here it is Core's, so the card is the same card either way and the look is
 * left to the stylesheet.
 *
 * @param wpjobportal_job Job object.
 */

if (empty($wpjobportal_job->description)) {
    return;
}

/*
 * The stored description is authored HTML. wp_kses_post keeps the formatting an
 * employer wrote - paragraphs, lists, emphasis - and drops scripts and event
 * handlers. The add-on's copy passed an empty allow-list to wp_kses, which
 * strips every tag and leaves the markup as one run-on block of text; that was
 * almost certainly not deliberate, and it is not reproduced.
 */
?>
<div class="wjportal-jobs-list-description">
    <?php echo wp_kses_post($wpjobportal_job->description); ?>
</div>
