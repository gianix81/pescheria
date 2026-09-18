# 4. Checklist di collaudo e criteri di accettazione

Ambiente: dati del seeder (`php artisan migrate:fresh --seed`), 5 punti vendita, 10 prodotti,
opportunità nei diversi stati. Credenziali nella [README](../README.md).

## 4.1 Criteri di accettazione MVP

| # | Criterio | Come si verifica | Test automatico |
|---|---|---|---|
| 1 | Il Buyer crea un'opportunità completa, la salva e la invia in verifica | `/buyer/opportunita/nuova`, creazione rapida | `FlussoBuyerTecnicoTest::la_creazione_rapida_produce_una_bozza_completa` |
| 2 | Il Tecnico approva o rimanda con motivazione | `/tecnico/verifica/{id}` | `FlussoBuyerTecnicoTest::il_tecnico_approva…`, `…respinge_indicando_la_motivazione` |
| 3 | All'apertura solo i CR destinatari la vedono | login con CR di un PdV non destinatario | `PermessiRuoliTest::un_capo_reparto_non_vede_le_opportunita_di_un_altro_punto_vendita` |
| 4 | Ogni CR può acquistare con quantità valida oppure rifiutare | `/cr/opportunita/{id}` | `FlussoCapoRepartoTest` (9 test) |
| 5 | Scelta colli rapida quanto il sondaggio WhatsApp | pulsanti 1–6 + stepper + «Altra quantità» | `FlussoCapoRepartoTest::il_cr_ordina_con_un_pulsante_rapido…` |
| 6 | kg, ricarico e margine calcolati senza ambiguità | scheda opportunità, box prezzi | `PricingCalculatorTest` (8 test), `RisposteTest::acquisto_calcola_i_kg_dai_colli` |
| 7 | Nessun CR può inviare dopo la scadenza | modificare `closes_at` nel passato e riprovare | `RisposteTest::dopo_la_scadenza_non_si_puo_inviare` |
| 8 | La disponibilità limitata non si supera nemmeno con invii simultanei | 6 processi paralleli su 10 colli | `ConcorrenzaStockTest` |
| 9 | Buyer e Tecnico vedono chi ha risposto e chi manca | `/tecnico/monitor` | `MonitorCompilazioniTest` |
| 10 | Le modifiche sono tracciate | `/tecnico/audit`, `response_revisions` | `RisposteTest::ogni_modifica_registra_valore_precedente_e_nuovo` |
| 11 | L'XLSX produce la matrice per punto vendita con la data di consegna | `/export` → XLSX | `ExportTest::lxlsx_contiene_i_tre_fogli_richiesti` |
| 12 | Il CSV si apre in Excel con caratteri italiani | `/export` → CSV, apertura in Excel | `ExportTest::il_csv_normalizzato_ha_intestazioni_bom_e_accenti` |
| 13 | Le policy impediscono accessi incrociati | provare le rotte di un altro ruolo | `PermessiRuoliTest` (10 test) |
| 14 | I test critici passano | `php artisan test` | 98 test verdi |

## 4.2 Collaudo manuale

### A — Autenticazione e ruoli
- [ ] Login con credenziali errate: messaggio neutro, nessuna informazione sull'esistenza dell'account.
- [ ] Sei tentativi in un minuto: il sesto viene bloccato dal rate limiter.
- [ ] Utente disattivato: accesso negato anche con password corretta.
- [ ] Utente con `must_change_password`: ogni pagina reindirizza al cambio password.
- [ ] Buyer su `/tecnico/...` → 403; Tecnico su `/buyer/...` → 403; CR su entrambi → 403.

### A-bis — Super Admin
- [ ] `admin@pescheria.local` accede e atterra sulla gestione profili.
- [ ] Crea un profilo per ciascun ruolo; per un Capo Reparto il punto vendita è obbligatorio.
- [ ] Reimposta la password di un utente: compare una sola volta e il log non la contiene.
- [ ] Elimina un profilo: l'utente non accede più, ma compare in «Mostra eliminati» e si ripristina.
- [ ] Non riesce a eliminare o disattivare se stesso.
- [ ] Con un solo Super Admin attivo, non riesce a rimuoverlo, disattivarlo né declassarlo.
- [ ] Un Tecnico crea e modifica profili ma non vede Elimina, Password e non può nominare Super Admin.
- [ ] `php artisan pescheria:stato` non segnala problemi; con un account disattivato lo spiega.

### B — Creazione opportunità (Buyer)
- [ ] Creazione rapida: video dalla fotocamera, ricerca articolo per PLU, prezzi, IVA, kg per collo,
      colli limitati, scadenza, consegna, destinatari, invio in verifica in una sola schermata.
