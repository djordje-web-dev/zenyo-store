<?php
/**
 * Informativne i pravne stranice: About, FAQ, Contact, Shipping & Returns, Terms, Privacy, Cookies, Company.
 * Pokrece se kroz setup.sh. Stranicu pravi samo ako ne postoji, pa ne gazi izmene iz admina.
 *
 * VAZNO: ovo su NACRTI. Pravne tekstove treba da pregleda pravnik pre pustanja sajta.
 * Podaci firme se ne pisu ovde nego dolaze iz admina preko kratkog koda [zenyo key="..."].
 */

require_once '/scripts/lib-image.php';
require_once '/scripts/lib-blocks.php';

$zp_updated = zp_p( 'Last updated: 14 November 2026', 'zy-note' );

/* ---------- About ---------- */

$about  = zp_p( 'ZENYO is a store for judo equipment and for objects inspired by judo and Japanese culture.' );
$about .= zp_h2( 'The name' );
$about .= zp_p( 'The name comes from <strong>Seiryoku Zenyo</strong> (精力善用), one of the two founding principles of judo set out by Jigoro Kano. It is usually translated as “maximum efficient use of energy”: achieving the most with the least wasted effort, on the mat and off it.' );
$about .= zp_p( 'That idea is where our line comes from: <strong>Minimum Effort. Maximum Efficiency.</strong>' );
$about .= zp_h2( 'What we offer' );
$about .= zp_ul(
	array(
		'<strong>Judo gi</strong> — IJF competition, training and kids models.',
		'<strong>Belts</strong> — coloured belts and the IJF black belt.',
		'<strong>ZENYO</strong> — our own line: art prints, jewellery and accessories.',
	)
);
$about .= zp_p( '<a href="/shop/">Go to the shop</a>' );

/* ---------- FAQ ---------- */

$faq  = zp_qa( 'How do I choose the right judo gi size?', 'Each judo gi page has a <strong>Size guide</strong> next to the size selection. Gi sizes are listed by height in centimetres (for example 150, 160, 170). If you are unsure which size to choose, contact us before ordering. <a href="/shop/">Go to the shop</a>', true );
$faq .= zp_qa( 'How long does delivery take?', 'Delivery usually takes 1–3 business days from the moment your order is dispatched. The delivery options available for your address are shown at checkout.' );
$faq .= zp_qa( 'What are the shipping costs?', 'The shipping cost for the delivery method you choose is shown at checkout, before you place the order.' );
$faq .= zp_qa( 'Can I exchange or return an item?', 'Yes. You can withdraw from your purchase within 14 days of receiving the goods, without giving a reason. To exchange an item for another size or colour, contact us and we will arrange it. <a href="/shipping-returns/">Shipping &amp; Returns</a>' );
$faq .= zp_qa( 'What payment methods are available?', 'The available payment methods are shown at checkout. At the moment you can pay by cash on delivery: you pay the courier when you receive your order.' );
$faq .= zp_qa( 'Are all products in stock?', 'On each product page you can only select sizes and options that are available. Options that are not available are crossed out and cannot be selected. If the size you need is not available, contact us.' );
$faq .= zp_qa( 'Do you ship outside Serbia?', 'At the moment we deliver within Serbia only.' );
$faq .= zp_qa( 'How can I track my order?', 'Your order number is shown on the order confirmation page and in the confirmation email. We will email you again when your order is shipped. If you have a question about your order, contact us and include the order number.' );
$faq .= zp_qa( 'I received a damaged or incorrect item. What should I do?', 'Please contact us as soon as possible and include your order number, a short description and, if possible, a photo. We will reply within 8 days and tell you how the problem will be resolved. <a href="/shipping-returns/">Shipping &amp; Returns</a>' );

/* ---------- Contact ---------- */

