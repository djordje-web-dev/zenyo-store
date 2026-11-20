# ZENYO Store

Internet prodavnica na WordPress-u i WooCommerce-u sa temom po meri.
Plan rada: `D:\Documents\biznis\ZENYO-Web-Store\PLAN.md`. Dizajn: isti folder, `project\` i `_blob\`.

## Lokalni sajt

Potreban je pokrenut Docker Desktop.

| Radnja | Komanda (iz ovog foldera) |
|---|---|
| Upali | `docker compose up -d` |
| Ugasi (podaci ostaju) | `docker compose down` |
| Obrisi sve i kreni ispocetka | `docker compose down -v`, pa `up -d`, pa setup |
| Prvo podesavanje / dopuna kataloga | `docker compose run --rm --entrypoint sh cli /scripts/setup.sh` |
| WP-CLI komanda | `docker compose run --rm cli plugin list` |

- Sajt: http://localhost:8090
- Admin: http://localhost:8090/wp-admin (korisnik i lozinka su u `.env`)
- Mejlovi: http://localhost:8091 (Mailpit hvata sve sto sajt posalje; nista ne ide pravim kupcima)

## Sta je gde

| Folder | Sadrzaj |
|---|---|
| `theme/zenyo/` | nasa tema; izmene se odmah vide na sajtu |
| `scripts/setup.sh` | instalira WordPress, WooCommerce i pokrece dve skripte ispod |
| `scripts/store-setup.php` | podesavanja prodavnice (RSD, Srbija, pouzece, dostava) |
| `scripts/payments.php` | placanje karticama (RaiAccept): pali i podesava dodatak banke iz fajla `.env` |
| `scripts/catalog.php` | kategorije, atributi i proizvodi sa cenama |
| `scripts/pages.php` | tekstovi informativnih i pravnih stranica (nacrti; pravne treba da pregleda pravnik) |
| `scripts/pages-sr.php` | srpske verzije tih stranica |
| `scripts/translations.php` | srpski nazivi i opisi proizvoda, kategorija i boja |
| `scripts/restock.php` | vraca probnu zalihu na 10 (probe kupovine je trose): `docker compose run --rm cli eval-file /scripts/restock.php` |
| `theme/zenyo/languages/sr.php` | srpski tekstovi teme (dugmad, natpisi, poruke) |
| `tools/tour.mjs` | slika sve stranice u zadatoj sirini i javlja ako nesto viri van ekrana: `node tools/tour.mjs snimci --width 390` |
| `seed/images/` | privremene slike proizvoda iz dizajna |
| `tools/e2e-order.mjs` | proba cele kupovine u pregledacu, od korpe do mejlova: `node tools/e2e-order.mjs`. Pravi probnu porudzbinu. Engleski: `ZENYO_LANG=en`, telefon: `ZENYO_WIDTH=390` ispred komande. |
| `local/mu-plugins/` | samo za lokalni rad (slanje mejlova u Mailpit); ne ide na pravi sajt |
| `tools/shot.mjs` | snimak ekrana stranice: `node tools/shot.mjs http://localhost:8090/cart/ cart.png --full` |
| `.env` | lokalne lozinke; ne ide u Git |

Baza i WordPress fajlovi zive u Docker volumenima, ne u ovom folderu.

## Dva jezika

Srpski je osnovni (`/cart/`), engleski ima `/en/` na pocetku adrese (`/en/cart/`). Proizvod postoji jednom, pa su zaliha i cena iste na oba jezika.

| Sta se prevodi | Gde |
|---|---|
| Proizvod | admin, na proizvodu: glavna polja su engleska, kutija „ZENYO - srpski prevod" je srpska |
| Kategorija, boja | admin, na kategoriji ili vrednosti osobine: polja „Srpski" |
| Stranica (FAQ, Terms...) | srpska verzija je posebna stranica; u kutiji „ZENYO jezik" se bira cija je |
| Rok isporuke, tekst na pocetnoj | Appearance -> Customize -> „ZENYO - kontakt i firma", polja sa „(srpski)" |
| Dugmad i natpisi teme | `theme/zenyo/languages/sr.php` |
| Nazivi dostave, placanja i osobina | isti fajl, odeljak `dynamic` |
| WooCommerce poruke i mejlovi | njegov srpski paket (cirilica), tema ga prebacuje u latinicu |

Prazno srpsko polje znaci da se na srpskom sajtu vidi engleski tekst. Kod je u `theme/zenyo/inc/lang.php` i `lang-admin.php`.

## Telefon i tablet

Mobilni dizajn ne postoji; raspored je izveden iz desktop dizajna. Granice: do 700 px telefon, do 1000 px tablet.

Video na pocetnoj ima i verziju za telefon: Appearance -> Customize -> „ZENYO - kontakt i firma", polja „Pocetna: video za telefon (uspravan)" i „Pocetna: slika za telefon...". Oba su neobavezna. Uspravan video dobijaju uspravni ekrani do 1000 px sirine (telefon i uspravno okrenut tablet); svi ostali dobijaju obican video. Svaki uredjaj skida samo jedan video. Prazno polje = koristi se obican video, odnosno obicna slika.

## Placanje karticama

Kartice idu preko Raiffeisen banke, njihovim dodatkom „Raiffeisen Payment Gateway" (RaiAccept). Kupac na naplati izabere karticu, ode na stranicu banke, tamo unese podatke i vrati se kod nas. Podatke o kartici sajt nikad ne vidi.

- **Pristupni podaci** su u `.env` (`RAIACCEPT_API_USERNAME`, `RAIACCEPT_API_PASSWORD`). Posle izmene: `docker compose run --rm cli eval-file /scripts/payments.php`. Prazno = kartice se ne prikazuju, ostaje pouzece.
- **Probni i pravi rad:** sada je probni (sandbox), nista se stvarno ne naplacuje. Za pravi rad banka salje druge podatke posle ugovora: `RAIACCEPT_PROD_USERNAME`, `RAIACCEPT_PROD_PASSWORD` i `RAIACCEPT_MODE=production`.
- **Placena porudzbina** sama prelazi u „Processing" (placeno, ceka slanje). „Completed" stavljas ti kad paket krene.
- **Odustajanje na stranici banke:** porudzbina se otkaze, artikli se vrate u korpu, kupac se vraca na naplatu sa porukom.
- **Povracaj novca:** u adminu, na porudzbini, dugme Refund.
- **Lokalno ogranicenje:** banka ne moze da javi rezultat sajtu na `localhost`, pa se stanje porudzbine osvezi tek kad se kupac vrati sa stranice banke. Na pravom i probnom sajtu na internetu banka javlja i sama.
- Nazivi „Card payment" i opis prevode se u `theme/zenyo/languages/sr.php`, odeljak `dynamic`.

## Privremeno, treba zameniti

- zaliha je 10 komada po varijaciji;
- dostava je 390 RSD (standardna) i 590 RSD (ekspres), iz dizajna;
- Cap i Keychain su nacrti jer nemaju cenu;
- srpske nazive i tekstove sam preveo ja; treba ih procitati i ispraviti po ukusu;
- adrese su iste na oba jezika (`/shop/`, `/product/...`), nisu prevedene;
- pravni tekstovi (Terms, Privacy, Cookies, Shipping & Returns) su nacrti i cekaju pregled pravnika;
- mejlovi imaju WooCommerce raspored sa ZENYO bojama; poseban dizajn mejla ne postoji;
- u Orders ima probnih porudzbina od testiranja.
