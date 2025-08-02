FROM trafex/alpine-nginx-php7:latest

COPY --chown=nobody dist ./dist
COPY --chown=nobody auth.php cache-clear.php cache.php config-default.php functions.php html.php index.php postlist.php rss.php sort-and-filter.php debug.php env-config.php ./

# Override config-default.php with our env-config.php that properly reads environment variables
RUN mv env-config.php config.php