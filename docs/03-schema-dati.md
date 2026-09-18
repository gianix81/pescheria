# 3. Schema ER e dizionario dei dati

## 3.1 Diagramma

```
                 ┌────────────┐            ┌───────────┐
                 │   stores   │            │ products  │
                 └─────┬──────┘            └─────┬─────┘
                       │ 1                       │ 1
                       │                         │
          ┌────────────┴──┐                      │ (snapshot alla pubblicazione)
          │     users     │ 1                    │
          │ role, store_id│──────┐               │
          └───────┬───────┘      │               │
                  │ created_by   │               │
                  ▼              │               ▼
            ┌─────────────────────────────────────────┐
            │             opportunities               │
            │ status, availability_type, stock, prezzi│
            └───┬────────────┬───────────┬────────┬───┘
      1..n      │      1..n  │     1..n  │   1..n │
        ┌───────▼───┐ ┌──────▼──────┐ ┌──▼─────┐ ┌▼──────────────────┐
        │opportunity│ │ opportunity │ │responses│ │opportunity_reviews│
        │  _media   │ │  _stores    │ └───┬────┘ └───────────────────┘
        └───────────┘ └─────────────┘     │ 1..n
                                    ┌─────▼────────────┐
                                    │response_revisions│
                                    └──────────────────┘

   ┌──────────────┐ 1..n ┌────────────────────────┐        ┌────────────┐
   │ notifications│─────▶│ notification_deliveries│        │ audit_logs │
   └──────────────┘      └────────────────────────┘        └────────────┘
```

## 3.2 Dizionario essenziale

### users
| campo | tipo | note |
|---|---|---|
| first_name, last_name | varchar(80) | |
| email | varchar(190) unique | login |
| phone | varchar(30) null | per WhatsApp |
| role | enum BUYER/TECNICO/CAPO_REPARTO | indice |
| store_id | FK stores null | obbligatorio se CR (validato) |
| is_active | bool | login negato se false |
| must_change_password | bool | forza cambio in produzione |
| last_login_at | timestamp null | |

### stores
`code` unique, `name`, `address`, `city`, `province(2)`, `email`, `is_active`.

### products
`article_code` unique, `plu`, `description`, `long_description`, `category`, `origin`,
`fao_zone`, `production_method`, `caliber`, `unit_of_measure`, `vat_rate decimal(5,2)`, `is_active`.

### opportunities
| campo | tipo | note |
|---|---|---|
| reference | varchar(20) unique | `OPP-2026-0001`, progressivo leggibile |
| product_id | FK products null on delete set null | l'ordine sopravvive all'anagrafica |
| article_code, plu, description, long_description, category, origin, fao_zone, production_method, caliber | **snapshot** | congelati alla creazione |
| title, commercial_description, technical_notes, logistics_notes | testo | |
| order_unit | varchar(20) default `COLLO` | |
| kg_per_package | decimal(10,3) | peso netto per collo |
| price_unit | varchar(20) default `EUR/KG` | configurabile |
| purchase_price, sale_price_gross | decimal(10,4) | |
| vat_rate | decimal(5,2) | snapshot |
| markup_percent, margin_percent | decimal(8,2) | **calcolati**; override tracciato |
| pricing_override_reason, pricing_overridden_by | text / FK | solo utenti autorizzati |
| min_lot, order_multiple | unsigned int default 1 | |
| quick_quantities | json null | default `[1,2,3,4,5,6]` |
| availability_type | enum APERTA/LIMITATA | |
| total_packages | unsigned int null | obbligatorio se LIMITATA |
| committed_packages | unsigned int default 0 | impegnato, aggiornato in transazione |
| opens_at, closes_at | datetime UTC | `closes_at > opens_at` |
| delivery_date | date | `>= data(closes_at)` |
| status | enum (9 stati) | indice |
| requires_refusal_reason | bool | rende obbligatoria la motivazione di rifiuto |
| media_exception, media_exception_reason | bool/text | eccezione tracciata del Tecnico |
| created_by, reviewed_by, closed_by, cancelled_by | FK users | |
| approved_at, published_at, closed_at, cancelled_at, archived_at | timestamp null | |
| close_reason, cancel_reason | text null | obbligatorie per chiusura/annullamento |

Indici: `status`, `closes_at`, `opens_at`, `delivery_date`, `product_id`, `(status, closes_at)`.

### opportunity_media
`opportunity_id` FK cascade, `disk`, `path`, `type enum IMAGE/VIDEO`, `mime`, `size`,
`original_name`, `poster_path`, `width/height/duration` null, `sort_order`, `scan_status`.

### opportunity_stores
`opportunity_id`, `store_id`, unique(`opportunity_id`,`store_id`). Destinatari.

### responses (risposta **corrente**, una per coppia)
| campo | note |
|---|---|
| unique(`opportunity_id`,`store_id`) | invariante richiesta |
| status | `NON_COMPILATA`/`BOZZA`/`INVIATA_ACQUISTO`/`INVIATA_RIFIUTO`/`RIAPERTA`/`BLOCCATA` |
| packages | unsigned int, 0 se rifiuto |
| kg | decimal(12,3) calcolato |
| committed_packages | quota di stock attualmente impegnata da questa risposta |
| refusal_reason, submitted_at, last_actor_id | |
| reopened_until, reopened_by, reopen_reason | riapertura del Tecnico |

### response_revisions (storico immutabile)
`response_id`, `user_id`, `action`, `from_status`, `to_status`, `from_packages`, `to_packages`,
`reason`, `ip_address`, `created_at`.

### opportunity_reviews
`opportunity_id`, `reviewer_id`, `outcome enum APPROVATA/RESPINTA`, `notes`, `checklist json`.

### notifications / notification_deliveries
`notifications`: `type`, `user_id`, `opportunity_id`, `title`, `body`, `url`, `read_at`.
`notification_deliveries`: `notification_id`, `channel enum IN_APP/EMAIL/WHATSAPP`,
`status enum PENDING/SENT/FAILED`, `attempts`, `error`, `sent_at`, `dedupe_key` **unique**.

### audit_logs (append-only)
`user_id` null, `action`, `auditable_type`, `auditable_id`, `payload json`, `ip_address`,
`user_agent`, `created_at`. Nessun `updated_at`: il modello blocca update e delete.

## 3.3 Vincoli e scelte

- Tutte le FK sono reali con `ON DELETE` esplicito; nessuna cancellazione a cascata su `responses`
  se non tramite l'opportunità.
- Prezzi, IVA e pesi in `decimal`, mai `float`. Colli in `unsigned int`.
- Snapshot dei dati articolo nell'opportunità: modificare l'anagrafica non riscrive lo storico.
- `soft delete` solo su `users`, `stores`, `products` (anagrafiche disattivabili/ripristinabili);
  mai su opportunità e risposte, dove vale l'archiviazione di stato.
