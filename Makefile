docker-build:
	docker compose build

docker-run:
	docker compose up -d

docker-sh:
	docker compose exec challenge bash
