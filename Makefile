NETWORK_NAME=tippit-network

.PHONY: up build down restart logs check-network

check-network:
	@if [ -z "$$(docker network ls --filter name=$(NETWORK_NAME) -q)" ]; then \
		echo "Creating Docker network $(NETWORK_NAME)"; \
		docker network create $(NETWORK_NAME); \
	else \
		echo "Docker network $(NETWORK_NAME) already exists"; \
	fi

up: check-network
	@echo "Starting backend containers..."
	docker compose up -d --build

down:
	@echo "Stopping backend containers..."
	docker compose down

restart: down up
	@echo "Backend containers restarted."

logs:
	@echo "Displaying logs..."
	docker compose logs -f
