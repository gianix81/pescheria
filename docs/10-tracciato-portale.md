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

## 10.3 I codici del portale

`CLIENTE` e `PRODOTTO` **non** sono i codici interni: nel file reale il punto vendita è `566518` e
l'articolo `497109`, mentre internamente sono `PV001` e `ART10001`. Sono quindi due campi distinti:

| Campo | Dove si compila |
|---|---|
| `stores.portal_code` | Anagrafiche → Punti vendita → «Codice cliente portale» |
| `products.portal_code` | Anagrafiche → Prodotti → «Codice prodotto portale» |

Il codice prodotto viene fotografato sull'opportunità alla creazione
(`opportunities.portal_product_code`), come gli altri dati articolo: modificare l'anagrafica non
riscrive gli export storici.

**Senza codice non c'è riga valida.** La pagina Export elenca in rosso i punti vendita e i prodotti
a cui manca, prima che il file venga generato, così l'errore si scopre qui e non al caricamento sul
portale. Nelle anagrafiche la colonna «Portale» segnala «manca» a colpo d'occhio.

## 10.4 Nome del file

Il download si chiama esattamente `Assegnazione per portale.xlsx`, come l'originale: alcuni
caricamenti sono sensibili anche al nome. Se il portale accettasse un nome con la data, si cambia
in una riga (`ExportService::assegnazionePortale`).

## 10.5 Verifica

`AssegnazionePortaleTest` ricostruisce la riga del file reale — consegna 15/09/2026, cliente
566518, prodotto 497109, quantità 1 — genera il file e lo confronta con il riferimento **cella per
cella**: valore, tipo di dato e formato numerico. Se qualcuno cambiasse il tracciato per sbaglio,
il test lo direbbe subito.
