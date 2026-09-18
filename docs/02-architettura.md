# 2. Architettura applicativa

## 2.1 Stack

| Livello | Scelta | Motivazione |
|---|---|---|
| Runtime | PHP 8.4 (min. 8.3) | enum nativi, readonly, property hooks |
| Framework | Laravel 13 | policy, queue, scheduler, form request già pronti |
| DB | MySQL 8.4 InnoDB | `SELECT ... FOR UPDATE` necessario per lo stock |
| UI | Blade + Livewire 4 + Alpine | interattività (countdown, stepper, matrice) senza SPA |
| CSS | Tailwind 4 | design system a token, build Vite |
| Export | PhpSpreadsheet | XLSX reale multi-foglio |
| Storage | Flysystem (`local` / `s3`) | stesso codice in dev e produzione |

Niente SPA: il CR usa una singola scheda per opportunità, il Tecnico una matrice. Livewire copre
entrambi con render server-side, utile su rete mobile di punto vendita.

## 2.2 Livelli

```
routes/web.php ─▶ Middleware (auth, role, must-change-password)
                   │
                   ├─▶ Livewire components (UI state)        ┐
                   └─▶ Controller (export, media, auth)      ├─▶ Policy (autorizzazione)
                                                             │
                                                             ▼
                                        Servizi di dominio (app/Services)
                        OpportunityWorkflowService · AvailabilityService
                        ResponseSubmissionService  · NotificationService
                        ExportService              · AuditService
                                                             │
                                                             ▼
                                   Eloquent models + enum + DB transactions
```

**Regola:** nessuna regola di business nei componenti Livewire o nei controller. I componenti
raccolgono input, validano il formato e delegano al servizio; il servizio decide, scrive e traccia.
Questo rende il dominio testabile senza HTTP (`tests/Unit`) e riutilizzabile da un futuro `/api/v1`.

## 2.3 Servizi di dominio

| Servizio | Responsabilità | Invarianti garantite |
|---|---|---|
| `OpportunityWorkflowService` | transizioni di stato, approvazione, rifiuto, chiusura, annullamento, duplicazione | solo transizioni ammesse dalla macchina a stati; motivazione obbligatoria dove prevista |
| `AvailabilityService` | impegno/rilascio stock in transazione con `lockForUpdate` | `committed_packages <= total_packages` |
| `ResponseSubmissionService` | bozza, invio, rifiuto, riapertura, revisioni | scadenza server-side, lotto minimo/multiplo, storico completo |
| `NotificationService` | creazione notifica + consegne per canale, idempotenti | nessun duplicato grazie a `dedupe_key` unica |
| `ExportService` | CSV normalizzato e XLSX a 3 fogli | rispetta i filtri e registra in audit |
| `AuditService` | scrittura log immutabile | nessun update/delete sui log |

`AvailabilityService` incapsula l'assunzione A4: la politica "first confirmed, first served" è
un'implementazione di `App\Services\Availability\AllocationStrategy`; sostituirla con una
ripartizione proporzionale non tocca né UI né workflow.

## 2.4 Stato, tempo e job

- Tutte le date sono salvate in **UTC** (`config/app.php: timezone = UTC`) e mostrate in
  `Europe/Rome` tramite l'helper `it_datetime()` e il cast `->timezone(config('app.display_timezone'))`.
- Le transizioni temporali non dipendono dal browser: i comandi schedulati
  `opportunities:open` e `opportunities:expire` girano ogni minuto, sono **idempotenti**
  (filtrano per stato + finestra) e usano `withoutOverlapping()`.
- Ogni pagina ricalcola comunque lo stato effettivo lato server prima di consentire un'azione
  (`Opportunity::isAcceptingResponses()`), quindi anche con scheduler fermo non si ordina fuori tempo.

## 2.5 Sicurezza

- Sessione Laravel, hashing bcrypt, reset password con token, CSRF su tutte le POST.
- Autorizzazione a tre livelli: middleware di ruolo, policy per modello, scoping di query
  (`Opportunity::visibleTo($user)`): un CR non può nemmeno interrogare dati di un altro PdV.
- Media serviti solo da `MediaController` con policy e URL firmati temporanei.
- Rate limiting su login, upload e invio risposte.
- Audit log append-only (nessuna route di modifica, modello con `saving`/`deleting` bloccati).

## 2.6 Struttura cartelle

```
app/
  Console/Commands/      comandi schedulati (open/expire/reminder/summary)
  Enums/                 Role, OpportunityStatus, ResponseStatus, AvailabilityType, ...
  Http/Controllers/      Auth, Media, Export
  Http/Middleware/       EnsureUserHasRole, EnsurePasswordChanged
  Http/Requests/         form request condivisi
  Livewire/Buyer|Tecnico|Cr|Shared
  Models/
  Policies/
  Services/              dominio (vedi 2.3)
  Support/               PricingCalculator, helper formattazione
resources/views/
  components/layouts/    layout app + auth
  livewire/              viste dei componenti
  partials/              badge di stato, countdown, media
docs/                    analisi, architettura, schema, collaudo
```

## 2.7 Validazione e rate limiting

- La validazione di formato vive dove l'input entra: `Form Request` per i controller
  (`ExportController` valida i filtri prima di toccare il servizio) e regole dedicate nei
  componenti Livewire (`OpportunitaForm::regole()`, con messaggi e attributi in italiano).
- La validazione **di dominio** non è mai nel form: lotto minimo, multipli, finestra temporale,
  stock e transizioni sono verificati dai servizi e sollevano eccezioni di dominio
  (`App\Exceptions\*`) con messaggi già in italiano, mostrati all'utente senza dettagli tecnici.
  Così le stesse regole valgono anche per un futuro client API o per un comando da console.
- Rate limiter registrati in `AppServiceProvider`: `login` (5/min per email + 20/min per IP),
  `upload` (30/min), `risposte` (40/min), `export` (10/min).

## 2.8 Cosa resta fuori dall'MVP

`/api/v1` non è esposto: il dominio è già isolato nei servizi, quindi aggiungere controller API e
OpenAPI non comporta riscritture. L'interfaccia Blade/Livewire funziona senza alcun client esterno.
