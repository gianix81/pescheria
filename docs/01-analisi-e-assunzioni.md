# 1. Analisi funzionale e assunzioni

## 1.1 Problema

Il processo attuale (AS-IS) vive in un gruppo WhatsApp: video verticale del prodotto, scheda
testuale incollata nella domanda di un sondaggio, opzioni `1..6` colli, voto dei Capi Reparto,
ricostruzione manuale dei totali alla chiusura del sondaggio.

Limiti strutturali:

| Limite AS-IS | Conseguenza | Risposta TO-BE |
|---|---|---|
| Il sondaggio non distingue "non compro" da "non ho risposto" | ordini dedotti a mano, errori | stati risposta espliciti `INVIATA_RIFIUTO` vs `NON_COMPILATA` |
| Il votante è un numero di telefono | associazione PdV incerta | account CR legato al punto vendita a database |
| Nessun controllo disponibilità | sovra-allocazione | `AvailabilityService` transazionale con lock di riga |
| Totali calcolati a mano | errori su colli/kg | calcolo automatico `kg = colli × kg_per_collo` |
| "Margine" ambiguo | prezzi sbagliati | ricarico e margine calcolati e mostrati separatamente |
| Nessuna tracciabilità | contestazioni | audit log immutabile + revisioni risposta |
| Modifica del sondaggio dopo la scadenza | ordini non validi | scadenza applicata server-side dallo scheduler |

> **Nota sulle diciture.** Nell'interfaccia il ruolo che ordina per il negozio si chiama
> **«Punto vendita»**, non più «Capo Reparto»: è il termine che l'azienda usa davvero. Nel codice
> il valore del ruolo resta `CAPO_REPARTO` — rinominarlo avrebbe richiesto di migrare i dati e
> riscrivere ogni riferimento senza che nulla cambiasse a schermo. Nei documenti tecnici le due
> diciture si equivalgono.

## 1.2 Attori

- **Buyer** – crea, duplica, pubblica, chiude, esporta. Non risponde per conto dei PdV.
- **Tecnico** – verifica prima della pubblicazione, monitora le compilazioni, sollecita, riapre,
  amministra le anagrafiche. Non digita quantità al posto del CR.
- **Capo Reparto (CR)** – vede le opportunità pubblicate e destinate al proprio punto vendita,
  risponde `Acquista` (colli > 0) o `Non acquista` (0 colli, esplicito). Dentro un'opportunità
  vede anche quanto hanno ordinato gli altri punti vendita destinatari (vedi §1.6).

## 1.3 Flusso TO-BE

```
Buyer  ──crea (rapida|guidata)──▶ BOZZA ──invia──▶ IN_VERIFICA ──▶ Tecnico
                                     ▲                  │
                                     └── DA_CORREGGERE ◀─┘ (respinta + motivazione)
                                                        │ approva
                                       PROGRAMMATA ─────┴─────▶ APERTA
                                       (job scheduler)          │  CR rispondono
                                                                ▼
                                        SCADUTA / CHIUSA / ANNULLATA ─▶ ARCHIVIATA
                                                                │
                                                                ▼ export CSV/XLSX
```

## 1.4 Assunzioni MVP (da validare prima della produzione)

Tutte le assunzioni di seguito sono isolate nel codice in modo da poter evolvere senza riscritture:

| # | Assunzione | Dove è isolata | Come evolverà |
|---|---|---|---|
| A1 | Un CR appartiene a un solo punto vendita | `users.store_id` + `StorePolicy` | tabella pivot `store_user` |
| A2 | Una sola data di consegna per opportunità | `opportunities.delivery_date` | colonna su `opportunity_stores` |
| A3 | Ordini in numero intero di colli, peso decimale | `responses.packages` (unsigned int) | nuova unità in `order_unit` |
| A4 | Disponibilità limitata "first confirmed, first served" | `AvailabilityService` | strategia alternativa iniettabile (`AllocationStrategy`) |
| A5 | Il Tecnico gestisce le anagrafiche | `role:TECNICO` su rotte `/anagrafiche` | ruolo Admin separato |
| A6 | Prezzo acquisto, vendita e IVA obbligatori | `OpportunityRequest` + eccezione tracciata | flag di configurazione |
| A7 | Il "margine" del gruppo WhatsApp = **ricarico** sul costo | `PricingCalculator` | nessuna, è una correzione definitiva |
| A8 | Notifiche in-app obbligatorie; email/WhatsApp opzionali | `NotificationService` + driver per canale | attivazione canale in `config/notifiche.php` |
| A9 | WhatsApp notifica ma non registra ordini | `WhatsAppChannel` invia solo deep link | invariata per policy |
| A10 | L'export non è ancora inviato all'ERP | `ExportService` | job di push verso ERP |
| A11 | Storage locale in sviluppo, S3 in produzione | disco `media` in `config/filesystems.php` | variabile `.env` |
| A12 | Scansione antivirus: punto di estensione documentato, non implementata | `ScanUploadedMedia` job | integrazione ClamAV/servizio |

