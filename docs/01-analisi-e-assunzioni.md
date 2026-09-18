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

## 1.2 Attori

- **Buyer** – crea, duplica, pubblica, chiude, esporta. Non risponde per conto dei PdV.
- **Tecnico** – verifica prima della pubblicazione, monitora le compilazioni, sollecita, riapre,
  amministra le anagrafiche. Non digita quantità al posto del CR.
- **Capo Reparto (CR)** – vede solo le opportunità pubblicate e destinate al proprio punto vendita,
  risponde `Acquista` (colli > 0) o `Non acquista` (0 colli, esplicito).

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

## 1.5 Regole di business non negoziabili implementate

1. `prezzo_vendita_netto = prezzo_vendita_lordo / (1 + aliquota_iva)`
2. `ricarico_% = ((netto - acquisto) / acquisto) × 100`
3. `margine_% = ((netto - acquisto) / netto) × 100`
4. L'assenza di risposta **non** vale zero.
5. La quantità rispetta lotto minimo e multiplo di ordinazione.
6. Dopo la scadenza (orologio del server) nessun CR può inviare o modificare.
7. Il totale confermato non può superare la disponibilità limitata, nemmeno con invii simultanei.
8. Ogni modifica è tracciata con utente, data/ora, valore precedente e nuovo.
