# Calcurates WooCommerce module

### Requirements

- PHP >= 7.1
- MySQL >= 5.7
- Web-Server (Nginx/Apache/etc...)

### Dependencies for Conventional Commits

```
npm install --global commitizen
npm i
```

Add new commit

```
git add .
git cz
```

### Docker .env setup

- Copy `.env.example` to `.env`. Set env vars as you need.
- Use `XDEBUG_REMOTE_HOST=host.docker.internal` for Windows or Mac.
- Find IP in Linux for XDEBUG_REMOTE_HOST variable.

```bash
ip -4 addr show docker0 | grep -Po 'inet \K[\d.]+'
```

### WordPress Bedrock dev config setup

```
cd .docker/wordpress-bedrock/
cp development.php.example development.php
```

### Run

```bash
docker-compose up
```

Go to http://localhost:8000
