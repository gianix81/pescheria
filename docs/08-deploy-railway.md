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
| `Vite requires Node.js version 20.19+` | Nixpacks sceglieva un Node più vecchio | `engines.node` in `package.json` fissa `>=22.12` |
| pagina bianca o 404 dopo un build riuscito | document root sbagliata | variabile `NIXPACKS_PHP_ROOT_DIR=/app/public` |
| `No application encryption key` | manca `APP_KEY` | vedi §8.2 |

## 8.6 Verifica dopo il primo deploy

- [ ] `https://<dominio>/up` risponde `200`.
- [ ] La pagina di accesso **non** mostra il banner «Ambiente dimostrativo»: se lo mostra, le
      variabili `DB_*` non sono arrivate al servizio.
- [ ] Il login funziona e la dashboard mostra i dati del seeder.
- [ ] Un Buyer carica un video: deve comparire nella scheda dopo il caricamento.
- [ ] Dopo un redeploy il video è ancora lì (se non c'è, manca il volume: §8.4).
- [ ] Nei log del servizio Scheduler compare «Running scheduled tasks» ogni minuto.
