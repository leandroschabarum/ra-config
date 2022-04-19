# -*- MakeFile -*-

SHELL=/bin/bash
PHPUNIT=phpunit
PHPUNIT_FLAGS=--colors=auto --do-not-cache-result --testdox --bootstrap vendor/autoload.php

TEST_FILES=$(wildcard tests/*Test.php)
TEST_NAMES=$(patsubst tests/%Test.php, %Test, $(TEST_FILES))

$(TEST_NAMES):
	@echo "# Starting $@..."
	export RA_CONFIG_KEY_FILE=secret.key;\
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