$contact  = zp_p( 'Questions about sizes, an order or a product? Write or call us, we are glad to help.' );
$contact .= zp_html(
	'<table><tbody>'
	. '<tr><th scope="row">Email</th><td>[zenyo key="email_link"]</td></tr>'
	. '<tr><th scope="row">Phone</th><td>[zenyo key="phone_link"]</td></tr>'
	. '<tr><th scope="row">Instagram</th><td>[zenyo key="instagram_link"]</td></tr>'
	. '<tr><th scope="row">Address</th><td>[zenyo key="address"]</td></tr>'
	. '</tbody></table>'
);
$contact .= zp_p( 'When you write about an existing order, please include your order number so we can find it quickly.' );
$contact .= zp_p( 'For returns and complaints, see <a href="/shipping-returns/">Shipping &amp; Returns</a>. Company details are on the <a href="/company-information/">Company Information</a> page.' );

/* ---------- Shipping & Returns ---------- */

$ship  = zp_h2( 'Delivery' );
$ship .= zp_ul(
	array(
		'We deliver to addresses in Serbia.',
		'Orders are delivered by courier to the address you enter at checkout.',
		'The delivery options and their cost are shown at checkout, before you place the order.',
		'Delivery usually takes 1–3 business days from the moment your order is dispatched.',
		'We will email you when your order is shipped.',
	)
);
$ship .= zp_p( 'Please check the package when you receive it. If it is visibly damaged, tell the courier and contact us the same day.' );

$ship .= zp_h2( 'Your right to withdraw from the purchase' );
$ship .= zp_p( 'When you buy online, you may withdraw from the contract <strong>within 14 days</strong> of the day you receive the goods, without giving a reason.' );
$ship .= zp_ul(
	array(
		'Send us a clear statement that you are withdrawing, by email to [zenyo key="email_link"], before the 14 days expire. You can use the form below.',
		'Send the goods back within 14 days of the day you told us about the withdrawal.',
		'The cost of sending the goods back is paid by the buyer.',
		'The goods should be returned unused and undamaged, with their labels and, where possible, in the original packaging. You may handle the goods only as far as needed to check them, as you would in a shop. You are responsible for any loss of value caused by handling beyond that.',
	)
);
$ship .= zp_p( 'We will refund the amount you paid, including the cost of the original standard delivery, without delay and no later than 14 days from the day we receive your withdrawal statement. We may wait with the refund until the goods are returned to us or until you send proof that you have sent them back. Because orders are paid on delivery, the refund is made to the bank account you give us.' );

$ship .= zp_h2( 'Withdrawal form' );
$ship .= zp_html(
	'<div class="zy-form">'
	. '<p>To: [zenyo key="company_name"], [zenyo key="address"], [zenyo key="email"]</p>'
	. '<p>I hereby give notice that I withdraw from the contract for the sale of the following goods:</p>'
	. '<p>Order number: ____________<br>Goods: ____________<br>Date the goods were received: ____________</p>'
	. '<p>Name of the buyer: ____________<br>Address of the buyer: ____________<br>Bank account for the refund: ____________</p>'
	. '<p>Date: ____________</p>'
	. '</div>'
);

$ship .= zp_h2( 'Exchanges' );
$ship .= zp_p( 'If you need a different size or colour, contact us at [zenyo key="email_link"] with your order number. We will check that the item you want is available and agree with you how to make the exchange.' );

$ship .= zp_h2( 'Complaints (damaged, faulty or incorrect goods)' );
$ship .= zp_p( 'We are responsible for goods that do not conform to the contract if the lack of conformity appears within <strong>two years</strong> of the day you received them.' );
$ship .= zp_ul(
	array(
		'Send your complaint by email to [zenyo key="email_link"] or by phone at [zenyo key="phone_link"]. Include your order number, a description of the problem and, if possible, photos.',
		'We will reply within <strong>8 days</strong> of receiving the complaint and tell you whether we accept it and how we propose to resolve it.',
		'An accepted complaint is resolved within <strong>15 days</strong> of the day it was submitted.',
		'Depending on the case, you are entitled to a repair or a replacement, or to a price reduction or a refund. The costs of resolving a justified complaint are ours.',
	)
);
$ship .= zp_p( 'If you are not satisfied with how a complaint was handled, you may turn to a body for out-of-court resolution of consumer disputes, or to the competent court.' );
$ship .= $zp_updated;

