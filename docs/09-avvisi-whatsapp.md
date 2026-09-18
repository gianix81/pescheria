# 9. Avvisi su WhatsApp

## 9.1 Cosa si può automatizzare e cosa no

| Destinatario | Automatico? | Perché |
|---|---|---|
| Una **persona** (Tecnico, Buyer, Capo Reparto) | sì, con WhatsApp Business API | l'API ufficiale invia messaggi 1:1 a chi ha dato consenso |
| Un **gruppo** | **no** | nessuna API ufficiale di Meta consente di scrivere in un gruppo |

Questa non è una scelta di progetto: è un limite della piattaforma. Esistono librerie non
ufficiali che pilotano un account WhatsApp reale e riescono a scrivere nei gruppi, ma violano le
condizioni d'uso e possono portare al blocco del numero — su un numero aziendale è un rischio
concreto, e per questo non sono state usate.

Dove serve il gruppo, l'applicazione prepara quindi il messaggio già scritto e lo apre in
WhatsApp: chi lo tocca sceglie il gruppo e invia. Un tocco, nessuna riscrittura a mano.

## 9.2 I tre momenti

| Momento | Chi agisce | In app | Su WhatsApp |
|---|---|---|---|
| Opportunità inviata in verifica | Buyer | notifica automatica a tutti i Tecnici | pulsante «Scrivi ai Tecnici», sulla scheda in verifica. Con l'API attiva il messaggio parte anche da solo |
| Opportunità approvata e aperta | Tecnico | notifica automatica ai capi reparto destinatari | pulsante «Condividi nel gruppo WhatsApp», sulla scheda aperta |
| Punto vendita conferma l'ordine | Capo Reparto | notifica automatica a Buyer e Tecnici | pulsante «Comunica al gruppo», nella ricevuta |

Ogni messaggio porta il collegamento alla scheda, che richiede autenticazione. WhatsApp resta un
canale di avviso: **l'ordine è valido solo dall'app**, e i messaggi non contengono pulsanti che
registrino quantità.

## 9.3 Come sono fatti i messaggi

```
🐟 Orata fresca — consegna giovedì
ART10001 · PLU 2101
€ 7,98/kg al pubblico · 6,0 kg per collo
40 colli disponibili
📅 Consegna 20/09/2026
⏱ Rispondere entro il 18/09/2026 18:00

Ordina qui: https://…/cr/opportunita/12

Le risposte valgono solo dall'app.
```

```
✅ PV001 ordina 3 colli (18,0 kg)
Orata fresca · ART10001
Totale finora: 11 colli (66,0 kg)
Restano 29 colli

Scheda: https://…/cr/opportunita/12
```

I testi sono in `App\Support\WhatsApp`: cambiarli non tocca né le notifiche in-app né il resto.
Accanto a ogni pulsante c'è «Copia testo», per chi preferisce incollare a mano.

## 9.4 Attivare l'invio automatico alle persone

```dotenv
NOTIFY_WHATSAPP_ENABLED=true
WHATSAPP_API_URL=https://graph.facebook.com/v20.0
WHATSAPP_API_TOKEN=…
WHATSAPP_PHONE_ID=…
```

Serve un account WhatsApp Business API con un numero verificato. Gli utenti devono avere il
telefono compilato in anagrafica, altrimenti la consegna viene registrata come fallita e la
notifica in-app resta comunque l'unica obbligatoria.

Per i messaggi iniziati dall'azienda fuori dalla finestra di 24 ore Meta richiede modelli
approvati: va previsto in fase di attivazione del numero.

## 9.5 Se in futuro servisse l'invio automatico ai gruppi

Le strade praticabili, in ordine di raccomandazione:

1. **Sostituire il gruppo con la lista dei destinatari**: l'app sa già chi sono i capi reparto di
   ogni punto vendita e può scrivere a ciascuno singolarmente, in modo automatico e conforme.
   È la via consigliata: dà lo stesso risultato senza dipendere da un gruppo.
2. **Canale WhatsApp** (broadcast ufficiale): adatto agli annunci in sola lettura, non alle
   conversazioni.
3. Librerie non ufficiali: fuori dalle condizioni d'uso, con rischio di blocco del numero.
