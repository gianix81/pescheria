# Ordini pescheria — opportunità di acquisto per la GDO

Applicazione web che sostituisce il gruppo WhatsApp usato oggi per raccogliere gli ordini dei
reparti pescheria: il Buyer pubblica un'opportunità (video, scheda articolo, prezzi, disponibilità,
scadenza, consegna), il Tecnico la verifica e monitora le compilazioni, ogni punto vendita dichiara
esplicitamente se acquista — e quanti colli — oppure no.

- **Stack:** PHP 8.3+ · Laravel 13 · MySQL 8 · Blade + Livewire 4 + Alpine · Tailwind 4 · PhpSpreadsheet
- **Lingua:** italiano · **Fuso:** dati in UTC, interfaccia in `Europe/Rome`
- **Documentazione:** [analisi e assunzioni](docs/01-analisi-e-assunzioni.md) ·
  [architettura](docs/02-architettura.md) · [schema dati](docs/03-schema-dati.md) ·
  [mappa schermate](docs/05-mappa-schermate.md) · [collaudo](docs/04-collaudo.md)

---

## 1. Requisiti

| Componente | Versione minima | Note |
|---|---|---|
| PHP | 8.3 | estensioni: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `dom`, `xml` — dichiarate in `composer.json`. Il `composer.lock` è risolto contro PHP 8.3 (`config.platform.php`), quindi vale anche su 8.4 |
| Composer | 2.x | |
| MySQL | 8.0 | InnoDB; servono `SELECT ... FOR UPDATE` e transazioni |
| Node.js | 20 | solo per compilare gli asset |

## 2. Installazione (sviluppo)

```bash
git clone <repo> ordini-pescheria && cd ordini-pescheria

composer install
cp .env.example .env
php artisan key:generate

# --- database -------------------------------------------------------------
mysql -u root -e "CREATE DATABASE pescheria      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE DATABASE pescheria_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# imposta DB_USERNAME / DB_PASSWORD in .env, poi:
php artisan migrate --seed

# --- asset ----------------------------------------------------------------
npm install
npm run build        # oppure: npm run dev

# --- avvio ----------------------------------------------------------------
bin/dev serve        # equivale a: php artisan serve, con i limiti di upload corretti
```

Applicazione su <http://localhost:8000>.

`bin/dev` accetta `serve`, `queue`, `scheduler` o `all`.

### Credenziali dimostrative (solo ambiente locale)

| Ruolo | Email | Password |
|---|---|---|
| Super Admin | `admin@pescheria.local` | `Pescheria2026!` |
| Buyer | `buyer@pescheria.local` | `Pescheria2026!` |
| Tecnico | `tecnico@pescheria.local` | `Pescheria2026!` |
| Punto vendita PV001…PV005 | `cr1@pescheria.local` … `cr5@pescheria.local` | `Pescheria2026!` |

> Fuori dall'ambiente `local` il seeder imposta `must_change_password = true`: al primo accesso
> l'applicazione obbliga al cambio password. **Non usare queste credenziali in produzione.**

## 3. Configurazione

### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pescheria
DB_USERNAME=root
DB_PASSWORD=
DB_TEST_DATABASE=pescheria_test
```

### Storage dei media

I media non sono mai pubblici: vengono serviti da `MediaController` tramite URL firmati a scadenza.

```dotenv
MEDIA_DISK=media_local        # sviluppo: storage/app/media
MEDIA_MAX_IMAGE_MB=10
MEDIA_MAX_VIDEO_MB=100
MEDIA_SIGNED_URL_MINUTES=30
MEDIA_ANTIVIRUS_ENABLED=false # attiva il job ScanUploadedMedia (da integrare con ClamAV)
```

In produzione con object storage:

```dotenv
MEDIA_DISK=media_s3
AWS_ACCESS_KEY_ID=…
AWS_SECRET_ACCESS_KEY=…
AWS_DEFAULT_REGION=eu-south-1
AWS_BUCKET=ordini-pescheria
```

#### Limiti di upload (obbligatori)

I video verticali del prodotto arrivano a 100 MB: con i default di PHP (`upload_max_filesize = 2M`,
`post_max_size = 8M`) il caricamento fallisce **prima** della validazione applicativa, con un errore
generico. Il file [`docker/php/uploads.ini`](docker/php/uploads.ini) contiene i valori corretti:

| Contesto | Come applicarlo |
|---|---|
| Docker | già copiato in `/usr/local/etc/php/conf.d/` dal `Dockerfile` |
| PHP-FPM / Apache | copiare `docker/php/uploads.ini` in `conf.d/` e riavviare il servizio |
| Sviluppo locale | usare `bin/dev serve` (imposta `PHPRC`) oppure copiare il file nel proprio `php.ini` |

Va allineato anche il server web: `client_max_body_size 120M;` su nginx.

Livewire ha un limite proprio, di default 12 MB: è già alzato in
[`config/livewire.php`](config/livewire.php) a `MEDIA_MAX_VIDEO_MB`.

### Queue

```dotenv
QUEUE_CONNECTION=database
```

```bash
php artisan queue:work --tries=3 --timeout=120
```

In produzione usare supervisor/systemd. Le code trasportano consegne notifiche
(`DeliverNotification`) ed eventuale scansione antivirus.

### Scheduler

Una sola voce di cron:

```cron
* * * * * cd /var/www/ordini-pescheria && php artisan schedule:run >> /dev/null 2>&1
```

Comandi pianificati (tutti idempotenti, con `withoutOverlapping`):

| Comando | Frequenza | Effetto |
|---|---|---|
| `opportunita:apri` | ogni minuto | `PROGRAMMATA → APERTA` all'ora di apertura |
| `opportunita:scadi` | ogni minuto | `APERTA → SCADUTA` alla scadenza, più riepilogo finale |
| `opportunita:solleciti` | ogni minuto | promemoria ai CR mancanti alle soglie configurate |
| `queue:prune-failed` | giornaliero | pulizia job falliti |

```dotenv
NOTIFY_REMINDER_MINUTES=120,30   # minuti prima della scadenza
```

### Notifiche

Le notifiche in-app sono sempre attive. Email e WhatsApp sono opzionali:

```dotenv
NOTIFY_EMAIL_ENABLED=true
NOTIFY_WHATSAPP_ENABLED=false
WHATSAPP_API_URL=https://graph.facebook.com/v20.0
WHATSAPP_API_TOKEN=…
WHATSAPP_PHONE_ID=…
```

WhatsApp **non** è la fonte dell'ordine: il messaggio contiene solo un deep link alla scheda, che
richiede autenticazione. Nessun pulsante registra ordini fuori dall'applicazione.

L'azienda usa **WhatsApp personale**, quindi gli avvisi non passano da alcuna API: l'app prepara il
messaggio già scritto e apre la conversazione giusta con un tocco — quella del singolo Tecnico o
capo reparto se il numero è in anagrafica, altrimenti l'elenco delle chat per scegliere il gruppo.
Momenti coperti e testi in [docs/09-avvisi-whatsapp.md](docs/09-avvisi-whatsapp.md).

### Compressione dei media

Foto e video vengono **compressi nel browser prima dell'invio**: le foto a 1920 px, i video a
1280 px in MP4/H.264 con WebCodecs. Un video da 300 MB parte tipicamente sotto i 20 MB, quindi
l'upload dal punto di vendita è rapido. Soglie e limiti in
[docs/07-compressione-media.md](docs/07-compressione-media.md). Se il browser non supporta la
transcodifica, il file viene inviato com'è: nessun percorso resta bloccato.

### Primo accesso in produzione

Due strade, a seconda che tu possa aprire una shell.

**Dal browser** (nessuna shell): imposta `SETUP_TOKEN` fra le variabili d'ambiente e apri
`https://<dominio>/setup/<token>`. Crei il primo Super Admin e la pagina si disattiva da sola.
Esiste solo finché non c'è un Super Admin attivo; se risponde 404 il motivo è nei log del
servizio. L'endpoint `/up` dichiara quale versione è pubblicata:
`{"stato":"ok","versione":"b9d25bd","ambiente":"production"}`.

**Dalla console:**

Il seeder popola anagrafiche e utenti dimostrativi; in produzione crea invece il tuo Super Admin
dalla console, senza dipendere da esso:

```bash
php artisan pescheria:admin --email=tuo@indirizzo.it
# stampa una password generata: annotala, non è recuperabile
```

Lo stesso comando **ripristina** un profilo eliminato o disattivato e lo promuove a Super Admin:
è la via d'accesso garantita quando nessuno riesce più a entrare.

Se l'accesso non funziona, il comando di diagnosi dice perché in trenta secondi:

```bash
php artisan pescheria:stato --email=utente@indirizzo.it
php artisan pescheria:stato --email=utente@indirizzo.it --password=DaVerificare
```

Verifica chiave applicativa, raggiungibilità del database, migrazioni non applicate, modalità
dimostrativa, presenza di utenti e stato del singolo account (attivo, eliminato, cambio password
richiesto). Con `--password` ripete i controlli del login e dice se l'accesso sarebbe accettato,
distinguendo password errata, account disattivato e utente inesistente.

