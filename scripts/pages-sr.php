<?php
/**
 * Srpske verzije stranica. Svaka je posebna stranica vezana za englesku (polje "ZENYO jezik" u adminu);
 * nema svoju adresu, nego se njen naslov i tekst prikazuju na adresi engleske stranice kad je sajt na srpskom.
 * Pokrece se kroz setup.sh posle pages.php. Pravi samo ono sto ne postoji, pa ne gazi izmene iz admina.
 *
 * VAZNO: ovo su NACRTI. Pravne tekstove treba da pregleda pravnik pre pustanja sajta.
 */

require_once '/scripts/lib-blocks.php';

$zs_updated = zp_p( 'Poslednja izmena: 14. novembar 2026.', 'zy-note' );

/* ---------- O ZENYO ---------- */

$about  = zp_p( 'ZENYO je prodavnica opreme za džudo i predmeta inspirisanih džudoom i japanskom kulturom.' );
$about .= zp_h2( 'Ime' );
$about .= zp_p( 'Ime dolazi od <strong>Seiryoku Zenyo</strong> (精力善用), jednog od dva osnovna načela džudoa koja je postavio Đigoro Kano. Obično se prevodi kao „“: postići najviše uz najmanje uzaludnog napora, na strunjači i van nje.' );
$about .= zp_p( 'Odatle i naš moto: <strong>Minimum Effort. Maximum Efficiency.</strong>' );
$about .= zp_h2( 'Šta nudimo' );
$about .= zp_ul(
	array(
		'<strong>Kimona za džudo</strong> — IJF takmičarski, trening i dečiji modeli.',
		'<strong>Pojasevi</strong> — pojasevi u boji i IJF crni pojas.',
		'<strong>ZENYO</strong> — naša linija: umetnički otisci, nakit i dodaci.',
	)
);
$about .= zp_p( '<a href="/shop/">Idi u prodavnicu</a>' );

/* ---------- Česta pitanja ---------- */

$faq  = zp_qa( 'Kako da izaberem pravu veličinu kimona?', 'Na stranici svakog kimona, pored izbora veličine, nalazi se <strong>Tabela veličina</strong>. Veličine kimona su date po visini u centimetrima (na primer 150, 160, 170). Ako niste sigurni koju veličinu da izaberete, javite nam se pre poručivanja. <a href="/shop/">Idi u prodavnicu</a>', true );
$faq .= zp_qa( 'Koliko traje isporuka?', 'Isporuka obično traje 1–3 radna dana od trenutka kada porudžbinu pošaljemo. Načini dostave dostupni za vašu adresu prikazani su pri poručivanju.' );
$faq .= zp_qa( 'Koliko košta dostava?', 'Cena dostave za način koji izaberete prikazana je pri poručivanju, pre nego što potvrdite porudžbinu.' );
$faq .= zp_qa( 'Mogu li da zamenim ili vratim artikal?', 'Da. Od kupovine možete odustati u roku od 14 dana od prijema robe, bez navođenja razloga. Ako želite zamenu za drugu veličinu ili boju, javite nam se i dogovorićemo je. <a href="/shipping-returns/">Dostava i povraćaj</a>' );
$faq .= zp_qa( 'Koji su načini plaćanja?', 'Dostupni načini plaćanja prikazani su pri poručivanju. Trenutno je moguće plaćanje pouzećem: plaćate kuriru kada preuzmete porudžbinu.' );
$faq .= zp_qa( 'Da li su svi proizvodi na stanju?', 'Na stranici proizvoda možete izabrati samo veličine i opcije koje su dostupne. Opcije koje nisu dostupne su precrtane i ne mogu se izabrati. Ako veličine koja vam treba nema, javite nam se.' );
$faq .= zp_qa( 'Da li šaljete van Srbije?', 'Trenutno isporučujemo samo na teritoriji Srbije.' );
$faq .= zp_qa( 'Kako da pratim porudžbinu?', 'Broj porudžbine prikazan je na stranici sa potvrdom i u mejlu sa potvrdom. Poslaćemo vam još jedan mejl kada porudžbina bude poslata. Ako imate pitanje u vezi sa porudžbinom, javite nam se i navedite njen broj.' );
$faq .= zp_qa( 'Stigao mi je oštećen ili pogrešan artikal. Šta da radim?', 'Javite nam se što pre i navedite broj porudžbine, kratak opis i, ako je moguće, fotografiju. Odgovorićemo u roku od 8 dana i reći kako će problem biti rešen. <a href="/shipping-returns/">Dostava i povraćaj</a>' );

