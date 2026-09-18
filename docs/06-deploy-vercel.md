# 6. Deploy su Vercel

> **Premessa onesta.** Vercel è pensata per frontend e funzioni serverless, non per
> applicazioni PHP con database, code e upload pesanti. Questa configurazione funziona,
> ma richiede due servizi esterni a pagamento (MySQL e object storage) e lascia fuori
> alcune cose che su un normale hosting PHP funzionerebbero senza sforzo.
> Il confronto sta in fondo, al §6.6.

## 6.1 Perché il build falliva

Vercel trovava `vite` nel `package.json`, presumeva un progetto frontend e cercava `dist/`.
Il plugin Vite di Laravel compila invece in `public/build`. Il `vercel.json` ora:

- dichiara la funzione PHP (`vercel-php@0.8.0`, PHP 8.4, lo stesso della macchina di sviluppo);
- compila gli asset e li copia in `dist/`, che resta la cartella statica servita da CDN;
- instrada tutto ciò che non è un file statico verso `api/index.php`.

## 6.2 Cosa serve prima di deployare

| Servizio | Perché | Note |
|---|---|---|
| **MySQL gestito** | la funzione non ha un database | deve supportare `SELECT … FOR UPDATE` (vedi §6.4) |
| **Bucket S3** | il filesystem della funzione è in sola lettura e non è condiviso | serve anche per l'upload diretto dal browser |
| **Cron esterno** | il piano Hobby esegue i cron **una volta al giorno** | per esempio cron-job.org, ogni minuto |

## 6.3 Passi

1. **Database.** Crea un MySQL 8 gestito, poi applica le migrazioni da questa macchina
   puntando `.env` al database remoto:

   ```bash
   php artisan migrate --force
   php artisan db:seed --force      # facoltativo: anagrafiche e utenti iniziali
   ```

   Vercel non esegue le migrazioni durante il build: la funzione è in sola lettura e
   il build non ha accesso garantito al database.

2. **Bucket S3.** Crea il bucket **privato** e abilita la CORS per l'upload diretto:

   ```json
   [
     {
       "AllowedOrigins": ["https://<progetto>.vercel.app"],
       "AllowedMethods": ["PUT", "POST", "GET", "HEAD"],
       "AllowedHeaders": ["*"],
       "ExposeHeaders": ["ETag"],
       "MaxAgeSeconds": 3000
     }
   ]
   ```

3. **Variabili d'ambiente.** Copia quelle di [.env.vercel.example](../.env.vercel.example) in
   Project → Settings → Environment Variables. `APP_KEY` si genera con
   `php artisan key:generate --show`; `CRON_SECRET` con `openssl rand -hex 32`.

4. **Deploy.** Importa il repository GitHub. Il framework preset deve restare
   **Other**: `vercel.json` definisce già build, output e routing.

5. **Comandi pianificati.** Registra su un servizio di cron esterno una chiamata **ogni minuto** a:

   ```
   https://<progetto>.vercel.app/cron/esegui?token=<CRON_SECRET>
   ```

   L'endpoint esegue `opportunita:apri`, `opportunita:scadi` e `opportunita:solleciti`.
   Senza token valido risponde `404`. Nel `vercel.json` resta un cron giornaliero come rete
   di sicurezza, ma **da solo non basta**: aprire e far scadere le opportunità all'ora giusta
   richiede la frequenza al minuto.

## 6.4 Limiti da conoscere

**Upload.** Una funzione Vercel accetta richieste fino a **4,5 MB**: molto sotto ai 100 MB di un
video del pescato. Per questo `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` fa caricare il file
**direttamente dal browser a S3** con URL prefirmati; l'applicazione riceve solo il riferimento e
`MediaService` esegue una copia S3→S3. Se questa variabile non è impostata, gli upload oltre
4,5 MB falliscono.

**Lock di riga.** La garanzia che la disponibilità limitata non venga mai superata si regge su
`SELECT … FOR UPDATE` dentro una transazione. Verificalo sul database scelto prima di andare in
produzione: alcuni MySQL «compatibili» basati su proxy o sharding non lo supportano allo stesso
modo. Il test `ConcorrenzaStockTest` va eseguito puntando al database di produzione (su un
database di prova, non su quello reale).

**Code.** Non esistono worker persistenti: `QUEUE_CONNECTION=sync` esegue email e notifiche
dentro la richiesta dell'utente. Con l'invio email attivo, approvare un'opportunità destinata a
molti punti vendita diventa più lento e può avvicinarsi al `maxDuration` di 60 secondi.

**Scadenze.** Se il cron esterno smette di funzionare, gli stati non avanzano: le opportunità
restano `APERTA` a schermo. Le risposte fuori tempo **restano comunque bloccate**, perché la
finestra è verificata a ogni invio sull'orologio del server e non dipende dallo scheduler.

**Cold start.** Prima richiesta dopo un periodo di inattività: circa 250 ms di runtime più
l'avvio di Laravel. Percepibile, non bloccante.

**Log.** Niente file: `LOG_CHANNEL=stderr`, si leggono in Vercel → Logs, con la retention del piano.

## 6.5 Verifica dopo il primo deploy

- [ ] `https://<progetto>.vercel.app/up` risponde `200`.
- [ ] Il login funziona (se fallisce con errore 500, il database non è raggiungibile).
- [ ] La pagina ha lo stile corretto: se è senza CSS, `dist/build` non è stato generato.
- [ ] Un Buyer carica un video da più di 5 MB: se fallisce, manca `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3`.
- [ ] Il video si rivede nella scheda: se no, controlla la CORS del bucket.
- [ ] `curl "https://<progetto>.vercel.app/cron/esegui?token=<CRON_SECRET>"` restituisce JSON.
- [ ] Un export XLSX si scarica correttamente.

## 6.6 Confronto con un hosting PHP

| | Vercel | Hosting/VPS PHP + MySQL |
|---|---|---|
| Database | servizio esterno a pagamento | incluso |
| Media | bucket S3 obbligatorio | disco locale o S3, a scelta |
| Upload 100 MB | solo via upload diretto a S3 | diretto, nessun limite oltre `php.ini` |
| Code | `sync`, dentro la richiesta | worker reali |
| Scadenze | cron esterno ogni minuto | una riga di crontab |
| Costo tipico | piano Vercel + MySQL + S3 | un hosting solo |

Se in futuro vuoi spostarti, non serve toccare il codice: cambiano soltanto le variabili
d'ambiente. Il `docker-compose.yml` e il §6 del README coprono già quel percorso.
