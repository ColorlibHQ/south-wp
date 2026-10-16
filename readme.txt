=== South ===
Contributors: colorlib
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A block theme for real-estate agencies, with a companion plugin for property listings and search.

== Description ==

South is a Full Site Editing theme for estate agents, built from Colorlib's South template. A full-height hero slider with the property search box overlapping its lower edge, property cards with their tag, price, address and facts, a rentals call to action, client testimonials, the founder's note, the team, a contact page with a form and a map that loads only when asked, a journal, and a dark footer with office hours.

Homes, agents and the search come from the companion plugin, South Listings (a separate download): a Property content type with real fields (price, sale or rent and rent period, type, beds, baths, garages, floor area, year built, address, neighbourhood, features, room sizes, gallery and agent), property types, cities and status, and a search that filters, sorts and pages with or without JavaScript and keeps the query in the address. Without the plugin the theme still works and looks complete: the search box searches the site and the listing sections are left out.

* 33 block patterns, 15 templates, 3 template parts
* 8 colour palettes and 5 typography presets as style variations
* Visitor dark mode that follows the system setting until a visitor chooses
* An enquiry form that works without JavaScript; property enquiries go to the home's agent
* Front page, About Us, Contact and Blog pages built from the patterns on activation
* Self-hosted Open Sans, Playfair Display and Montserrat; no request leaves the site for fonts or icons

= Updates and privacy =

South is distributed by Colorlib outside WordPress.org. Twice a day it asks updates.colorlib.com whether a new version exists, sending the theme and WordPress versions, PHP version, locale, whether the site is a multisite, and a one-way hash of the site address. No personal data and no site name is sent. To switch it off:

    add_filter( 'south_check_for_updates', '__return_false' );

The South Listings plugin uses the same host and the same filter.

== Installation ==

1. Appearance > Themes > Add New > Upload Theme, choose south.zip, activate.
2. Plugins > Add New > Upload Plugin, choose south-listings.zip, activate.
3. Properties > Demo content > Import demo content, for the template's nine homes, three agents and three journal posts.

== Frequently Asked Questions ==

= Where do I change the phone number and email in the header and footer? =

Appearance > Editor > Patterns > Header (or Footer). They are ordinary paragraphs.

= Why is the Featured Properties section missing? =

It shows homes marked Featured, and needs the South Listings plugin. Without it the section is left out on purpose rather than shown empty.

= Can I turn off dark mode or the hero slider's autoplay? =

add_filter( 'south_enable_dark_mode', '__return_false' ); and add_filter( 'south_hero_autoplay', '__return_false' );

== Changelog ==

= 1.0.0 =
* First release, built from the South 3.0.0 HTML template.

== Copyright ==

South WordPress Theme, Copyright 2026 Colorlib.
South is distributed under the terms of the GNU GPL v2 or later.

This theme bundles the following third-party resources:

Open Sans, Copyright 2020 The Open Sans Project Authors
License: SIL Open Font License, 1.1, https://openfontlicense.org
Source: https://fontsource.org/fonts/open-sans

Playfair Display, Copyright 2017 The Playfair Display Project Authors
License: SIL Open Font License, 1.1, https://openfontlicense.org
Source: https://fontsource.org/fonts/playfair-display

Montserrat, Copyright 2011 The Montserrat Project Authors
License: SIL Open Font License, 1.1, https://openfontlicense.org
Source: https://fontsource.org/fonts/montserrat

Lucide icons, Copyright Lucide Contributors (portions Copyright Cole Bemis, Feather)
License: ISC, https://lucide.dev/license
Source: https://lucide.dev

Photographs in assets/images, from the South HTML template. Their licence is the Unsplash License (https://unsplash.com/license), which permits commercial use and redistribution but is not GPL-compatible; replace them with your own before publishing if that matters to you.
* hero1.avif, footer.avif: Alexander Faé, https://unsplash.com/photos/gray-and-black-house-under-blue-sky-SN0mu2M_Va0
* hero2.avif: Antonio Venezian, https://unsplash.com/photos/a-tall-white-building-with-a-balcony-on-top-of-it-pMNmlpEaEgg
* hero3.avif: R C, https://unsplash.com/photos/white-architectural-louvers-on-building-facade-GzWD5DfrhoA
* cta.avif: Polina Kuzovkova, https://unsplash.com/photos/modern-room-with-ocean-view-at-sunset-zLCTdR6W8N4
* about.avif: Jason Briscoe, https://unsplash.com/photos/brown-staircase-UV81E0oXXWQ
* team2.avif: Centre for Ageing Better, https://unsplash.com/photos/a-woman-in-a-blue-jacket-looking-at-her-cell-phone-ssE2sYVt-xs
* team3.avif: Taylor Grote, https://unsplash.com/photos/smiling-man-standing-between-brown-concrete-buildings-at-daytime-UiVe5QvOhao
* client-1.avif (crop): Avi Werde, https://unsplash.com/photos/a-charming-tudor-style-home-with-a-beautiful-yard-VcWqA204WWY
* client-2.avif (crop): Suzanne Rushton, https://unsplash.com/photos/a-living-room-filled-with-furniture-and-a-fire-place-wnXfO63yiFE
* client-3.avif (crop): Brian Babb, https://unsplash.com/photos/modern-house-with-garage-at-dusk-XbwHrt87mQ0
* editor.avif, team1.avif and signature.svg come with the original 2018 South template by Colorlib.

All other files (PHP, CSS, JavaScript, block markup) are Copyright 2026 Colorlib and GPLv2 or later.