> `pescheria:admin` **sovrascrive** la password a ogni esecuzione: attenzione a non lanciarlo
> copiando un esempio, o la password diventerà quella dell'esempio.

### Export per il portale del fornitore

Oltre a CSV e XLSX, l'applicazione genera il file nel tracciato richiesto dal portale
(`Assegnazione per portale.xlsx`, foglio `DATI`): riprodotto identico all'originale e verificato
cella per cella contro il file reale. Richiede i codici portale nelle anagrafiche di punti vendita
e prodotti — vedi [docs/10-tracciato-portale.md](docs/10-tracciato-portale.md).

## 4. Test

I test girano su MySQL (come la produzione): servono lock di riga reali.

```bash
php artisan test                      # tutta la suite (107 test)
php artisan test --testsuite=Unit
php artisan test --filter=ConcorrenzaStockTest
npm run test:js                       # compressione media, in un Chrome headless reale
```

`ConcorrenzaStockTest` avvia **processi PHP paralleli** che competono sullo stesso stock e verifica
che la disponibilità limitata non venga mai superata.

Copertura dei casi obbligatori: permessi dei tre ruoli, transizioni di workflow, apertura/scadenza
automatica, blocco dopo la scadenza, lotto minimo e multipli, calcolo kg, ricarico e margine con
IVA 4/10/22%, distinzione fra zero confermato e mancata risposta, disponibilità aperta e limitata,
concorrenza, isolamento fra punti vendita, approvazione e rifiuto, riapertura con audit,
export CSV/XLSX con accenti e totali, upload sicuri, filtri del monitor.

## 5. Docker (opzionale)

```bash
docker compose up -d          # MySQL 8 + Mailpit + app su http://localhost:8000
docker compose exec app php artisan migrate --seed
```

Mailpit (anteprima email): <http://localhost:8025>.

## 6. Deploy su Vercel

Configurazione presente (`vercel.json`, `api/index.php`). Per un uso reale servono un MySQL
gestito, un bucket S3 e un cron esterno: limiti e passi in
[docs/06-deploy-vercel.md](docs/06-deploy-vercel.md), variabili in
[.env.vercel.example](.env.vercel.example).

Per una **dimostrazione** non serve configurare nulla: senza alcun database impostato
l'applicazione parte da sola su un SQLite temporaneo (`DEMO_MODE=false` lo impedisce,
`DEMO_MODE=true` lo forza). I dati si azzerano a ogni riavvio e la garanzia sulla
disponibilità limitata non vale, quindi non è adatta a raccogliere ordini veri; un banner lo
ricorda su ogni pagina.

## 6-bis. Deploy su Railway (consigliato fra i PaaS)

Container persistente: nessun limite sulle richieste, worker di coda e scheduler reali, database
MySQL come plugin. Variabili pronte in [.env.railway.example](.env.railway.example), procedura in
[docs/08-deploy-railway.md](docs/08-deploy-railway.md).

## 7. Deploy su hosting PHP tradizionale

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link        # solo se si usano dischi pubblici
```

Checklist minima:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` con HTTPS (il provider forza `https`);
- utente MySQL dedicato, senza privilegi amministrativi;
- worker di coda e cron dello scheduler attivi e monitorati;
- backup giornaliero del database (retention consigliata 30 giorni) e del bucket media;
- `/up` è l'endpoint di health check;
- log applicativi senza dati sensibili (l'audit non registra password né token).

## 8. Struttura del progetto

```
app/
  Console/Commands/     opportunita:apri · opportunita:scadi · opportunita:solleciti
  Enums/                Role, OpportunityStatus, ResponseStatus, AvailabilityType, …
  Http/                 controller (auth, media, export) e middleware di ruolo
  Livewire/             Buyer/ · Tecnico/ · Cr/ · Shared/
  Models/               Opportunity, Response, Store, Product, AuditLog, …
  Policies/             autorizzazione per modello
  Services/             workflow, disponibilità, risposte, notifiche, export, audit
  Support/              PricingCalculator, Format, Navigation
resources/views/        layout, componenti Blade, viste Livewire
database/               migration, factory, seeder
docs/                   analisi, architettura, schema dati, schermate, collaudo
tests/                  Unit/ e Feature/ (98 test)
```

## 9. Fuori scope dell'MVP (dichiarato)

- Invio automatico dell'ordine a ERP o fornitori: l'export è il punto di consegna.
- API JSON pubbliche: il dominio è già isolato nei servizi, un futuro `/api/v1` non richiede
  riscritture, ma non è implementato.
- Scansione antivirus: presente il punto di estensione (`ScanUploadedMedia`), non il motore.
- Gestione di più punti vendita per un singolo utente di punto vendita (assunzione A1).
