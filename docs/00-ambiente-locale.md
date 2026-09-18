# 0. Ambiente locale su questo Mac

Questo computer non aveva PHP, Composer, MySQL né Docker. Per poter **eseguire davvero**
l'applicazione e i test sono stati installati, senza toccare il sistema, i seguenti strumenti
nella home dell'utente:

| Strumento | Percorso | Versione |
|---|---|---|
| PHP CLI (binario statico) | `~/.local/php/php` (symlink `~/.local/bin/php`) | 8.4.23 |
| Composer | `~/.local/bin/composer` | 2.10 |
| MySQL Community Server | `~/.local/mysql/bin/` | 8.4.6 |
| Dati MySQL | `~/.local/mysqldata/data` | — |

Aggiungi una volta per tutte il percorso alla shell:

```bash
echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.zshrc && source ~/.zshrc
```

## Avviare e fermare MySQL

```bash
# avvio (127.0.0.1:3306, utente root senza password — solo sviluppo locale)
~/.local/mysql/bin/mysqld \
  --basedir=$HOME/.local/mysql \
  --datadir=$HOME/.local/mysqldata/data \
  --socket=$HOME/.local/mysqldata/mysql.sock \
  --pid-file=$HOME/.local/mysqldata/mysql.pid \
  --port=3306 --bind-address=127.0.0.1 --mysqlx=OFF \
  > $HOME/.local/mysqldata/mysqld.log 2>&1 &

# arresto
~/.local/mysql/bin/mysqladmin --socket=$HOME/.local/mysqldata/mysql.sock -u root shutdown
```

Database già creati: `pescheria` (sviluppo) e `pescheria_test` (suite di test).

> In produzione si usa l'installazione MySQL del server, con utente dedicato e password:
> questa configurazione serve soltanto a far girare l'app su questa macchina.
> In alternativa, `docker compose up -d` solleva MySQL e Mailpit senza installare nulla.

## Limiti di upload

Il binario PHP statico non caricava alcun `php.ini`: i default (`upload_max_filesize = 2M`)
facevano fallire qualsiasi video. La configurazione corretta è in
[`docker/php/uploads.ini`](../docker/php/uploads.ini) ed è stata copiata in
`~/.local/php/php.ini`, che il binario carica automaticamente.

```bash
php --ini     # deve mostrare: Loaded Configuration File: ~/.local/php/php.ini
php -r 'echo ini_get("upload_max_filesize");'   # 110M
```

`bin/dev` imposta comunque `PHPRC` sul file del progetto, quindi funziona anche se
quel `php.ini` viene rimosso.

## Avviare l'applicazione

```bash
cd ~/Documents/pescheriasole
bin/dev serve        # http://localhost:8000
bin/dev queue        # in un secondo terminale, per email/WhatsApp
bin/dev scheduler    # in un terzo terminale, per apertura/scadenza/solleciti
npm run dev          # solo se si modificano CSS/JS
```