/* ---------- Terms & Conditions ---------- */

$terms  = zp_p( 'These terms apply to every order placed on this website. By placing an order you confirm that you have read and accepted them.' );
$terms .= zp_h2( '1. The seller' );
$terms .= zp_p( 'The seller is [zenyo key="company_name"], [zenyo key="address"], tax number (PIB) [zenyo key="company_pib"], registration number (MB) [zenyo key="company_mb"]. Contact: [zenyo key="email_link"], [zenyo key="phone_link"]. Full details are on the <a href="/company-information/">Company Information</a> page.' );
$terms .= zp_h2( '2. Ordering' );
$terms .= zp_ul(
	array(
		'You order by adding products to the cart, entering your details at checkout and confirming the order.',
		'You can buy without creating an account.',
		'After you place an order we send a confirmation to the email address you entered. The sales contract is concluded when you receive that confirmation.',
		'If a product you ordered turns out to be unavailable, we will inform you without delay and cancel that part of the order.',
		'We may contact you by email or phone to confirm the order or clarify its details.',
	)
);
$terms .= zp_h2( '3. Prices' );
$terms .= zp_ul(
	array(
		'All prices are in Serbian dinars (RSD) and are the final prices of the goods.',
		'Delivery is charged separately. Its cost is shown at checkout before you place the order.',
		'The price that applies is the one shown at the moment you place the order.',
		'If a price is obviously wrong because of a technical error, we will contact you before processing the order and you may cancel it.',
	)
);
$terms .= zp_h2( '4. Payment' );
$terms .= zp_p( 'The payment methods that are available are shown at checkout. With cash on delivery you pay the courier when you receive your order.' );
$terms .= zp_h2( '5. Delivery' );
$terms .= zp_p( 'We deliver within Serbia. The conditions of delivery are described on the <a href="/shipping-returns/">Shipping &amp; Returns</a> page.' );
$terms .= zp_h2( '6. Withdrawal, exchanges and complaints' );
$terms .= zp_p( 'You may withdraw from the purchase within 14 days of receiving the goods. Your rights when goods are damaged, faulty or incorrect, and the way to submit a complaint, are described on the <a href="/shipping-returns/">Shipping &amp; Returns</a> page, which forms part of these terms.' );
$terms .= zp_h2( '7. Product information' );
$terms .= zp_p( 'We take care that descriptions, sizes and photographs are accurate. Colours may look slightly different on different screens. Size guides are there to help you choose; if you are unsure, contact us before ordering.' );
$terms .= zp_h2( '8. Personal data' );
$terms .= zp_p( 'We use your personal data only to process and deliver your order and to meet our legal obligations. Details are in the <a href="/privacy-policy/">Privacy Policy</a> and the <a href="/cookie-policy/">Cookie Policy</a>.' );
$terms .= zp_h2( '9. Trademarks and content' );
$terms .= zp_p( 'The ZENYO name, logo, texts and photographs on this website may not be used without our permission. Other brand names and logos shown on the website belong to their owners.' );
$terms .= zp_h2( '10. Disputes and applicable law' );
$terms .= zp_p( 'These terms are governed by the law of the Republic of Serbia. We will try to resolve any disagreement by agreement. You may also turn to a body for out-of-court resolution of consumer disputes, or to the competent court.' );
$terms .= zp_h2( '11. Changes' );
$terms .= zp_p( 'We may change these terms. The version published on the day you place an order applies to that order.' );
$terms .= $zp_updated;

/* ---------- Privacy Policy ---------- */