## 1.4-bis Visibilità fra punti vendita (regola rivista)

La prima stesura prevedeva che un CR non vedesse quantità e decisioni degli altri punti vendita.
Su indicazione del committente la regola è stata **rovesciata**: dentro un'opportunità tutti i
destinatari vedono quanto ordinano gli altri, per creare emulazione fra i reparti.

Cosa cambia e cosa no:

| | Prima | Ora |
|---|---|---|
| Quantità ordinate dagli altri PdV | nascoste | **visibili a tutti i destinatari** |
| Totale ordinato sull'opportunità | nascosto | **visibile**, anche nelle card di elenco |
| Opportunità non destinate al proprio PdV | invisibili | invisibili (invariato) |
| Modifica della risposta altrui | vietata | vietata (invariato) |
| Nome della persona che ha ordinato | — | **non esposto** agli altri PdV: si mostra il codice del punto vendita. Buyer e Tecnico continuano a vederlo |

Il perimetro resta quello dell'opportunità: un punto vendita non destinatario non vede nulla,
nemmeno i totali. La separazione fra *vedere* e *poter agire* è applicata dalla policy
(`ResponsePolicy::view` contro `ResponsePolicy::update`) e coperta da
`VisibilitaFraPuntiVenditaTest`.

Effetto collaterale da tenere presente: con disponibilità limitata la classifica visibile accelera
la corsa all'ultimo collo, ed è probabile che le quantità visibili influenzino le scelte di chi
ordina dopo. È l'effetto voluto, ma va considerato leggendo i dati storici.

## 1.4-ter Super Admin (ruolo aggiunto)

La prima stesura prevedeva tre soli ruoli, con le anagrafiche in carico al Tecnico e nessun
ruolo «Admin» (assunzione A5). Su richiesta del committente è stato aggiunto un quarto ruolo,
**Super Admin**, perché serviva qualcuno in grado di creare, modificare ed eliminare i profili.

| Facoltà | Tecnico | Super Admin |
|---|---|---|
| Creare e modificare utenti, punti vendita, prodotti | sì | sì |
| Assegnare il ruolo Super Admin | no | sì |
| Eliminare e ripristinare un profilo | no | sì |
| Reimpostare la password di un altro utente | no | sì |
| Verificare le opportunità, monitorare, esportare | sì | sì |
| Creare opportunità (facoltà del Buyer) | no | sì |
| Rispondere per un punto vendita | no | no (non ha un PdV) |

Il Super Admin supera i controlli di Buyer e Tecnico, ma **non** quelli del Capo Reparto: non è
associato a un punto vendita e non può quindi ordinare al posto di nessuno.

Due paracadute impediscono di chiudersi fuori: non ci si può eliminare o disattivare da soli, e
l'ultimo Super Admin attivo non può essere rimosso, disattivato o declassato. In caso estremo
resta la console: `php artisan pescheria:admin`.

## 1.4-quater Modifica dell'opportunità dopo la pubblicazione

Il capitolato (§3) dice che il Buyer «crea e modifica opportunità in qualsiasi momento». Una prima
versione limitava la modifica alle bozze e alle opportunità respinte: corretto.

Stati modificabili: `BOZZA`, `DA_CORREGGERE`, `PROGRAMMATA`, `APERTA`, `SCADUTA`.
Restano bloccati `IN_VERIFICA` — §6 la vuole ferma fino all'esito del Tecnico — e gli stati
terminali `CHIUSA`, `ANNULLATA`, `ARCHIVIATA`.

**La modifica non va in linea da sola.** Su indicazione del committente, salvando un'opportunità
già pubblicata questa torna in `IN_VERIFICA`: la ripubblicazione passa sempre dal Tecnico, come la
prima pubblicazione. Il ciclo è:

```
APERTA ──Buyer «Salva e ripubblica»──▶ IN_VERIFICA ──Tecnico conferma──▶ APERTA
```

Conseguenza da conoscere: **finché è in verifica i punti vendita non la vedono**. Per questo
ricevono subito una notifica che spiega perché è sparita, e i Tecnici una che chiede di
ripubblicarla. Le risposte già raccolte restano intatte e tornano visibili con l'opportunità.

