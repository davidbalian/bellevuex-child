<?php
/**
 * Data helpers for the Explore Athens page template.
 */

function chic_explore_athens_attractions(): array {
	return [
		[
			'title' => 'Monastiraki',
			'body'  => 'Wander through the vibrant Monastiraki neighborhood, known for its bustling flea market where you can find all sorts of souvenirs, antiques, clothing, and handicrafts. Don\'t forget to explore the narrow streets and discover hidden gems.',
			'image' => chic_upload_url( '10-chic-centre-suites-athens-nearby-attractions.webp' ),
			'alt'   => 'Aerial view of Monastiraki Square with the Acropolis in the background',
		],
		[
			'title' => 'Acropolis',
			'body'  => 'Start your journey at the iconic Acropolis, a UNESCO World Heritage Site and the symbol of Athens. Dominating the city skyline, this ancient citadel houses several historic monuments, including the Parthenon, Erechtheion, Propylaea, and the Temple of Athena Nike. The views of Athens from the Acropolis are breath-taking, especially during sunset.',
			'image' => chic_upload_url( '3-chic-centre-suites-athens-nearby-attractions.webp' ),
			'alt'   => 'The Caryatids of the Erechtheion on the Acropolis of Athens',
		],
		[
			'title' => 'Acropolis Museum',
			'body'  => 'Next to the Acropolis, visit the Acropolis Museum, which showcases an impressive collection of artifacts and sculptures from the Acropolis site. The museum provides valuable insights into the city\'s ancient past and the significance of the monuments you\'ve just seen. Consistently rated as one of the best museums in the world, the Acropolis Museum was founded in 2003 to house every artifact found on the rock and on the surrounding slopes.',
			'image' => chic_upload_url( '2-chic-centre-suites-athens-nearby-attractions.webp' ),
			'alt'   => 'Exterior of the Acropolis Museum in Athens',
		],
		[
			'title' => 'Syntagma Square',
			'body'  => 'Visit Syntagma Square, the central square of Athens, to witness the impressive changing of the guard ceremony in front of the Hellenic Parliament building. The square is also surrounded by shops, cafes, and is a starting point for many walking tours. Named after the Constitution that Otto, the first King of Greece, was obliged to grant in 1843, the square stands in front of the 19th-century Old Royal Palace, which has housed the Greek Parliament since 1934.',
			'image' => chic_upload_url( '4-chic-centre-suites-athens-nearby-attractions-syntagma-square.webp' ),
			'alt'   => 'Syntagma Square with the Hellenic Parliament building and fountain',
		],
		[
			'title' => 'Plaka',
			'body'  => 'After the Acropolis, explore the charming neighborhood of Plaka, located at the foot of the Acropolis hill. This old district is a maze of narrow streets lined with neoclassical buildings, traditional taverns, and souvenir shops. It\'s an excellent place to enjoy a leisurely stroll and get a taste of local life.',
			'image' => chic_upload_url( '5-chic-centre-suites-athens-nearby-restaurants.webp' ),
			'alt'   => 'Outdoor taverna seating along a narrow stairway street in the Plaka neighborhood',
		],
		[
			'title' => 'Ancient Agora',
			'body'  => 'Head to the Ancient Agora, the heart of ancient Athens\' political, social, and commercial life. Here, you can see well-preserved ruins like the Temple of Hephaestus, the Stoa of Attalos, and the Agora Museum, which houses artifacts from the area.',
			'image' => chic_upload_url( '1-chic-centre-suites-athens-nearby-attractions.webp' ),
			'alt'   => 'The Stoa of Attalos in the Ancient Agora with the Acropolis rising behind it',
		],
	];
}

function chic_explore_athens_more(): array {
	return [
		[
			'title' => 'Lycabettus Hill',
			'body'  => 'For a panoramic view of Athens, hike or take a funicular ride up Lycabettus Hill. The hill offers stunning views of the city, especially during the evening when Athens lights up.',
		],
		[
			'title' => 'National Archaeological Museum',
			'body'  => 'For history enthusiasts, the National Archaeological Museum is a must-visit. It\'s one of the world\'s most extensive archaeological museums, housing a vast collection of ancient Greek artifacts, including sculptures, pottery, jewelry, and more.',
		],
	];
}