- [ ] Ricarico e margine si aggiornano da soli e restano distinti (etichette + tooltip).
- [ ] Scadenza precedente all'apertura → errore vicino al campo e nel riepilogo in testa.
- [ ] Disponibilità limitata senza totale colli → errore bloccante.
- [ ] Nessun media → l'invio in verifica è bloccato finché il Tecnico non autorizza l'eccezione.
- [ ] Creazione guidata: i 4 passi salvano gli stessi dati; «Anteprima CR» mostra la vista del reparto.
- [ ] Duplica: copia articolo, testi e prezzi; azzera disponibilità, scadenze, consegna e risposte.

### B-bis — Modifica di un'opportunità già pubblicata
- [ ] Dalla scheda di un'opportunità aperta compare «Modifica» e il modulo mostra il banner ambra.
- [ ] Il pulsante dice «Salva modifiche» e non compare «Invia in verifica».
- [ ] Dopo il salvataggio i capi reparto destinatari ricevono la notifica di modifica.
- [ ] Ridurre i colli sotto quelli già confermati viene rifiutato con un messaggio esplicito.
- [ ] Togliere un punto vendita che ha già risposto viene rifiutato, indicandone il codice.
- [ ] Cambiando i kg per collo, i kg delle risposte già inviate risultano riallineati.
- [ ] Un'opportunità in verifica non è modificabile; annullate e chiuse nemmeno.
- [ ] Salvando, l'opportunità torna in verifica e i capi reparto non la vedono più.
- [ ] I Tecnici ricevono la notifica «Da ripubblicare» e sulla scheda compare il pulsante WhatsApp per ciascuno.
- [ ] La schermata di verifica segnala che è una ripubblicazione e quante risposte ha già raccolto.
- [ ] Confermata dal Tecnico, torna aperta, i punti vendita sono avvisati e le risposte raccolte sono ancora lì.
- [ ] Se nel frattempo la scadenza è passata, la conferma la porta in Scaduta e non riapre i termini.

### B-ter — Avvisi su WhatsApp
- [ ] Inviata in verifica: compare un pulsante per ciascun Tecnico; con il numero in anagrafica apre la sua chat, senza numero apre l'elenco.
- [ ] Su un'opportunità aperta con risposte mancanti compare il sollecito diretto per ogni punto vendita che manca.
- [ ] Approvata: compare «Condividi nel gruppo WhatsApp» e il collegamento porta alla scheda del Capo Reparto.
- [ ] Un capo reparto che conferma vede «Comunica al gruppo» con quantità, kg e totale aggiornato.
- [ ] Aprendo il collegamento, WhatsApp mostra il messaggio già scritto e chiede solo il destinatario.
- [ ] Lo stesso collegamento del messaggio funziona per tutti i ruoli: il capo reparto arriva alla scheda d'ordine, Buyer e Tecnico a quella completa. Nessun 403.
- [ ] Aprendolo senza aver fatto l'accesso si passa dal login e poi si arriva all'opportunità.
- [ ] Un capo reparto di un punto vendita non destinatario legge «non è destinata al tuo punto vendita».
- [ ] «Copia testo» copia il messaggio negli appunti.
- [ ] Alla conferma di un punto vendita, Buyer e Tecnico ricevono la notifica in-app.

### C — Verifica (Tecnico)
- [ ] La checklist resta visibile durante lo scorrimento (pannello sticky).
- [ ] «Richiedi correzioni» senza motivazione → bloccato.
- [ ] Rifiuto → stato `DA_CORREGGERE` e notifica al Buyer con la motivazione.
- [ ] Approvazione con apertura futura → `PROGRAMMATA`; con apertura passata → `APERTA`.
- [ ] All'apertura i CR destinatari ricevono la notifica in-app; gli altri no.

