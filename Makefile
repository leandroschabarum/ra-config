# -*- MakeFile -*-

SHELL=/bin/bash
PHPUNIT=./phpunit

test :
	@echo "running tests..."
	$(PHPUNIT) --colors=auto --do-not-cache-result --testdox tests/*Test.php