/* ---------- Kontakt ---------- */

$contact  = zp_p( 'Imate pitanje o veličinama, porudžbini ili proizvodu? Pišite nam ili nas pozovite, rado ćemo pomoći.' );
$contact .= zp_html(
	'<table><tbody>'
	. '<tr><th scope="row">Mejl</th><td>[zenyo key="email_link"]</td></tr>'
	. '<tr><th scope="row">Telefon</th><td>[zenyo key="phone_link"]</td></tr>'
	. '<tr><th scope="row">Instagram</th><td>[zenyo key="instagram_link"]</td></tr>'
	. '<tr><th scope="row">Adresa</th><td>[zenyo key="address"]</td></tr>'
	. '</tbody></table>'
);
$contact .= zp_p( 'Kada pišete u vezi sa postojećom porudžbinom, navedite njen broj da bismo je brzo pronašli.' );
$contact .= zp_p( 'Za povraćaj i reklamacije pogledajte stranicu <a href="/shipping-returns/">Dostava i povraćaj</a>. Podaci o firmi su na stranici <a href="/company-information/">Podaci o firmi</a>.' );

/* ---------- Dostava i povraćaj ---------- */

$ship  = zp_h2( 'Dostava' );
$ship .= zp_ul(
	array(
		'Isporučujemo na adrese u Srbiji.',
		'Porudžbine dostavlja kurirska služba na adresu koju unesete pri poručivanju.',
		'Načini dostave i njihova cena prikazani su pri poručivanju, pre nego što potvrdite porudžbinu.',
		'Isporuka obično traje 1–3 radna dana od trenutka kada porudžbinu pošaljemo.',
		'Poslaćemo vam mejl kada porudžbina bude poslata.',
	)
);
$ship .= zp_p( 'Pregledajte paket pri preuzimanju. Ako je vidljivo oštećen, recite to kuriru i javite nam se istog dana.' );

$ship .= zp_h2( 'Pravo na odustanak od kupovine' );
$ship .= zp_p( 'Kada kupujete preko interneta, od ugovora možete odustati <strong>u roku od 14 dana</strong> od dana kada ste primili robu, bez navođenja razloga.' );
$ship .= zp_ul(
	array(
		'Pošaljite nam jasnu izjavu da odustajete, mejlom na [zenyo key="email_link"], pre isteka roka od 14 dana. Možete koristiti obrazac ispod.',
		'Robu pošaljite nazad u roku od 14 dana od dana kada ste nas obavestili o odustanku.',
		'Trošak vraćanja robe snosi kupac.',
		'Robu treba vratiti nekorišćenu i neoštećenu, sa etiketama i, ako je moguće, u originalnom pakovanju. Robom smete rukovati samo onoliko koliko je potrebno da je proverite, kao što biste to učinili u prodavnici. Odgovarate za umanjenu vrednost robe koja je posledica rukovanja preko te mere.',
	)
);
$ship .= zp_p( 'Vratićemo vam iznos koji ste platili, uključujući trošak prvobitne standardne dostave, bez odlaganja, a najkasnije u roku od 14 dana od dana kada smo primili izjavu o odustanku. Povraćaj novca možemo odložiti dok ne primimo robu ili dok ne dostavite dokaz da ste je poslali. Pošto se porudžbine plaćaju pouzećem, novac vraćamo na bankovni račun koji nam navedete.' );

