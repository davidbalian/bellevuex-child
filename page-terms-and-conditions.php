<?php
/**
 * Template Name: Terms &amp; Conditions
 */
get_header();
?>

<main class="page-legal">

	<section class="page-legal__title-block fade-in">
		<h1><?php te( 'Terms & Conditions' ); ?></h1>
		<p class="page-legal__meta chic-type-meta"><?php te( 'Last updated: May 2026' ); ?></p>
		<hr class="page-legal__divider">
	</section>

	<article class="page-legal__content" data-fade-stagger>

		<section>
			<h2><?php te( 'Arrival and Departure' ); ?></h2>
			<p><?php te( 'Check-in starts at 14:00 on your arrival day. Check-out: 11:00 AM on the departure day.' ); ?></p>
			<p><?php te( 'We offer you an easy and secure key-less access to your suite. This access allows you to check-in without our physical presence. Prior to your arrival, you will receive a unique access code corresponding to your reservation. This code will be valid for the duration of your stay.' ); ?></p>
			<p><?php te( 'Each guest has their personal code for the apartment, as well as the code for building and floor access sent around 14:00 or even earlier if permitted by availability. The ground floor door is locked after 18:00. If you arrive later, use the code provided.' ); ?></p>
			<p><?php te( 'In case you need our physical presence, you can find us daily at our office from 9:00 AM to 16:00 on the ground floor of 11 Theseos Street.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Reservations' ); ?></h2>
			<p><?php te( 'Guests can cancel free of charge 7 days prior to arrival. The guest will be charged the total reservation amount if cancelled within 7 days of arrival.' ); ?></p>
			<p><?php te( "If the guest doesn't show up, the total reservation amount will be charged. Full payment for the reservation will be charged to the credit or debit card provided by the customer." ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Cancellation by Us' ); ?></h2>
			<p><?php te( 'If we need to cancel a reservation, we will contact you immediately. Any payments will be fully refunded.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Room Change' ); ?></h2>
			<p><?php te( 'If a reservation is made for a specific room and that reservation for that room cannot be used due to circumstances beyond our control, we reserve the right to transfer the reservation to a same type room.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Prices' ); ?></h2>
			<p><?php echo wp_kses_post( t( 'Prices include 13% VAT &amp; City tax 0.5% per booking. Environmental tax of €0.50 for the period from 01/11 to 28/02 and €1.50 for the period from 01/03 to 31/10 per night is NOT included.' ) ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Parking' ); ?></h2>
			<p><?php te( 'No parking is available for guests on the premises. Private paid parking is available nearby. Parking is your responsibility.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Damages' ); ?></h2>
			<p><?php te( 'You are responsible for any damages to the property or its contents. We reserve the right to charge for repairs or replacements if the damage is significant. We also reserve the right to charge for missing items.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Liability' ); ?></h2>
			<p><?php te( 'We are not liable for any damage, loss, or injury throughout your stay on the premises.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Lost Items' ); ?></h2>
			<p><?php te( 'If items are found left after your departure, we will return them. However, we may charge for shipping to cover postage and packaging.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Room Cleaning Service' ); ?></h2>
			<p><?php te( 'Towels will be changed every 4 days. However, we also offer the following options for your convenience:' ); ?></p>
			<p><strong><?php te( 'Room Cleaning:' ); ?></strong> <?php te( 'Available upon request for an additional cost of €30 per day. This service includes general cleaning of the room, fresh bed sheets, and towels.' ); ?></p>
			<p><strong><?php te( 'Extra Towels:' ); ?></strong> <?php te( 'Guests may request an additional set of fresh bath towels for an extra cost of €5 per set.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Pets' ); ?></h2>
			<p><?php te( 'Pets are not permitted within the premises.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Social Gatherings and Events' ); ?></h2>
			<p><?php te( 'The hosting of parties or events within the building is strictly prohibited.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Visitor Prohibition Policy' ); ?></h2>
			<p><?php te( 'For the safety and privacy of all our guests, we do not allow access to the property for visitors who are not included in the initial reservation during your stay. Only registered guests are allowed on the premises.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Unaccompanied Minors' ); ?></h2>
			<p><?php te( 'Individuals under the age of 18 must be accompanied by an adult guardian to access the premises. Unaccompanied minors are not granted entry.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Smoking' ); ?></h2>
			<p><?php te( 'Smoking is permitted exclusively in designated smoking areas.' ); ?></p>
		</section>

		<section>
			<h2><?php te( 'Termination Policy' ); ?></h2>
			<p><?php te( 'Chic Center Suites Athens reserves the right, at its discretion, to terminate the stay of an individual without warning if deemed necessary due to unacceptable behavior or actions that may endanger the property or offend others. In such cases, no refunds will be issued.' ); ?></p>
		</section>

		<section>
			<p><?php te( 'We reserve the right to modify these terms and conditions at any time.' ); ?></p>
			<p><?php te( 'By booking one of our suites, you acknowledge and agree to the above.' ); ?></p>
		</section>

	</article>

</main>

<?php get_footer(); ?>
