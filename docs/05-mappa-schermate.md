# 5. Mappa delle schermate

| Rotta | Componente | Ruolo | Cosa fa |
|---|---|---|---|
| `/login` | `Auth\LoginController` | ospite | accesso con rate limiting (5/min per email) |
| `/password/dimenticata`, `/password/reset/{token}` | `PasswordResetController` | ospite | recupero password |
| `/password/cambia` | `ChangePasswordController` | tutti | cambio obbligatorio al primo accesso |
| `/` | `HomeController` | tutti | redirect alla home del ruolo |
| `/notifiche` | `Shared\Notifiche` | tutti | elenco notifiche in-app, filtro non lette |
| `/media/{media}` | `MediaController` | autorizzati | media privato via URL firmato |

## Buyer

| Rotta | Componente | Contenuto |
|---|---|---|
| `/buyer/dashboard` | `Buyer\Dashboard` | bozze, in verifica, aperte, in scadenza, colli e kg ordinati, disponibilità residue, tasso di risposta, recenti, pulsante «Nuova opportunità» |
| `/buyer/opportunita/nuova` | `Buyer\OpportunitaForm` | **creazione rapida** (una schermata, mobile-first) e **creazione guidata** (wizard 4 passi), anteprima vista CR |
| `/buyer/opportunita/{id}/modifica` | `Buyer\OpportunitaForm` | modifica bozza o opportunità da correggere |
| `/buyer/ordini` | `Buyer\Ordini` | tutte le risposte, filtri, correzione eccezionale post-scadenza con motivazione |
| `/opportunita` | `Shared\OpportunitaIndex` | elenco con ricerca, stato, data consegna |
| `/opportunita/{id}` | `Shared\OpportunitaShow` | scheda completa, risposte per PdV, chiusura/annullamento, duplicazione, sollecito |
| `/export` | `Shared\Esporta` | filtri + anteprima + download CSV/XLSX |
| `/storico` | `Shared\OpportunitaIndex` (preset) | scadute, chiuse, annullate, archiviate |

## Tecnico

| Rotta | Componente | Contenuto |
|---|---|---|
| `/tecnico/dashboard` | `Tecnico\Dashboard` | da verificare, in scadenza oggi, PdV mancanti, anomalie, tabella monitoraggio con azioni rapide |
| `/tecnico/verifica/{id}` | `Tecnico\Verifica` | due pannelli: anteprima completa + checklist sticky, «Approva» / «Richiedi correzioni», eccezione media tracciata |
| `/tecnico/monitor` | `Tecnico\Monitor` | matrice opportunità × punti vendita (lista raggruppata su mobile), selezione multipla dei mancanti, sollecito |
| `/tecnico/anagrafiche/utenti` | `Tecnico\Anagrafiche\Utenti` | CRUD utenti, associazione CR–punto vendita obbligatoria |
| `/tecnico/anagrafiche/punti-vendita` | `Tecnico\Anagrafiche\PuntiVendita` | CRUD punti vendita |
| `/tecnico/anagrafiche/prodotti` | `Tecnico\Anagrafiche\Prodotti` | CRUD prodotti |
| `/tecnico/audit` | `Tecnico\Audit` | log immutabile con filtri |
| `/tecnico/stato` | `Tecnico\StatoSistema` | **solo Super Admin**: versione pubblicata, stato del database e migrazioni non applicate, persistenza del disco dei media, ultima esecuzione dello scheduler. Risponde senza aprire una shell alle domande che tornano durante la messa in linea |

## Capo Reparto

| Rotta | Componente | Contenuto |
|---|---|---|
| `/cr/opportunita` | `Cr\Opportunita` | **pagina d'ingresso: l'elenco della merce, non una dashboard.** In prima pagina solo ciò su cui si può ancora agire — stato `APERTA` **e** termine non passato; chiuse, annullate e scadute stanno in «Storico». Schede di filtro con i conteggi (Da rispondere / Bozze / Inviate / Storico), una sola riga di avviso quando c'è una scadenza vicina, e card con anteprima grande, articolo, PLU, origine, prezzo, peso collo, disponibilità residua, consegna, countdown, esito della propria risposta e **quanto hanno già ordinato gli altri punti vendita**. `/cr/dashboard` reindirizza qui |
| `/cr/opportunita/{id}` | `Cr\Scheda` | due colonne su desktop (galleria + dati), colonna unica e barra azioni sticky su mobile; box decisione «Acquista / Non acquista», pulsanti rapidi 1–6, stepper, «Altra quantità», riepilogo `N colli × X kg = Y kg`, conferma modale, ricevuta; **classifica degli ordini degli altri punti vendita** con totali e, se la disponibilità è limitata, barra di quanto è già impegnato |

## Priorità e lettura su telefono

Ogni pagina di ruolo si apre con **una riga sola** che dice che cosa si sta guardando e qual è la
prossima cosa da fare, con il pulsante dell'azione principale accanto. La frase cambia con la
situazione, in ordine di urgenza:

| Ruolo | Prima | Poi | Se non c'è nulla |
|---|---|---|---|
| Buyer | opportunità respinte da correggere | in scadenza entro 6 ore | «pubblicane una nuova» |
| Tecnico | opportunità che aspettano la verifica | punti vendita che non hanno risposto | «tutto in ordine» |
| Capo Reparto | opportunità che aspettano la sua risposta | — | elenco della merce |

I conteggi stanno in riquadri **due per riga su telefono** (quattro da tablet), alti quanto basta:
occupano mezza schermata invece di quattro, e sono collegamenti ai rispettivi elenchi.

Le tabelle larghe — ultime opportunità, compilazioni, elenco opportunità — hanno una **lista
compatta per telefono** e restano tabelle da 1024 px in su. Su telefono non si scorre mai di lato.

## Comportamenti trasversali

- **Skeleton** durante i caricamenti Livewire, **stati vuoti** con spiegazione e azione successiva.
- **Toast** per conferme non critiche, **dialog modale** per azioni irreversibili.
- **Banner persistenti** per opportunità scaduta, chiusa, annullata o esaurita.
- Errori vicino al campo **e** riepilogo in testa alla pagina.
- Stato sempre comunicato da icona + testo, mai dal solo colore; target touch ≥ 44×44 px.