Se la scadenza è nel frattempo passata, la conferma del Tecnico la porta direttamente in
`SCADUTA` invece che in `APERTA`: non si riapre un termine già scaduto per effetto di una modifica.

| Situazione | Comportamento |
|---|---|
| Qualsiasi modifica | torna in verifica, notifica a destinatari e Tecnici, voce nell'audit log |
| Riduzione dei colli sotto quelli già confermati | rifiutata, con il numero già impegnato nel messaggio |
| Rimozione di un punto vendita che ha già risposto | rifiutata, con i codici interessati |
| Modifica dei kg per collo | i kg delle risposte già raccolte vengono riallineati, con voce di audit dedicata |
| Aumento della disponibilità | sempre consentito, riapre gli acquisti se era esaurita |

Il vincolo forte resta quello del §7: il Buyer non tocca le quantità decise dai Capi Reparto. Le
correzioni su una singola risposta passano per la funzione dedicata, tracciata separatamente.

## 1.4-quinquies Pagina d'ingresso del Capo Reparto

Su indicazione del committente il Capo Reparto **non** ha una dashboard: chi apre l'applicazione
da un reparto vuole vedere subito la merce disponibile, non dei contatori.

La pagina d'ingresso è quindi l'elenco delle opportunità. I conteggi non sono spariti, si sono
spostati dove non rubano spazio: dentro le schede di filtro («Da rispondere 3»). Resta una sola
riga di avviso, e solo quando c'è davvero qualcosa in scadenza.

Ogni card mostra ciò che serve per decidere senza aprirla: anteprima grande di foto o video,
titolo, codice articolo, PLU, origine, prezzo di vendita, peso del collo, disponibilità residua,
data di consegna, countdown, la propria risposta se già inviata e quanto hanno ordinato gli altri.

## 1.4-sexies Eliminazione definitiva di un'opportunità

Oltre all'annullamento — che conserva tutto e comunica ai punti vendita — il Buyer può eliminare
definitivamente un'opportunità creata per errore o troppo vecchia per meritare l'archivio.

| Situazione | Motivazione |
|---|---|
| Termine passato da almeno un mese | non richiesta |
| Tutto il resto, comprese le bozze | obbligatoria |

L'eliminazione porta via media, risposte, revisioni e destinatari. **Resta però la voce nell'audit
log**, che non ha vincoli verso le opportunità e sopravvive alla cancellazione: vi si trovano
riferimento, articolo, stato, scadenza, numero di risposte, colli ordinati e motivazione. Di ciò
che è stato eliminato resta traccia di chi, quando e perché.

La conferma dichiara in anticipo quanti file, quante risposte e quanti colli si stanno per perdere:
è un'operazione irreversibile e non deve sembrare una qualsiasi.

## 1.4-septies Correzione del prezzo da parte del Tecnico

Su indicazione del committente il Tecnico può correggere il **prezzo di vendita al pubblico**
durante la verifica. È l'unico dato che modifica direttamente, e solo in stato `IN_VERIFICA`:
dopo la pubblicazione il prezzo è già stato letto da chi decide se ordinare, e cambiarlo di nascosto
falserebbe una scelta già fatta.

- ricarico e margine si **ricalcolano**, non si digitano: resta valida la regola del §5.1;
- la schermata di verifica mostra l'effetto della correzione *prima* di salvare, così il Tecnico
  vede dove va a finire il ricarico mentre digita;
- il Buyer riceve una notifica con il prima e il dopo: è un suo dato che è cambiato;
- la correzione finisce nell'audit log con entrambi i valori e l'eventuale nota;
- salvare lo stesso prezzo non produce né notifica né voce di audit.

Il prezzo di **acquisto** resta del Buyer: è il dato negoziato col fornitore e il Tecnico non lo tocca.

## 1.5 Regole di business non negoziabili implementate

1. `prezzo_vendita_netto = prezzo_vendita_lordo / (1 + aliquota_iva)`
2. `ricarico_% = ((netto - acquisto) / acquisto) × 100`
3. `margine_% = ((netto - acquisto) / netto) × 100`
4. L'assenza di risposta **non** vale zero.
5. La quantità rispetta lotto minimo e multiplo di ordinazione.
6. Dopo la scadenza (orologio del server) nessun CR può inviare o modificare.
7. Il totale confermato non può superare la disponibilità limitata, nemmeno con invii simultanei.
8. Ogni modifica è tracciata con utente, data/ora, valore precedente e nuovo.
