#!/bin/sh
# Prvo podesavanje lokalnog sajta. Moze da se pokrene vise puta bez stete.
# Pokretanje (iz korena projekta):
#   docker compose run --rm --entrypoint sh cli /scripts/setup.sh
set -e

wp() { php -d memory_limit=512M /usr/local/bin/wp --path=/var/www/html "$@"; }

echo "== 1/5 WordPress"
if ! wp core is-installed 2>/dev/null; then
  wp core install \
    --url="http://localhost:${WP_PORT}" \
    --title="ZENYO Store" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email
fi
wp option update timezone_string "Europe/Belgrade"
wp option update blogdescription ""
wp rewrite structure '/%postname%/'

echo "== 2/5 Dodaci i tema"
wp plugin delete hello akismet 2>/dev/null || true
wp plugin is-installed woocommerce || wp plugin install woocommerce
wp plugin is-active woocommerce || wp plugin activate woocommerce
wp theme activate zenyo
# Placanje karticama: dodatak Raiffeisen banke (RaiAccept). Podesava ga scripts/payments.php iz fajla .env.
wp plugin is-installed raiffeisen-payment-gateway || wp plugin install raiffeisen-payment-gateway --version=1.2.0 || echo "  (dodatak za kartice nije preuzet; pokreni setup ponovo kad bude interneta)"
wp plugin is-active raiffeisen-payment-gateway || wp plugin activate raiffeisen-payment-gateway || true
# Srpski jezicki paketi (WooCommerce poruke i mejlovi na srpskom). Sajt ih sam prebacuje u latinicu.
wp language core is-installed sr_RS || wp language core install sr_RS || echo "  (srpski paket nije preuzet; pokreni setup ponovo kad bude interneta)"
wp language plugin is-installed woocommerce sr_RS || wp language plugin install woocommerce sr_RS || true

echo "== 3/5 Podesavanja prodavnice"
wp eval-file /scripts/store-setup.php
wp eval-file /scripts/payments.php

echo "== 4/5 Katalog"
wp eval-file /scripts/catalog.php
wp eval-file /scripts/categories.php
wp eval-file /scripts/product-content.php
wp eval-file /scripts/pages.php
wp eval-file /scripts/translations.php
wp eval-file /scripts/pages-sr.php

echo "== 5/5 Zavrsno"
wp rewrite flush
wp cache flush 2>/dev/null || true
echo "Gotovo: http://localhost:${WP_PORT}  |  admin: http://localhost:${WP_PORT}/wp-admin"
