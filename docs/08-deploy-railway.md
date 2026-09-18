# 8. Deploy su Railway

Railway esegue l'applicazione in un container persistente: niente limiti di dimensione delle
richieste, worker di coda reali, cron vero, filesystem scrivibile. È l'ambiente più vicino a un
server tradizionale fra quelli provati, e non richiede nessuna delle acrobazie del §6.

## 8.1 Servizi da creare nel progetto

| Servizio | Cosa fa | Comando di avvio |
|---|---|---|
| **App** (dal repository GitHub) | serve l'applicazione | quello predefinito di Nixpacks |
| **MySQL** (plugin Railway) | database | — |
| **Worker** (stesso repository) | invio notifiche | `php artisan queue:work --tries=3 --timeout=120` |
| **Scheduler** (stesso repository) | apertura, scadenza, solleciti | `php artisan schedule:work` |

Worker e Scheduler usano le **stesse variabili** del servizio App. Se preferisci contenere i costi
puoi ometterli: in tal caso imposta `QUEUE_CONNECTION=sync` e richiama `/cron/esegui?token=…`
da un servizio di cron esterno (§6.3, punto 5).

## 8.2 Variabili del servizio App

Vedi [.env.railway.example](../.env.railway.example): il blocco è pensato per essere incollato
nell'editor «Raw Editor» delle variabili di Railway.

I riferimenti `${{MySQL.MYSQLHOST}}` presuppongono che il servizio database si chiami `MySQL`
(il nome predefinito). Se lo hai rinominato, cambia il prefisso.

## 8.3 Migrazioni

Settings → Deploy → **Pre-Deploy Command**:

```
php artisan migrate --force
```

Gira prima che la nuova versione vada in linea, quindi lo schema è sempre allineato al codice.

Per caricare i dati iniziali una sola volta (utenti, punti vendita, prodotti), apri la shell del
servizio ed esegui `php artisan db:seed --force`.

### Primo accesso dal browser, senza shell

Se aprire una shell sul servizio non è pratico, imposta fra le variabili:

```
SETUP_TOKEN=<stringa casuale, es. openssl rand -hex 24>
```

poi apri `https://<dominio>/setup/<quella-stringa>` e compila il modulo: crea il primo Super
Admin e ti riporta alla pagina di accesso.

La pagina esiste **solo** se il token è impostato, corrisponde, e non c'è ancora un Super Admin
attivo: appena l'account è creato risponde 404 da sola, senza bisogno di ricordarsi di chiuderla.
Torna disponibile se l'ultimo Super Admin viene disattivato, come via di rientro.

### Se l'accesso non funziona

Nella shell del servizio:

```bash
php artisan pescheria:stato                                        # diagnosi completa
php artisan pescheria:stato --email=tuo@indirizzo.it               # stato di un account
php artisan pescheria:stato --email=tuo@indirizzo.it --password=X  # dice quale controllo fallisce
php artisan pescheria:admin --email=tuo@indirizzo.it               # crea o ripristina un Super Admin
```

Con `--password` il comando ripete gli stessi controlli del login — esistenza, eliminazione,
stato attivo, confronto dell'impronta della password — e dichiara se l'accesso *sarebbe accettato*.
Distingue quindi «password sbagliata» da «account disattivato» da «utente inesistente», che
dall'esterno appaiono tutti come «credenziali non valide». La password compare nella cronologia
della shell: usalo per diagnosi, non di routine.

**Attenzione a una trappola frequente:** `pescheria:admin` **sovrascrive** la password ogni volta
che viene eseguito. Se lo lanci copiando un esempio, la password diventa quella dell'esempio.

Le cause più frequenti, tutte segnalate dal comando di stato: il seeder non è mai stato eseguito
(nessun utente), le variabili `DB_*` non arrivano al servizio (modalità dimostrativa attiva,
banner ambra sul login), le migrazioni non sono state applicate, oppure l'account esiste ma è
disattivato.

## 8.4 Media caricati

Il filesystem del container si azzera a ogni deploy. Due strade:

- **Volume Railway** (semplice): Settings → Volumes → mount path `/app/storage`.
  Le variabili restano `MEDIA_DISK=media_local`. Sopravvive ai deploy.
- **Bucket S3**: `MEDIA_DISK=media_s3` più le `AWS_*`. Indicata se il volume cresce troppo.

Senza nessuna delle due i video caricati spariscono al deploy successivo.

## 8.5 Limiti di upload

`public/.user.ini` alza i limiti di PHP a 110 MB per file. Se un upload grande fallisce con
**413 Request Entity Too Large**, il blocco è del web server davanti a PHP: va alzato anche
`client_max_body_size` nella configurazione nginx generata da Nixpacks.

Va comunque ricordato che foto e video sono già compressi nel browser prima dell'invio
([§7](07-compressione-media.md)): un video da 300 MB parte sotto i 20 MB.

## 8.5-bis Se il build fallisce

Nixpacks legge i requisiti dal repository, non li indovina:

| Sintomo nel build log | Causa | Rimedio |
|---|---|---|
| `requires ext-gd * -> it is missing` (o `ext-zip`) | le estensioni arrivavano solo come dipendenza transitiva di PhpSpreadsheet | sono ora dichiarate in `composer.json`: Nixpacks le installa |
| `Vite requires Node.js version 20.19+` | veniva scelto un Node più vecchio | `engines.node` in `package.json` fissa `>=22.12` |
| `Your lock file does not contain a compatible set of packages` con decine di `symfony/... requires php >=8.4.1` | il `composer.lock` era stato risolto su PHP 8.4, mentre la piattaforma installa il **minimo** del vincolo (`^8.3` → 8.3) | `config.platform.php` in `composer.json` è fissato a `8.3.0`: il lock viene risolto contro la versione minima dichiarata e funziona sia su 8.3 sia su 8.4 |
| `vite: not found` durante `npm run build` | il build gira dopo `npm prune --omit=dev` | gli strumenti di build stanno fra le `dependencies`, non fra le `devDependencies` |
| pagina bianca o 404 dopo un build riuscito | document root sbagliata | variabile `NIXPACKS_PHP_ROOT_DIR=/app/public` |
| `No application encryption key` | manca `APP_KEY` | vedi §8.2 |
| `Invalid URI` durante `php artisan config:cache` | `APP_URL` conteneva `${{RAILWAY_PUBLIC_DOMAIN}}`, non ancora risolto perché il dominio pubblico nasce col primo deploy riuscito | `App\Support\AppUrl` scarta i riferimenti non risolti e ricade sul dominio della piattaforma. **Dopo il primo deploy, imposta `APP_URL` al dominio reale**: i link nelle notifiche inviate da coda e scheduler nascono da lì |

## 8.6 Verifica dopo il primo deploy

- [ ] `https://<dominio>/up` risponde `200`.
- [ ] `APP_URL` è stata sostituita con il dominio reale e il servizio è stato ridistribuito
      (i link nelle notifiche generate fuori da una richiesta HTTP dipendono da questo valore).
- [ ] La pagina di accesso **non** mostra il banner «Ambiente dimostrativo»: se lo mostra, le
      variabili `DB_*` non sono arrivate al servizio.
- [ ] Il login funziona e la dashboard mostra i dati del seeder.
- [ ] Un Buyer carica un video: deve comparire nella scheda dopo il caricamento.
- [ ] Dopo un redeploy il video è ancora lì (se non c'è, manca il volume: §8.4).
- [ ] Nei log del servizio Scheduler compare «Running scheduled tasks» ogni minuto.