$ship .= zp_h2( 'Obrazac za odustanak' );
$ship .= zp_html(
	'<div class="zy-form">'
	. '<p>Prima: [zenyo key="company_name"], [zenyo key="address"], [zenyo key="email"]</p>'
	. '<p>Ovim obaveštavam da odustajem od ugovora o prodaji sledeće robe:</p>'
	. '<p>Broj porudžbine: ____________<br>Roba: ____________<br>Datum prijema robe: ____________</p>'
	. '<p>Ime i prezime kupca: ____________<br>Adresa kupca: ____________<br>Broj računa za povraćaj novca: ____________</p>'
	. '<p>Datum: ____________</p>'
	. '</div>'
);

$ship .= zp_h2( 'Zamena' );
$ship .= zp_p( 'Ako vam treba druga veličina ili boja, pišite nam na [zenyo key="email_link"] i navedite broj porudžbine. Proverićemo da li je željeni artikal dostupan i dogovoriti sa vama način zamene.' );

$ship .= zp_h2( 'Reklamacije (oštećena, neispravna ili pogrešna roba)' );
$ship .= zp_p( 'Odgovaramo za nesaobraznost robe ugovoru koja se pojavi u roku od <strong>dve godine</strong> od dana kada ste robu primili.' );
$ship .= zp_ul(
	array(
		'Reklamaciju pošaljite mejlom na [zenyo key="email_link"] ili je izjavite telefonom na [zenyo key="phone_link"]. Navedite broj porudžbine, opis problema i, ako je moguće, fotografije.',
		'Odgovorićemo u roku od <strong>8 dana</strong> od prijema reklamacije i reći da li je prihvatamo i kako predlažemo da se reši.',
		'Prihvaćena reklamacija rešava se u roku od <strong>15 dana</strong> od dana podnošenja.',
		'Zavisno od slučaja, imate pravo na popravku ili zamenu, odnosno na umanjenje cene ili povraćaj novca. Troškove rešavanja osnovane reklamacije snosimo mi.',
	)
);
$ship .= zp_p( 'Ako niste zadovoljni načinom na koji je reklamacija rešena, možete se obratiti telu za vansudsko rešavanje potrošačkih sporova ili nadležnom sudu.' );
$ship .= $zs_updated;

/* ---------- Uslovi kupovine ---------- */