$privacy  = zp_p( 'This policy explains which personal data we collect when you use this website, why, and what your rights are.' );
$privacy .= zp_h2( 'Who is responsible for your data' );
$privacy .= zp_p( 'The controller of your personal data is [zenyo key="company_name"], [zenyo key="address"]. For any question about your data, write to [zenyo key="email_link"].' );
$privacy .= zp_h2( 'Which data we collect' );
$privacy .= zp_ul(
	array(
		'<strong>Order data:</strong> first and last name, email address, phone number, delivery address and any note you add to the order.',
		'<strong>Purchase data:</strong> the products you ordered, the amounts, and the delivery and payment method.',
		'<strong>Technical data:</strong> data needed for the website to work, such as the contents of your cart. See the <a href="/cookie-policy/">Cookie Policy</a>.',
	)
);
$privacy .= zp_p( 'We do not collect or store payment card data.' );
$privacy .= zp_h2( 'Why we use it' );
$privacy .= zp_ul(
	array(
		'To process, deliver and invoice your order, and to contact you about it. The basis is the contract of sale.',
		'To handle withdrawals, exchanges and complaints. The basis is the contract and our legal obligations.',
		'To keep the records that accounting and tax regulations require. The basis is a legal obligation.',
	)
);
$privacy .= zp_p( 'We do not use your data for advertising and we do not send newsletters unless you have asked for them.' );
$privacy .= zp_h2( 'Who receives your data' );
$privacy .= zp_ul(
	array(
		'The courier service that delivers your order receives your name, address and phone number.',
		'The companies that host this website and deliver its emails process the data technically, on our behalf.',
		'Our accountant and the competent authorities, where the law requires it.',
	)
);
$privacy .= zp_p( 'We do not sell your data or pass it on to anyone else. Some of our service providers may store data on servers outside Serbia; in that case we make sure it is protected in line with the law.' );
$privacy .= zp_h2( 'How long we keep it' );
$privacy .= zp_p( 'We keep order data for as long as it is needed to fulfil the order and for as long as accounting, tax and consumer protection regulations require. After that it is deleted.' );
$privacy .= zp_h2( 'Your rights' );
$privacy .= zp_p( 'You have the right to ask us for access to your data, to have it corrected or deleted, to have its processing restricted, to object to its processing and to receive it in a portable form. To use any of these rights, write to [zenyo key="email_link"]. We will reply within 30 days.' );
$privacy .= zp_p( 'If you believe your data is being processed unlawfully, you may file a complaint with the Commissioner for Information of Public Importance and Personal Data Protection of the Republic of Serbia.' );
$privacy .= zp_h2( 'Changes' );
$privacy .= zp_p( 'If we change the way we handle personal data, we will update this page.' );
$privacy .= $zp_updated;

/* ---------- Cookie Policy ---------- */

$cookies  = zp_p( 'Cookies are small files that a website stores in your browser. This page explains which ones this website uses.' );
$cookies .= zp_h2( 'Necessary cookies' );
$cookies .= zp_p( 'These are needed for the shop to work and cannot be switched off on the website:' );
$cookies .= zp_ul(
	array(
		'<strong>Cart and session</strong> — remember what is in your cart and keep it as you move between pages and through checkout. They expire when you close the browser or after about two days.',
		'<strong>Cart contents in your browser</strong> — the number of items shown next to “Cart” is also kept in your browser’s storage, so the page loads faster.',
	)
);
$cookies .= zp_h2( 'Analytics and advertising cookies' );
$cookies .= zp_p( 'This website does not currently use analytics or advertising cookies. If that changes, we will ask for your consent first and update this page.' );
$cookies .= zp_h2( 'Content from other services' );
$cookies .= zp_p( 'The fonts on this website are loaded from Google Fonts. When they load, your browser contacts Google’s servers and Google receives your IP address. No cookie is set for this.' );
$cookies .= zp_h2( 'How to manage cookies' );
$cookies .= zp_p( 'You can delete or block cookies in your browser’s settings. If you block the necessary cookies, the cart and checkout will not work.' );
$cookies .= $zp_updated;

