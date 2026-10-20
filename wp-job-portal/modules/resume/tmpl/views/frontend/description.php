<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * A resume's skills, as an excerpt inside a resume card.
 *
 * The resume card's twin of job/views/frontend/description.php, and for the
 * same reason: this excerpt existed only in the Elegant Design add-on's copy
 * of the card, which is what forced that add-on to fork the whole card. Now
 * it is Core's, the card is one card, and the look is left to the stylesheet.
 *
 * It follows the same setting as the job card's excerpt - one decision, "do
 * list cards carry an excerpt", rather than one switch per card.
 *
 * @param wpjobportal_myresume Resume object.
 */

if (empty($wpjobportal_myresume->skills)) {
    return;
}

/*
 * Skills are authored text, sometimes with markup. wp_kses_post keeps what a
 * job seeker wrote and drops scripts. The add-on's copy used an empty
 * allow-list, flattening any formatting into one run of text.
 */
?>
<div class="wjportal-resume-list-description">
    <?php echo wp_kses_post($wpjobportal_myresume->skills); ?>
</div>
