# Deploy na Prod i Staging (Docker + GitHub Actions)

## Pregled arhitekture

Aplikacija se deployuje kao Docker kontejneri. GitHub Actions builduje image i pushuje ga na `ghcr.io`, a zatim se image povlači na server i pokreće via `docker compose`.

```
GitHub push → CI/CD → Build image → ghcr.io → SSH → docker compose up
```

**Staging** — automatski deploy na svaki push na `main`  
**Production** — manualni deploy via GitHub Actions → Run workflow

---

## Fajlovi koji su napravljeni

```
docker/
├── Dockerfile          # Multi-stage build: Node assets + PHP-FPM + Nginx
├── nginx.conf          # Nginx config za Laravel SPA
├── supervisord.conf    # Pokreće nginx + php-fpm u jednom kontejneru
├── php.ini             # Produkciona PHP podešavanja (OPcache itd.)
├── php-fpm.conf        # PHP-FPM pool config
└── start-container     # Entrypoint: kešira config/routes → pokreće supervisord

docker-compose.prod.yml     # Prod stack: app, horizon, pgsql, redis, typesense
docker-compose.staging.yml  # Staging stack: + minio, mailpit, typesense-dashboard
.dockerignore
.github/workflows/ci-cd.yml
```

App kontejner sluša na `127.0.0.1:8000` — ispred stoji Nginx/Caddy kao reverse proxy sa SSL-om.

---

## Korak 1 — Kreiraj Droplet na DigitalOcean

- Distribucija: **Ubuntu 24.04 LTS**
- Veličina: po potrebi (preporučeno minimum 2 vCPU / 2 GB RAM)
- Region: po želji
- Ponovi za staging i za prod

---

## Korak 2 — Instaliraj Docker na serveru

```bash
# Prijavi se kao root
ssh root@IP_SERVERA

# Instaliraj Docker
curl -fsSL https://get.docker.com | sh
```

---

## Korak 3 — Napravi deployer usera

```bash
# Napravi usera
adduser --disabled-password --gecos "" deployer

# Dodaj ga u docker grupu
usermod -aG docker deployer

# Napravi .ssh direktorijum
mkdir -p /home/deployer/.ssh
chmod 700 /home/deployer/.ssh

# Dodaj javni ključ (vidi Korak 4)
echo "JAVNI_KLJUC" >> /home/deployer/.ssh/authorized_keys
chmod 600 /home/deployer/.ssh/authorized_keys
chown -R deployer:deployer /home/deployer/.ssh

# Napravi deploy direktorijum
mkdir -p /opt/acomody
chown deployer:deployer /opt/acomody
```

---

## Korak 4 — Napravi SSH ključeve za GitHub Actions

Na svom računaru (poseban ključ za svaki server):

```bash
# Za staging
ssh-keygen -t ed25519 -C "github-actions-staging" -f ~/.ssh/id_ed25519_staging -N ""

# Za prod
ssh-keygen -t ed25519 -C "github-actions-prod" -f ~/.ssh/id_ed25519_prod -N ""
```

Javni ključ dodaj na odgovarajući server:

```bash
ssh-copy-id -i ~/.ssh/id_ed25519_staging.pub deployer@STAGING_IP
ssh-copy-id -i ~/.ssh/id_ed25519_prod.pub deployer@PROD_IP
```

Testiraj konekciju:

```bash
ssh -i ~/.ssh/id_ed25519_staging deployer@STAGING_IP
ssh -i ~/.ssh/id_ed25519_prod deployer@PROD_IP
```

---

## Korak 5 — Kopiraj docker-compose fajlove na servere

```bash
scp -i ~/.ssh/id_ed25519_staging docker-compose.staging.yml deployer@STAGING_IP:/opt/acomody/docker-compose.staging.yml

scp -i ~/.ssh/id_ed25519_prod docker-compose.prod.yml deployer@PROD_IP:/opt/acomody/docker-compose.prod.yml
```

---

## Korak 6 — Napravi .env na serverima

### Staging

```bash
ssh deployer@STAGING_IP
nano /opt/acomody/.env
```

```env
APP_NAME=Acomody
APP_ENV=production
APP_DEBUG=false
APP_KEY=             # php artisan key:generate --show
APP_URL=https://staging.acomody.com
FRONTEND_URL=https://staging.acomody.com

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=acomody
DB_USERNAME=acomody
DB_PASSWORD=JAKA_LOZINKA

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=staging.acomody.com

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

FILESYSTEM_DISK=minio
FILESYSTEM_CLOUD=minio
MINIO_ROOT_USER=sail
MINIO_ROOT_PASSWORD=JAKA_LOZINKA
MINIO_ENDPOINT=http://minio:9000
MINIO_REGION=us-east-1
MINIO_BUCKET=files
AWS_ACCESS_KEY_ID=sail
AWS_SECRET_ACCESS_KEY=JAKA_LOZINKA
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=files
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@acomody.com
MAIL_FROM_NAME=Acomody

SCOUT_DRIVER=typesense
TYPESENSE_HOST=typesense
TYPESENSE_PORT=8108
TYPESENSE_PROTOCOL=http
TYPESENSE_API_KEY=JAKI_KLJUC

SANCTUM_STATEFUL_DOMAINS=staging.acomody.com
SESSION_DRIVER=database

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://staging.acomody.com/auth/google/callback

HORIZON_PATH=admin/horizon
```

