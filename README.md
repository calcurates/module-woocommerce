# Calcurates WooCommerce module

### Requirements
- PHP >= 7.1
- MySQL >= 5.7
- Web-Server (Nginx/Apache/etc...)

### Docker setup
- Copy `.env.example` to `.env`. Set env vars as you need.
- Copy `.docker/php/wordpress-bedrock/development.php.example` to `.docker/php/wordpress-bedrock/development.php`. Set vars as you need.
- Use `XDEBUG_REMOTE_HOST=host.docker.internal` for Windows or Mac.
- Find IP in Linux for XDEBUG_REMOTE_HOST variable.
```bash
ip -4 addr show docker0 | grep -Po 'inet \K[\d.]+'
```

### Run
```bash
docker-compose up
```
Go to http://localhost:8000

### Dependencies for Conventional Commits
```bash
npm install --global commitizen
npm i
```
Add new commit
```bash
git add .
git cz
```