### D — Risposta (Capo Reparto, da smartphone)
- [ ] Dopo il login si atterra sull'elenco delle opportunità, non su una dashboard.
- [ ] Ogni card mostra articolo, PLU, prezzo, peso collo, disponibilità, consegna e countdown senza aprirla.
- [ ] I conteggi compaiono nelle schede di filtro, non in riquadri dedicati.
- [ ] Un'opportunità esaurita lo dichiara già nell'elenco.
- [ ] Un'opportunità in scadenza entro 3 ore è evidenziata.
- [ ] Nella scheda compare la classifica degli altri punti vendita, con i totali di colli e kg.
- [ ] Il proprio punto vendita è evidenziato con il contrassegno «Tu».
- [ ] Con disponibilità limitata la barra mostra la percentuale già impegnata.
- [ ] Non compare da nessuna parte il nome della persona che ha ordinato per un altro PdV.
- [ ] Un punto vendita non destinatario non vede l'opportunità né i suoi totali.
- [ ] Dalla notifica alla conferma della quantità in meno di cinque tocchi.
- [ ] Il video parte solo su azione dell'utente, senza audio automatico, con controlli nativi.
- [ ] Pulsante `0` → conferma «Non acquista» (con motivazione se richiesta).
- [ ] Riepilogo `N colli × X kg = Y kg` aggiornato in tempo reale.
- [ ] Conferma modale prima dell'invio, ricevuta con data/ora dopo l'invio.
- [ ] Modifica della risposta fino alla scadenza; dopo la scadenza banner e azioni disabilitate.
- [ ] Barra azioni sticky in basso su mobile, target ≥ 44 px, countdown che non copre i dati.

### E — Disponibilità limitata
- [ ] Salvare una bozza non impegna stock.
- [ ] Due CR che chiedono più del residuo: il secondo vede lo stock rimanente e deve ridurre.
- [ ] Ridurre la quantità libera solo la differenza; rifiutare libera tutto.
- [ ] Residuo zero → «Esaurito», acquisto disabilitato, rifiuto ancora possibile.

### F — Monitoraggio e solleciti
- [ ] La matrice distingue non compilato, bozza, acquisto, rifiuto, riaperto.
- [ ] Filtri per stato, ricerca e data di consegna.
- [ ] «Seleziona mancanti» + «Sollecita» invia solo a chi non ha risposto.
- [ ] Ripetendo il sollecito automatico non arrivano doppioni (chiave di idempotenza).
- [ ] Riapertura di una singola risposta: nuova scadenza, motivazione, notifica al PdV, voce in audit.

### G — Export
- [ ] XLSX con i fogli `Matrice ordini`, `Dettaglio risposte`, `Mancanti e rifiuti`.
- [ ] Nella matrice le celle dei PdV non destinatari restano vuote (non zero).
- [ ] Il CSV si apre in Excel italiano con accenti corretti e separatore `;`.
- [ ] Il nome file contiene data di consegna e timestamp.
- [ ] Ogni export compare in `/tecnico/audit`.

### H — Sicurezza
- [ ] URL media senza firma → 403; con firma scaduta → 403.
- [ ] CR non destinatario che tenta l'URL firmato di un altro PdV → 403.
- [ ] Upload di `.php` rinominato in `.jpg` → rifiutato (MIME verificato lato server).
- [ ] File oltre i limiti → messaggio chiaro, nessun errore tecnico esposto.
- [ ] Modifica manuale dell'ID in URL (IDOR) → 403.

### I — Accessibilità e responsive
- [ ] Navigazione completa da tastiera con focus sempre visibile.
- [ ] Ogni campo ha una label associata; gli errori sono annunciati.
- [ ] Nessuna informazione veicolata dal solo colore.
- [ ] Layout utilizzabile da 360 px di larghezza, senza scorrimento orizzontale involontario.

### J — Automazioni
- [ ] Con lo scheduler fermo, una risposta dopo la scadenza resta comunque bloccata dal server.
- [ ] `php artisan opportunita:apri` eseguito due volte non altera `published_at`.
- [ ] `php artisan opportunita:scadi` genera il riepilogo finale a Buyer e Tecnico.

## 4.3 Esito dell'ultima esecuzione automatica

```
php artisan test
Tests:  98 passed (364 assertions)
```

## 4.4 Regressioni chiuse

| Data | Sintomo osservato | Causa | Correzione | Test che la protegge |
|---|---|---|---|---|
| 18/09/2026 | Caricando un video compare `validation.uploaded` | `APP_LOCALE=it` senza cartella `lang/it`: il traduttore restituiva la chiave grezza per **tutti** i messaggi standard | aggiunte le traduzioni italiane complete (`lang/it/validation.php`, `auth.php`, `passwords.php`, `pagination.php`) | `ValidazioneMessaggiTest::i_messaggi_di_validazione_sono_tradotti_in_italiano` |
| 18/09/2026 | Idem, per file oltre 2 MB | PHP senza `php.ini`: `upload_max_filesize = 2M`, `post_max_size = 8M`; inoltre Livewire limitava a 12 MB | `docker/php/uploads.ini` (110M/120M) applicato in Docker, in locale e via `bin/dev`; `config/livewire.php` allineato a `MEDIA_MAX_VIDEO_MB` | `ValidazioneMessaggiTest::i_limiti_di_upload_coprono_i_video_da_100_mb`, `…::un_video_da_50_mb_viene_accettato` |
