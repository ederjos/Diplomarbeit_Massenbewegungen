#!/bin/bash
set -e

#
#   Gemini 3.1 Pro, 2026-02-22
#   "Improve the setup.sh script to check if PHP 8.5 or higher, Composer, and Docker are installed. The script should prompt the user to install PHP or Composer if they are not found. It is only necessary to support installation on Ubuntu."
#

REQUIRED_PHP_VERSION="8.5.0"

check_php() {
    if command -v php &>/dev/null; then
        if php -r "exit(version_compare(PHP_VERSION, '$REQUIRED_PHP_VERSION', '>=') ? 0 : 1);"; then
            return 0
        fi
    fi

    echo "PHP 8.5 or higher is required but not found."
    if command -v apt &>/dev/null; then
        read -p "Do you want to install PHP 8.5? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sudo add-apt-repository ppa:ondrej/php -y
            sudo apt update
            sudo apt install -y php8.5-cli php8.5-curl php8.5-mbstring php8.5-xml php8.5-zip

            if command -v php &>/dev/null; then
                echo "PHP installed successfully."
                return 0
            fi

            echo "Failed to install PHP 8.5."
        fi
    fi

    echo "Please install PHP 8.5 or higher manually."
    exit 1
}

check_composer() {
    if command -v composer &>/dev/null; then
        return 0
    fi

    echo "Composer is required but not found."
    if command -v apt &>/dev/null; then
        read -p "Do you want to install Composer? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            sudo apt update
            sudo apt install -y php8.5-zip unzip

            # modified from: https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
            EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
            php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
            ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

            if [ "$EXPECTED_CHECKSUM" = "$ACTUAL_CHECKSUM" ]; then
                sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

                if command -v composer &>/dev/null; then
                    rm composer-setup.php
                    echo "Composer installed successfully."
                    return 0
                fi
            fi

            rm -f composer-setup.php
            echo "Failed to install Composer."
        fi
    fi

    echo "Please install Composer manually."
    exit 1
}

check_docker_installed() {
    if ! command -v docker &>/dev/null; then
        echo "Docker is required but not found."
        echo "Please install Docker and try again."
        exit 1
    fi

    if ! docker info &>/dev/null; then
        echo "Docker daemon is not running."
        echo "Please start Docker and try again."
        exit 1
    fi

    if ! docker compose version &>/dev/null && ! command -v docker-compose &>/dev/null; then
        echo "Docker Compose is required but not found."
        echo "Please install Docker Compose and try again."
        exit 1
    fi
}

check_php
check_composer
check_docker_installed

cp --update=none .env.example .env

composer install
php artisan key:generate

./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d

sleep 10

./vendor/bin/sail artisan migrate:fresh --force --seed

./vendor/bin/sail npm install
./vendor/bin/sail npm run build

echo "Setup complete! You can access the site at http://localhost"
echo "To stop the environment, use the stop.sh script."