/* ---------- Company Information ---------- */

$company  = zp_html(
	'<table><tbody>'
	. '<tr><th scope="row">Business name</th><td>[zenyo key="company_name"]</td></tr>'
	. '<tr><th scope="row">Legal form</th><td>Entrepreneur (preduzetnik)</td></tr>'
	. '<tr><th scope="row">Registered address</th><td>[zenyo key="address"]</td></tr>'
	. '<tr><th scope="row">Tax number (PIB)</th><td>[zenyo key="company_pib"]</td></tr>'
	. '<tr><th scope="row">Registration number (MB)</th><td>[zenyo key="company_mb"]</td></tr>'
	. '<tr><th scope="row">In business since</th><td>3 December 2021</td></tr>'
	. '<tr><th scope="row">Email</th><td>[zenyo key="email_link"]</td></tr>'
	. '<tr><th scope="row">Phone</th><td>[zenyo key="phone_link"]</td></tr>'
	. '<tr><th scope="row">Website</th><td>zenyostore.com</td></tr>'
	. '</tbody></table>'
);
$company .= zp_p( 'ZENYO is the name of the online store operated by the business above.' );

/* ---------- upis ---------- */

$zp_pages = array(
	'about'               => array( 'About ZENYO', $about, '' ),
	'faq'                 => array( 'FAQ', $faq, 'Here you\'ll find answers to the most common questions about ordering, delivery, sizes, exchanges and more.' ),
	'contact'             => array( 'Contact', $contact, '' ),
	'shipping-returns'    => array( 'Shipping & Returns', $ship, '' ),
	'terms'               => array( 'Terms & Conditions', $terms, '' ),
	'privacy-policy'      => array( 'Privacy Policy', $privacy, '' ),
	'cookie-policy'       => array( 'Cookie Policy', $cookies, '' ),
	'company-information' => array( 'Company Information', $company, '' ),
);

$zp_ids = array();
foreach ( $zp_pages as $slug => $page ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	// WordPress pri instalaciji sam napravi praznu "Privacy Policy" kao nacrt; nju zamenjujemo.
	if ( $existing && 'draft' !== $existing->post_status ) {
		$zp_ids[ $slug ] = $existing->ID;
		WP_CLI::log( '  preskacem (postoji): ' . $page[0] );
		continue;
	}
	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $page[0],
		'post_content' => $page[1],
		'post_excerpt' => $page[2],
	);
	if ( $existing ) {
		$data['ID'] = $existing->ID;
	}
	$id = wp_insert_post( wp_slash( $data ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $page[0] . ': ' . $id->get_error_message() );
		continue;
	}
	$zp_ids[ $slug ] = $id;
	WP_CLI::log( '  + ' . $page[0] );
}

// Slika na vrhu FAQ stranice.
if ( ! empty( $zp_ids['faq'] ) && ! has_post_thumbnail( $zp_ids['faq'] ) ) {
	$image_id = zenyo_image( 'faq-hero.jpg' );
	if ( $image_id ) {
		set_post_thumbnail( $zp_ids['faq'], $image_id );
	}
}

// WordPress-u i WooCommerce-u kazemo koje su stranice uslovi kupovine i politika privatnosti.
// Od tog trenutka naplata trazi da kupac potvrdi uslove pre narucivanja.
if ( ! empty( $zp_ids['terms'] ) && ! get_option( 'woocommerce_terms_page_id' ) ) {
	update_option( 'woocommerce_terms_page_id', $zp_ids['terms'] );
}
if ( ! empty( $zp_ids['privacy-policy'] ) ) {
	update_option( 'wp_page_for_privacy_policy', $zp_ids['privacy-policy'] );
}

WP_CLI::success( 'Stranice napravljene.' );