### Production

```bash
ssh deployer@PROD_IP
nano /opt/acomody/.env
```

Isto kao staging, sa sledećim razlikama:

```env
APP_URL=https://acomody.com
FRONTEND_URL=https://acomody.com
APP_DEBUG=false
LOG_LEVEL=error

SESSION_DOMAIN=acomody.com
SANCTUM_STATEFUL_DOMAINS=acomody.com

# DigitalOcean Spaces umesto MinIO
FILESYSTEM_DISK=s3
FILESYSTEM_CLOUD=s3
AWS_ACCESS_KEY_ID=DO_SPACES_KEY
AWS_SECRET_ACCESS_KEY=DO_SPACES_SECRET
AWS_DEFAULT_REGION=fra1
AWS_BUCKET=ime-bucketa
AWS_ENDPOINT=https://fra1.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false

# Resend za mailove
MAIL_MAILER=resend
RESEND_KEY=re_XXXXX
MAIL_FROM_ADDRESS=noreply@acomody.com
MAIL_FROM_NAME=Acomody

GOOGLE_REDIRECT_URI=https://acomody.com/auth/google/callback
```

---

## Korak 7 — Autentifikuj se na ghcr.io

Server mora da može da povlači Docker image sa GitHub Container Registry.

Napravi Personal Access Token (PAT):  
GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic) → New token  
Scope: `read:packages`

Na **oba servera** (kao `deployer`):

```bash
echo "TVOJ_PAT_TOKEN" | docker login ghcr.io -u TVOJ_GITHUB_USERNAME --password-stdin
```

---

## Korak 8 — Postavi GitHub Secrets

GitHub → repozitorijum → Settings → Secrets and variables → Actions

| Secret | Vrednost |
|--------|----------|
| `STAGING_SSH_KEY` | sadržaj `~/.ssh/id_ed25519_staging` (privatni ključ) |
| `STAGING_SSH_HOST` | IP staging servera |
| `STAGING_SSH_USER` | `deployer` |
| `STAGING_DEPLOY_PATH` | `/opt/acomody` |
| `PROD_SSH_KEY` | sadržaj `~/.ssh/id_ed25519_prod` (privatni ključ) |
| `PROD_SSH_HOST` | IP prod servera |
| `PROD_SSH_USER` | `deployer` |
| `PROD_DEPLOY_PATH` | `/opt/acomody` |

Privatni ključ kopiraš ovako:

```bash
cat ~/.ssh/id_ed25519_staging
# Kopiraj ceo output uključujući -----BEGIN i -----END linije
```

---

## Korak 9 — Postavi Nginx reverse proxy na serveru

App sluša na `127.0.0.1:8000`. Nginx prima HTTPS i prosleđuje:

```bash
apt install nginx certbot python3-certbot-nginx -y

nano /etc/nginx/sites-available/acomody
```

```nginx
server {
    listen 80;
    server_name acomody.com www.acomody.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name acomody.com www.acomody.com;

    ssl_certificate /etc/letsencrypt/live/acomody.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/acomody.com/privkey.pem;

    client_max_body_size 100M;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/acomody /etc/nginx/sites-enabled/
certbot --nginx -d acomody.com -d www.acomody.com
nginx -t && systemctl reload nginx
```

---

## Korak 10 — Prvi deploy

Pushuj na `main` branch — GitHub Actions automatski:
1. Pokreće testove i PHPStan
2. Builduje Docker image i pushuje na `ghcr.io`
3. SSH-uje na staging i deployuje

Posle prvog deploya na staging, **jednom ručno** pokreni:

```bash
ssh deployer@STAGING_IP
cd /opt/acomody

docker compose -f docker-compose.staging.yml exec app php artisan storage:link
docker compose -f docker-compose.staging.yml exec app php artisan scout:sync-index-settings
docker compose -f docker-compose.staging.yml exec app php artisan db:seed --class=PlanSeeder
```

---

## Korak 11 — Deploy na produkciju

GitHub → Actions → CI/CD Pipeline → Run workflow

- Odaberi branch: `main`
- Označi: ✓ Deploy to production?
- Klikni: Run workflow

Posle prvog deploya na prod, jednom ručno:

```bash
ssh deployer@PROD_IP
cd /opt/acomody

docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app php artisan scout:sync-index-settings
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=PlanSeeder
```

---

## Korisne komande

```bash
# Logovi
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f horizon

# Status kontejnera
docker compose -f docker-compose.prod.yml ps

# Artisan komande
docker compose -f docker-compose.prod.yml exec app php artisan tinker
docker compose -f docker-compose.prod.yml exec app php artisan queue:monitor

# Restart
docker compose -f docker-compose.prod.yml restart app

# Ručno povlačenje novog image-a bez CI/CD
IMAGE_TAG=latest docker compose -f docker-compose.prod.yml pull
IMAGE_TAG=latest docker compose -f docker-compose.prod.yml up -d
```
