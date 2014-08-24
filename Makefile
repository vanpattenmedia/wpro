all:
	# make deps    	 - Install dependencies.
	# make builddocs - Convert WordPress the readme.txt into GitHub README.md markdown.
	# make clean     - Remove build and test junk from filesystem.

deps:
	composer install

builddocs: deps
	vendor/bin/wp2md convert < readme.txt > README.md

clean:
	rm -Rf vendor composer.lock

.PHONY: all deps builddocs clean
