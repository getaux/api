# AuctionX API

## Requirements

- PHP >= 8.1
- PostgreSQL >= 13

## Install locally

- Install Docker https://docs.docker.com/get-docker/
- `cp .env .env.local`
- `docker-compose build --no-cache --pull`

## Run locally

- `docker-compose up -d`
- Visit https://api.auctionx.localhost/

## Fix Chrome/Brave SSL locally

If you have a TLS trust issues, you can copy the self-signed certificate from Caddy and add it to the trusted certificates:

```bash
# Mac
docker cp $(docker-compose ps -q caddy):/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt
# Linux
docker cp $(docker-compose ps -q caddy):/data/caddy/pki/authorities/local/root.crt /usr/local/share/ca-certificates/root.crt && sudo update-ca-certificates
```

## License

Licensed under the terms of the MIT License.