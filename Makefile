.PHONY: setup composer checkcs fixcs tests

setup: ## Setup containers and dependencies
	make composer c="install --ansi --no-interaction"
	make .cache/setup

composer: ## Run Composer commands with arguments
	$(eval c := $(filter-out composer,$(MAKECMDGOALS)))
	docker compose run --rm composer $(c)

tests: .cache/build_tests ## Run PHPUnit tests
	docker compose run --rm tests

checkcs: .cache/build_cs ## Check the Code Style
	docker compose run --rm checkcs

fixcs: .cache/build_cs ## Automatically fix the code style
	docker compose run --rm fixcs

compat: .cache/build_cs ## Check the PHPCompatibilty
	docker compose run --rm compat

.cache/build_deps: Dockerfile vendor composer.json composer.lock
	docker build --tag=wplang_dev-deps:latest --progress=plain --no-cache-filter=dev-deps --target=dev-deps .
	touch .cache/build_deps

.cache/build_cs: Dockerfile .phpcs.xml .phpcompat.xml .cache/build_deps
	docker build --tag=wplang_phpcs:latest --progress=plain --no-cache-filter=phpcs --target=phpcs .
	touch .cache/build_cs

.cache/build_tests: Dockerfile .cache/build_deps
	docker build --tag=test:wplang_latest --progress=plain --no-cache-filter=tests --target=tests .
	touch .cache/build_tests

.cache/setup: Dockerfile .cache/build_deps .cache/build_cs .cache/build_test
	touch .cache/setup

vendor:
	mkdir vendor

rebuild:
	docker build --progress=plain --no-cache .
	touch .cache/build_test .cache/build_cs .cache/build_deps .cache/install

# Custom help generator
help:
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = "(:|##)"}; {printf "  %-12s %s\n", $$1, $$3}'
