<?php
/**
 * Srpski tekstovi teme.
 *
 * Levo je engleski tekst TACNO kako pise u sablonu, desno srpski. Menja se samo desna strana.
 * Znak %s ili %d je mesto gde sajt upise naziv ili broj; mora da ostane u prevodu.
 *
 *   strings - stalni tekstovi teme (dugmad, natpisi, poruke)
 *   plurals - tekstovi sa brojem; tri oblika: 1 artikal, 2 artikla, 5 artikala
 *   dynamic - tekstovi upisani u podesavanjima prodavnice (dostava, placanje, nazivi osobina).
 *             Ako u adminu promenis engleski tekst, ovde treba promeniti levu stranu da bude ista.
 */

return array(
	'strings' => array(
		// Zaglavlje i futer.
		'Skip to content'      => 'Pređi na sadržaj',
		'ZENYO — home'         => 'ZENYO — početna',
		'Phone — show number'  => 'Telefon — prikaži broj',
		'Call ZENYO'           => 'Pozovite ZENYO',
		'Call us'              => 'Pozovite nas',
		'Call'                 => 'Pozovi',
		'Cart'                 => 'Korpa',
		'Language'             => 'Jezik',
		'Breadcrumb'           => 'Putanja',
		'About ZENYO'          => 'O ZENYO',
		'Help'                 => 'Pomoć',
		'Legal'                => 'Pravne informacije',
		'FAQ'                  => 'Česta pitanja',
		'Shipping & Returns'   => 'Dostava i povraćaj',
		'Shipping & returns'   => 'Dostava i povraćaj',
		'Contact'              => 'Kontakt',
		'Terms & Conditions'   => 'Uslovi kupovine',
		'Privacy Policy'       => 'Politika privatnosti',
		'Cookie Policy'        => 'Politika kolačića',
		'Company Information'  => 'Podaci o firmi',
		'Serbia'               => 'Srbija',
		'Page not found'       => 'Stranica nije pronađena',
		'Search: %s'           => 'Pretraga: %s',

		// Prodavnica i kategorije.
		'Shop'                 => 'Prodavnica',
		'Shop %s'              => 'Prodavnica: %s',
		'All products'         => 'Svi proizvodi',
		'View all'             => 'Pogledaj sve',
		'Categories'           => 'Kategorije',
		'Collection'           => 'Kolekcija',
		'No products found.'   => 'Nema proizvoda.',
		'From %s'              => 'Od %s',
		'Sold out'             => 'Rasprodato',
		'Select options'       => 'Izaberi opcije',
		'Select product'       => 'Izaberite proizvod',
		'Select %s'            => 'Izaberi: %s',
		'Buy'                  => 'Kupovina',
		'Price'                => 'Cena',

		// Stranica proizvoda.
		'Add to cart'          => 'Dodaj u korpu',
		'Adding…'              => 'Dodajem…',
		'Added to cart — %s.'  => 'Dodato u korpu — %s.',
		'Could not add to cart. Please try again.' => 'Dodavanje u korpu nije uspelo. Pokušajte ponovo.',
		'Please select a %s.'  => 'Izaberite: %s.',
		'and'                  => 'i',
		'qty %d'               => 'kom. %d',
		'Size guide'           => 'Tabela veličina',
		'%s size guide'        => 'Tabela veličina: %s',
		'Open size guide'      => 'Otvori tabelu veličina',
		'Close size guide'     => 'Zatvori tabelu veličina',
		'Product details'      => 'Detalji proizvoda',
		'Care instructions'    => 'Održavanje',
		'Returns & exchanges'  => 'Povraćaj i zamena',
		'Product information sections' => 'Odeljci sa podacima o proizvodu',
		'Product images'       => 'Slike proizvoda',
		'Choose image'         => 'Izaberite sliku',
		'Image %d'             => 'Slika %d',
		'Enlarge image'        => 'Uvećaj sliku',
		'Enlarged image'       => 'Uvećana slika',
		'Close enlarged image' => 'Zatvori uvećanu sliku',

		// Korpa.
		'Your cart'            => 'Vaša korpa',
		'Cart items'           => 'Artikli u korpi',
		'Product'              => 'Proizvod',
		'Quantity'             => 'Količina',
		'Qty'                  => 'Kol.',
		'Total'                => 'Ukupno',
		'Shipping'             => 'Dostava',
		'Free'                 => 'Besplatno',
		'Remove'               => 'Ukloni',
		'Remove %s'            => 'Ukloni: %s',
		'Decrease quantity'    => 'Smanji količinu',
		'Increase quantity'    => 'Povećaj količinu',
		'Update cart'          => 'Ažuriraj korpu',
		'Proceed to checkout'  => 'Nastavi na plaćanje',
		'Continue shopping'    => 'Nastavi kupovinu',
		'Your cart is empty.'  => 'Vaša korpa je prazna.',
		'Return to shop'       => 'Nazad u prodavnicu',
		'Things you may also be interested in' => 'Možda će vas zanimati i ovo',
		'Estimated delivery'   => 'Očekivana isporuka',

		// Naplata.
		'Checkout'             => 'Plaćanje',
		'Checkout form'        => 'Obrazac za poručivanje',
		'Contact us'           => 'Kontaktirajte nas',
		'Delivery information' => 'Podaci za dostavu',
		'Delivery address'     => 'Adresa za dostavu',
		'Delivery method'      => 'Način dostave',
		'Payment method'       => 'Način plaćanja',
		'Shipping address'     => 'Adresa za dostavu',
		'First name'           => 'Ime',
		'Last name'            => 'Prezime',
		'Email'                => 'Mejl',
		'Phone'                => 'Telefon',
		'Address'              => 'Adresa',
		'Street and number'    => 'Ulica i broj',
		'City'                 => 'Mesto',
		'Postal code'          => 'Poštanski broj',
		'Order note'           => 'Napomena uz porudžbinu',
		'e.g. building entrance, intercom, delivery instructions...' => 'npr. ulaz, interfon, uputstvo za dostavu...',
		'Required field'       => 'Obavezno polje',
		'Invalid phone number' => 'Neispravan broj telefona',
		'Invalid postal code'  => 'Neispravan poštanski broj',
		'Order summary'        => 'Pregled porudžbine',
		'Your order'           => 'Vaša porudžbina',
		'Place order'          => 'Poruči',
		'We use your personal data to process your order and for the other purposes described in our <a>Privacy Policy</a>.' => 'Vaše podatke koristimo za obradu porudžbine i u druge svrhe opisane u dokumentu <a>Politika privatnosti</a>.',
		'I have read and accept the <a>Terms & Conditions</a>' => 'Pročitao/la sam i prihvatam <a>Uslove kupovine</a>',
		'No delivery options are available for this address.' => 'Za ovu adresu nema dostupnih načina dostave.',
		'No payment methods are available. Please contact us.' => 'Nema dostupnih načina plaćanja. Kontaktirajte nas.',

		// Potvrda porudzbine.
		'Thank you'            => 'Hvala',
		'Your order has been received.'  => 'Vaša porudžbina je primljena.',
		'Your order has been confirmed.' => 'Vaša porudžbina je potvrđena.',
		'We have sent a confirmation email to %s' => 'Potvrdu smo poslali na %s',
		'Order number'         => 'Broj porudžbine',
		'Order date'           => 'Datum porudžbine',
		'Payment information'  => 'Podaci o plaćanju',
		'Payment status'       => 'Status plaćanja',
		'Payment on delivery'  => 'Plaćanje pouzećem',
		'Payment successful'   => 'Plaćanje je uspelo',
		'Payment unsuccessful' => 'Plaćanje nije uspelo',
		'Awaiting payment'     => 'Čeka se uplata',
		'Awaiting payment confirmation' => 'Čeka se potvrda uplate',
		'Payment instructions will follow by email.' => 'Uputstvo za plaćanje stiže mejlom.',
		'What happens next?'   => 'Šta sledi?',
		'You will receive an order confirmation email with all details.' => 'Dobićete mejl sa potvrdom porudžbine i svim detaljima.',
		'We will prepare your order and notify you when it is shipped.'  => 'Pripremićemo porudžbinu i javiti vam kada bude poslata.',
		'You can contact us if you have any questions.' => 'Ako imate pitanja, slobodno nam se javite.',
		'Your payment could not be completed.' => 'Plaćanje nije moglo da se završi.',
		'No order has been completed and your cart is unchanged.' => 'Porudžbina nije napravljena, a vaša korpa je ostala ista.',
		'Your cart has been kept, so nothing is lost.' => 'Vaša korpa je sačuvana, ništa nije izgubljeno.',
		'You can return to your cart and try again.'   => 'Možete se vratiti u korpu i pokušati ponovo.',
		'Try payment again'    => 'Pokušaj plaćanje ponovo',
		'Payment was cancelled and nothing has been charged. Your cart has been kept, so you can try again or choose another payment method.' => 'Plaćanje je otkazano i ništa nije naplaćeno. Vaša korpa je sačuvana, pa možete pokušati ponovo ili izabrati drugi način plaćanja.',
		'There is no recent order to show.' => 'Nema nedavne porudžbine za prikaz.',

		// FAQ i kontakt.
		'FAQ introduction'            => 'Uvod u česta pitanja',
		'Frequently asked questions'  => 'Česta pitanja',
		'Quick links'                 => 'Brzi linkovi',
		'Still have a question?'      => 'Imate još pitanja?',
		"We're here to help."         => 'Tu smo da pomognemo.',
	),

	'plurals' => array(
		'Subtotal (%d item)'      => array( 'Međuzbir (%d artikal)', 'Međuzbir (%d artikla)', 'Međuzbir (%d artikala)' ),
		'Order summary (%d item)' => array( 'Pregled porudžbine (%d artikal)', 'Pregled porudžbine (%d artikla)', 'Pregled porudžbine (%d artikala)' ),
		'Your order (%d item)'    => array( 'Vaša porudžbina (%d artikal)', 'Vaša porudžbina (%d artikla)', 'Vaša porudžbina (%d artikala)' ),
	),

	'dynamic' => array(
		// Nazivi osobina proizvoda (Products -> Attributes).
		'Colour'      => 'Boja',
		'Size'        => 'Veličina',
		'Length (cm)' => 'Dužina (cm)',
		'Print size'  => 'Veličina otiska',
		'Material'    => 'Materijal',

		// Dostava (WooCommerce -> Settings -> Shipping).
		'Standard delivery — Delivery across Serbia' => 'Standardna dostava — Dostava na teritoriji Srbije',
		'Express delivery — 1–2 business days'       => 'Ekspres dostava — 1–2 radna dana',
		'Standard delivery'                          => 'Standardna dostava',
		'Express delivery'                           => 'Ekspres dostava',

		// Placanje (WooCommerce -> Settings -> Payments).
		'Cash on delivery'                            => 'Plaćanje pouzećem',
		'Pay with cash when your order is delivered.' => 'Plaćate gotovinom kuriru kada vam porudžbina stigne.',
		'Card payment'                                => 'Plaćanje karticom',
		'Visa, Mastercard, DinaCard. You enter your card details on the secure page of Raiffeisen Bank.' => 'Visa, Mastercard, DinaCard. Podatke o kartici unosite na sigurnoj stranici Raiffeisen banke.',
	),
);
