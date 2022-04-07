# -*- MakeFile -*-

SHELL=/bin/bash
PHPUNIT=phpunit
PHPUNIT_FLAGS=--colors=auto --do-not-cache-result --testdox

TEST_FILES=$(wildcard tests/*Test.php)
TEST_NAMES=$(patsubst tests/%Test.php, %Test, $(TEST_FILES))

$(TEST_NAMES):
	@echo "### Starting $@..."
	$(PHPUNIT) $(PHPUNIT_FLAGS) tests/$@.php

test : $(TEST_NAMES)

list :
	@echo $(TEST_NAMES)
