QA_DOCKER_IMAGE=jakzal/phpqa:1.25.0-php7.3-alpine
QA_DOCKER_COMMAND=docker run -it --rm -v "$(shell pwd):/project" -w /project ${QA_DOCKER_IMAGE}

ci: cs-full-check phpstan
lint: cs-full-check phpstan

phpstan:
	sh -c "${QA_DOCKER_COMMAND} phpstan analyse --configuration phpstan.neon --level 6 ."

cs:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --diff"

cs-full:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=false --diff"

cs-full-check:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=false --diff --dry-run"

in-docker-install-dev:
	rm -f composer.lock
	cp composer.json _composer.json
	composer.phar config minimum-stability dev
	composer.phar update --no-progress --no-interaction --no-suggest --optimize-autoloader --ansi
	mv _composer.json composer.json

.PHONY: phpstan cs cs-full cs-full-checks