$terms  = zp_p( 'Ovi uslovi važe za svaku porudžbinu napravljenu na ovom sajtu. Poručivanjem potvrđujete da ste ih pročitali i prihvatili.' );
$terms .= zp_h2( '1. Prodavac' );
$terms .= zp_p( 'Prodavac je [zenyo key="company_name"], [zenyo key="address"], PIB [zenyo key="company_pib"], matični broj [zenyo key="company_mb"]. Kontakt: [zenyo key="email_link"], [zenyo key="phone_link"]. Svi podaci su na stranici <a href="/company-information/">Podaci o firmi</a>.' );
$terms .= zp_h2( '2. Poručivanje' );
$terms .= zp_ul(
	array(
		'Poručujete tako što proizvode dodate u korpu, unesete svoje podatke i potvrdite porudžbinu.',
		'Možete kupovati bez otvaranja naloga.',
		'Kada poručite, na mejl adresu koju ste uneli šaljemo potvrdu. Ugovor o prodaji je zaključen kada primite tu potvrdu.',
		'Ako se ispostavi da poručeni proizvod nije dostupan, obavestićemo vas bez odlaganja i otkazati taj deo porudžbine.',
		'Možemo vas kontaktirati mejlom ili telefonom da potvrdimo porudžbinu ili razjasnimo njene detalje.',
	)
);
$terms .= zp_h2( '3. Cene' );
$terms .= zp_ul(
	array(
		'Sve cene su u dinarima (RSD) i predstavljaju konačne cene robe.',
		'Dostava se naplaćuje posebno. Njena cena je prikazana pri poručivanju, pre nego što potvrdite porudžbinu.',
		'Važi cena prikazana u trenutku kada poručite.',
		'Ako je cena očigledno pogrešna zbog tehničke greške, kontaktiraćemo vas pre obrade porudžbine i možete je otkazati.',
	)
);
$terms .= zp_h2( '4. Plaćanje' );
$terms .= zp_p( 'Dostupni načini plaćanja prikazani su pri poručivanju. Kod plaćanja pouzećem plaćate kuriru kada preuzmete porudžbinu.' );
$terms .= zp_h2( '5. Dostava' );
$terms .= zp_p( 'Isporučujemo na teritoriji Srbije. Uslovi dostave opisani su na stranici <a href="/shipping-returns/">Dostava i povraćaj</a>.' );
$terms .= zp_h2( '6. Odustanak, zamena i reklamacije' );
$terms .= zp_p( 'Od kupovine možete odustati u roku od 14 dana od prijema robe. Vaša prava kada je roba oštećena, neispravna ili pogrešna, kao i način podnošenja reklamacije, opisani su na stranici <a href="/shipping-returns/">Dostava i povraćaj</a>, koja je sastavni deo ovih uslova.' );
$terms .= zp_h2( '7. Podaci o proizvodima' );
$terms .= zp_p( 'Trudimo se da opisi, veličine i fotografije budu tačni. Boje mogu izgledati malo drugačije na različitim ekranima. Tabele veličina služe kao pomoć pri izboru; ako niste sigurni, javite nam se pre poručivanja.' );
$terms .= zp_h2( '8. Podaci o ličnosti' );
$terms .= zp_p( 'Vaše podatke o ličnosti koristimo samo da obradimo i isporučimo porudžbinu i da ispunimo zakonske obaveze. Detalji su u dokumentima <a href="/privacy-policy/">Politika privatnosti</a> i <a href="/cookie-policy/">Politika kolačića</a>.' );
$terms .= zp_h2( '9. Žigovi i sadržaj' );
$terms .= zp_p( 'Ime ZENYO, logo, tekstovi i fotografije na ovom sajtu ne smeju se koristiti bez naše dozvole. Ostali nazivi brendova i logotipi prikazani na sajtu pripadaju svojim vlasnicima.' );
$terms .= zp_h2( '10. Sporovi i merodavno pravo' );
$terms .= zp_p( 'Na ove uslove primenjuje se pravo Republike Srbije. Svaki nesporazum pokušaćemo da rešimo dogovorom. Možete se obratiti i telu za vansudsko rešavanje potrošačkih sporova ili nadležnom sudu.' );
$terms .= zp_h2( '11. Izmene' );
$terms .= zp_p( 'Ove uslove možemo menjati. Na porudžbinu se primenjuje verzija objavljena na dan kada je porudžbina napravljena.' );
$terms .= $zs_updated;

/* ---------- Politika privatnosti ---------- */

