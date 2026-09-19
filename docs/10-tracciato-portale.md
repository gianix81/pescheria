# 10. Tracciato «Assegnazione per portale»

## 10.1 Il file

È il file che si carica sul portale del fornitore. Il tracciato è **fissato dal portale** e
l'applicazione lo riproduce identico, a partire dal file reale fornito dal committente
(conservato in `tests/Fixtures/assegnazione-per-portale-riferimento.xlsx`).

Foglio unico: **`DATI`**

| A | B | C | D |
|---|---|---|---|
| `DATA CONSEGNA` | `CLIENTE` | `PRODOTTO` | `QUANTITA` |
| `15/09/2026` | `566518` | `497109` | `1` |
| testo, formato `@` | numero | numero | numero |

Intestazione in grassetto, larghezze di colonna come nell'originale.

**La data è testo, non una data di Excel.** È voluto: una data vera verrebbe riscritta secondo le
impostazioni locali di chi apre il file — negli Stati Uniti `15/09/2026` diventerebbe un errore o
una data diversa. Il portale la legge come è scritta.

## 10.2 Cosa finisce nel file

Una riga per **ogni acquisto confermato**: al portale si comunica ciò che va consegnato. Rifiuti
espliciti e mancate risposte non producono righe.

Con i filtri della pagina Export si sceglie cosa esportare: singola opportunità, intervallo di date
di consegna, punto vendita, stato.

## 10.3 I codici

**`CLIENTE` è il codice del punto vendita e `PRODOTTO` è il codice articolo**: in azienda sono già
la stessa cosa, quindi l'export li usa direttamente e non c'è nulla da compilare in più.

Esiste comunque un campo dedicato per ciascuno, da usare **solo** se un domani il portale adottasse
numerazioni diverse. L'ordine di precedenza è:

| Colonna | Primo | Poi | Infine |
|---|---|---|---|
| `CLIENTE` | `stores.portal_code` | — | `stores.code` |
| `PRODOTTO` | `opportunities.portal_product_code` (snapshot) | `products.portal_code` | `opportunities.article_code` |

Lo snapshot sull'opportunità funziona come per gli altri dati articolo: modificare l'anagrafica non
riscrive gli export storici.

Nelle anagrafiche la colonna «Portale» mostra `= PV001` quando il codice coincide con quello
interno, così si vede a colpo d'occhio che non serve intervenire.

Il messaggio «codici mancanti» nella pagina Export compare quindi solo se una voce non ha
**nessuno** dei due codici, caso che in pratica si verifica solo se il codice è stato svuotato a
mano nel database.

### Tipi di cella dei codici

Un codice di sole cifre senza zeri iniziali viene scritto come **numero**, come nel file di
riferimento (`566518`). Un codice come `0002` verrebbe alterato da una conversione numerica, quindi
viene scritto come **testo**, conservando gli zeri.

## 10.4 Nome del file

Il download si chiama esattamente `Assegnazione per portale.xlsx`, come l'originale: alcuni
caricamenti sono sensibili anche al nome. Se il portale accettasse un nome con la data, si cambia
in una riga (`ExportService::assegnazionePortale`).

## 10.5 Verifica

`AssegnazionePortaleTest` ricostruisce la riga del file reale — consegna 15/09/2026, cliente
566518, prodotto 497109, quantità 1 — genera il file e lo confronta con il riferimento **cella per
cella**: valore, tipo di dato e formato numerico. Se qualcuno cambiasse il tracciato per sbaglio,
il test lo direbbe subito.
