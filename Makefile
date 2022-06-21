.PHONY: all build

all: build run

build:
	docker build -f docker/Dockerfile -t reftkd:latest .

run:
	docker run -dit --name reftkd-local -v `pwd`/frontend:/var/www/html -v `pwd`/backend:/usr/local/app -p 8043:80 reftkd:latest

stop:
	docker stop reftkd-local
	docker container rm reftkd-local

clean: stop
	docker image rm ocular