$privacy  = zp_p( 'Ovaj dokument objašnjava koje podatke o ličnosti prikupljamo kada koristite ovaj sajt, zašto, i koja su vaša prava.' );
$privacy .= zp_h2( 'Ko je odgovoran za vaše podatke' );
$privacy .= zp_p( 'Rukovalac vašim podacima o ličnosti je [zenyo key="company_name"], [zenyo key="address"]. Za svako pitanje o vašim podacima pišite na [zenyo key="email_link"].' );
$privacy .= zp_h2( 'Koje podatke prikupljamo' );
$privacy .= zp_ul(
	array(
		'<strong>Podaci iz porudžbine:</strong> ime i prezime, mejl adresa, broj telefona, adresa za dostavu i napomena koju dodate uz porudžbinu.',
		'<strong>Podaci o kupovini:</strong> proizvodi koje ste poručili, iznosi, način dostave i način plaćanja.',
		'<strong>Tehnički podaci:</strong> podaci potrebni da sajt radi, na primer sadržaj vaše korpe. Pogledajte dokument <a href="/cookie-policy/">Politika kolačića</a>.',
	)
);
$privacy .= zp_p( 'Ne prikupljamo i ne čuvamo podatke o platnim karticama.' );
$privacy .= zp_h2( 'Zašto ih koristimo' );
$privacy .= zp_ul(
	array(
		'Da obradimo i isporučimo porudžbinu, izdamo račun i kontaktiramo vas u vezi s njom. Osnov je ugovor o prodaji.',
		'Da rešimo odustanak, zamenu i reklamacije. Osnov su ugovor i naše zakonske obaveze.',
		'Da vodimo evidencije koje traže računovodstveni i poreski propisi. Osnov je zakonska obaveza.',
	)
);
$privacy .= zp_p( 'Vaše podatke ne koristimo za reklamiranje i ne šaljemo vam obaveštenja ako ih niste tražili.' );
$privacy .= zp_h2( 'Ko dobija vaše podatke' );
$privacy .= zp_ul(
	array(
		'Kurirska služba koja dostavlja porudžbinu dobija vaše ime, adresu i broj telefona.',
		'Firme kod kojih je sajt smešten i koje isporučuju njegove mejlove obrađuju podatke tehnički, u naše ime.',
		'Naš knjigovođa i nadležni organi, kada to zakon zahteva.',
	)
);
$privacy .= zp_p( 'Vaše podatke ne prodajemo i ne ustupamo nikom drugom. Neki od naših pružalaca usluga mogu čuvati podatke na serverima van Srbije; u tom slučaju vodimo računa da budu zaštićeni u skladu sa zakonom.' );
$privacy .= zp_h2( 'Koliko dugo ih čuvamo' );
$privacy .= zp_p( 'Podatke iz porudžbine čuvamo onoliko koliko je potrebno da se porudžbina izvrši i onoliko koliko zahtevaju računovodstveni i poreski propisi i propisi o zaštiti potrošača. Posle toga se brišu.' );
$privacy .= zp_h2( 'Vaša prava' );
$privacy .= zp_p( 'Imate pravo da od nas zatražite pristup svojim podacima, njihovu ispravku ili brisanje, ograničenje obrade, da uložite prigovor na obradu i da podatke dobijete u obliku koji se može preneti. Da biste ostvarili bilo koje od ovih prava, pišite na [zenyo key="email_link"]. Odgovorićemo u roku od 30 dana.' );
$privacy .= zp_p( 'Ako smatrate da se vaši podaci obrađuju nezakonito, možete podneti pritužbu Povereniku za informacije od javnog značaja i zaštitu podataka o ličnosti Republike Srbije.' );
$privacy .= zp_h2( 'Izmene' );
$privacy .= zp_p( 'Ako promenimo način na koji postupamo sa podacima o ličnosti, ažuriraćemo ovu stranicu.' );
$privacy .= $zs_updated;

/* ---------- Politika kolačića ---------- */

$cookies  = zp_p( 'Kolačići su male datoteke koje sajt čuva u vašem pregledaču. Ova stranica objašnjava koje kolačiće ovaj sajt koristi.' );
$cookies .= zp_h2( 'Neophodni kolačići' );
$cookies .= zp_p( 'Potrebni su da bi prodavnica radila i na sajtu se ne mogu isključiti:' );
$cookies .= zp_ul(
	array(
		'<strong>Korpa i sesija</strong> — pamte šta je u vašoj korpi i čuvaju to dok prelazite sa stranice na stranicu i kroz poručivanje. Ističu kada zatvorite pregledač ili posle oko dva dana.',
		'<strong>Sadržaj korpe u pregledaču</strong> — broj artikala prikazan pored natpisa „“ čuva se i u memoriji vašeg pregledača, da bi se stranica brže učitala.',
	)
);
$cookies .= zp_h2( 'Kolačići za analitiku i oglašavanje' );
$cookies .= zp_p( 'Ovaj sajt trenutno ne koristi kolačiće za analitiku ni za oglašavanje. Ako se to promeni, prvo ćemo tražiti vašu saglasnost i ažurirati ovu stranicu.' );
$cookies .= zp_h2( 'Sadržaj sa drugih servisa' );
$cookies .= zp_p( 'Fontovi na ovom sajtu učitavaju se sa servisa Google Fonts. Pri učitavanju se vaš pregledač povezuje sa Google-ovim serverima i Google dobija vašu IP adresu. Pri tome se ne postavlja kolačić.' );
$cookies .= zp_h2( 'Kako da upravljate kolačićima' );
$cookies .= zp_p( 'Kolačiće možete obrisati ili blokirati u podešavanjima pregledača. Ako blokirate neophodne kolačiće, korpa i poručivanje neće raditi.' );
$cookies .= $zs_updated;

