# tmux

This is a small tutorial that shows how to use [tmux](https://github.com/tmux/tmux/wiki)
to manage several "pseudo terminals" from a single one.

This is useful in version 0, as we need to have 5 terminals opened:

- window 1: for the `country` worker, and monitor its output
- window 3: for the `capital` worker, and monitor its output
- window 4: for the `rabbitmqadmin` CLI tool that sends test input

Here's a cheat sheet:

```console
# if tmux isn't already installed
sudo apt install tmux

# setup
docker compose build
docker compose run --rm challenge composer install --optimize-autoloader
docker compose up -d

# start tmux
tmux new-session -s challenge

# in window 0: country worker
docker compose exec challenge php /var/www/src/country.php > /tmp/country.log 2>&1

# in window 1: monitoring country worker output
Ctrl + b, c
tail -n 0 -f /tmp/country.log

# in window 2: capital worker
Ctrl + b, c
docker compose exec challenge php /var/www/src/capital.php > /tmp/capital.log 2>&1

# in window 3: monitoring capital worker output
Ctrl + b, c
tail -n 0 -f /tmp/capital.log

# in window 4: test input
Ctrl + b, c
docker compose exec rabbitmq bash
rabbitmqadmin publish exchange=router routing_key=country payload=FR
rabbitmqadmin publish exchange=router routing_key=capital payload=USA
rabbitmqadmin publish exchange=router routing_key=capital payload=Paris

# list windows
Ctrl + b, w
# switch to specific window
# (1 country output, 3 capital output or 4 rabbitmqadmin)
Ctrl + b, <window-number>

# kill all 4 windows and tmux
Ctrl + b, d
Ctrl + b, d
Ctrl + b, d
Ctrl + b, d
tmux kill-session -t challenge
```
