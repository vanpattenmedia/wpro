help:
	# make deps    	 - Install dependencies.
	# make builddocs - Convert WordPress the readme.txt into GitHub README.md markdown.
	# make clean     - Remove build and test junk from filesystem.
	#
	# Requirements for everything to work:
	# * composer - Check out https://getcomposer.org/
	# * make

all: deps builddocs

deps:
	composer install

builddocs: deps
	vendor/bin/wp2md convert < readme.txt > README.md

clean:
	rm -Rf vendor composer.lock

.PHONY: help all deps builddocs clean