/* ---------- Podaci o firmi ---------- */

$company  = zp_html(
	'<table><tbody>'
	. '<tr><th scope="row">Poslovno ime</th><td>[zenyo key="company_name"]</td></tr>'
	. '<tr><th scope="row">Pravna forma</th><td>Preduzetnik</td></tr>'
	. '<tr><th scope="row">Sedište</th><td>[zenyo key="address"]</td></tr>'
	. '<tr><th scope="row">PIB</th><td>[zenyo key="company_pib"]</td></tr>'
	. '<tr><th scope="row">Matični broj</th><td>[zenyo key="company_mb"]</td></tr>'
	. '<tr><th scope="row">Posluje od</th><td>3. decembra 2021.</td></tr>'
	. '<tr><th scope="row">Mejl</th><td>[zenyo key="email_link"]</td></tr>'
	. '<tr><th scope="row">Telefon</th><td>[zenyo key="phone_link"]</td></tr>'
	. '<tr><th scope="row">Sajt</th><td>zenyostore.com</td></tr>'
	. '</tbody></table>'
);
$company .= zp_p( 'ZENYO je naziv internet prodavnice koju vodi gore navedeni preduzetnik.' );

/* ---------- upis ---------- */

// adresa engleske stranice => [ srpski naslov, tekst, uvod ]
// Shop, Cart i Checkout imaju samo srpski naslov; njihov sadrzaj crta tema.
$zs_pages = array(
	'about'               => array( 'O ZENYO', $about, '' ),
	'faq'                 => array( 'Česta pitanja', $faq, 'Ovde su odgovori na najčešća pitanja o poručivanju, dostavi, veličinama, zameni i ostalom.' ),
	'contact'             => array( 'Kontakt', $contact, '' ),
	'shipping-returns'    => array( 'Dostava i povraćaj', $ship, '' ),
	'terms'               => array( 'Uslovi kupovine', $terms, '' ),
	'privacy-policy'      => array( 'Politika privatnosti', $privacy, '' ),
	'cookie-policy'       => array( 'Politika kolačića', $cookies, '' ),
	'company-information' => array( 'Podaci o firmi', $company, '' ),
	'shop'                => array( 'Prodavnica', '', '' ),
	'cart'                => array( 'Korpa', '', '' ),
	'checkout'            => array( 'Plaćanje', '', '' ),
);

foreach ( $zs_pages as $slug => $page ) {
	$base = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $base ) {
		WP_CLI::warning( 'Nema engleske stranice: ' . $slug );
		continue;
	}
	$existing = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'meta_key'    => '_zenyo_translation_of',
			'meta_value'  => $base->ID,
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		WP_CLI::log( '  preskacem (postoji): ' . $page[0] );
		continue;
	}
	$id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => 'sr-' . $slug,
				'post_title'   => $page[0],
				'post_content' => $page[1],
				'post_excerpt' => $page[2],
			)
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $page[0] . ': ' . $id->get_error_message() );
		continue;
	}
	update_post_meta( $id, '_zenyo_translation_of', $base->ID );
	WP_CLI::log( '  + ' . $page[0] );
}

WP_CLI::success( 'Srpske stranice napravljene.' );
