# OZ-TZ

## Initialization

### Clone the OZ-TZ repo

  ```bash
    git clone git@github.com:ptretyakov/oztz.git
  ```

### Update folder permissions

  ```bash
    sudo chmod -R 0755 ./oztz
  ```

### Install PHP packages with Composer package manager [(if not installed)](https://goo.gl/64aGDo)

  ```bash
    composer install
  ```

### Rename .env.example env variables config to .env

  ```bash
    mv .env.example .env
  ```

### Type configs on the file

  ```bash
    nano .env
  ```

### Make DB migration

  ```bash
    php init.php
  ```

---

## Run mother fucker, run

### Run receiver script from root project folder

  ```bash
    php receiver.php
  ```

### Open site

### Enter "first" or "second" to the input to get success value

### Enter any other value to the input to get failed value