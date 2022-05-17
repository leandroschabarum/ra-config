# -*- MakeFile -*-

SHELL=/bin/bash
PHPUNIT=phpunit
PHPUNIT_FLAGS=--colors=auto --do-not-cache-result --testdox --bootstrap vendor/autoload.php

TEST_FILES=$(wildcard tests/*Test.php)
TEST_NAMES=$(patsubst tests/%Test.php, %Test, $(TEST_FILES))

$(TEST_NAMES):
	@echo "# Starting $@..."
	export RA_CONFIG_KEY_FILE=secret.key;\
	export RA_CONFIG_DB_DRIVER=mysql;\
	export RA_CONFIG_DB_HOST=192.168.0.210;\
	export RA_CONFIG_DB_PORT=3306;\
	export RA_CONFIG_DB_DATABASE=ra_config;\
	export RA_CONFIG_DB_USERNAME=test;\
	export RA_CONFIG_DB_PASSWORD_ENCRYPTED=false;\
	$(PHPUNIT) $(PHPUNIT_FLAGS) tests/$@.php;

test : $(TEST_NAMES)

verify : RequiredExtensionsTest

test-list :
	@echo $(TEST_NAMES)

autoload-dev :
	composer dump-autoload --dev -o

autoload :
	composer dump-autoload --no-dev -o

clean : vendor secret.key
	@echo "Cleaning up..."
	rm -rfv $^
	@echo "Done"
