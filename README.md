# DevBook

### Tasks

###### EN

- Project It must be prepared for mobile applications.
- Create communicator with use protocol WebSocket ([Ratchet](http://socketo.me/)). 
    * It must send a message down online users (Web and mobile) (Live).
    * It must send notification for offline user.
    * User by user interface must be able to create conversation to other user.
    * Conversation have a maximum two people.
    * He must have conversational stories.
- User is supposed to have the possibility added post (addition post with using RabbitMQ).
- All tokens (Mobile, Web, Public) need to refresh every certain time.
- The development environment is based on Docker.
- The entire application is to be in English and ready for translation into other languages.
  
  
###### PL

- Projekt musi być przygotowany do aplikacji mobilnych.
- Stwórz komunikator używając protokołu WebSocket ([Ratchet](http://socketo.me/)).
    * Musi wysyłać wiadomości do użytkowników online (Web i aplikacji mobilnych) (Live).
    * Musi wysyłać powiadomienia do nieaktywnego użytkownika.
    * Użytkownik przez interfejs użytkownika musi móc tworzyć rozmowy z innym użytkownikiem.
    * Konwersacja może mieć maksimum dwie osoby.
    * Musi posiadać historie konwersacji.
- Użytkownik powinnien mieć możliwość dodawania postu (Dodawanie postów za pomocą RabbitMQ).
- Wszystkie tokeny (Mobilne, internetowe, publiczne) muszą odświeżać się co pewien czas.
- Środowisko developerskie ma opierać się o Dockera. 
- Cała aplikacja ma być w języku angielskim i gotowa dla translacji na inne języki.


### What i gained

###### EN

- Basic knowledge from Ratchet and WebSocket (JS, socketo.me).
- Basic knowledge from RabbitMQ, Redis and Docker.
- I understood how to prevent incorrect implementation of the Command design pattern.
- I understood how console applications written in Symfony work.

###### PL

- Podstawową wiedzę z Ratchet (socketo.me) i Websocket (JS)
- Podstawową wiedzę z RabbitMQ, Redis i Docker.
- Zrozumiałem jak zapobiegać błędnej implemetancji wzorca projektowego Command.
- Zrozumiałem jak działają aplikacje konsolowe napisane w Symfony.


### What mistakes I made

###### EN

- At the beginning of the project I implemented the Command pattern incorrectly.
- I did not look at the validation of the entity before writing to the database.

###### PL

- Na początku projektu źle zaimplementowałem wzorzec Command
- Niedopilnowałem walidacji encji przed zapisem do bazy danych.

### How to run project

###### EN

- In console execute command 'docker-compose up -d'
- Come in to console container by execution command 'docker container exec -it devbook_web_1 bash'
and update database 'php bin/console doctrine:schema:update --force'
- Execute in console container ('docker container exec -it devbook_web_1 bash')  three command 
'php bin/console DevMessenger:server-start', 'php bin/console RabbitMQ:notification-worker-start'
, 'php bin/console RabbitMQ:post-worker-start'
- Project is ready to working.

###### PL

- W konsoli wykonaj polecenie 'docker-compose up -d'
- Wejdź do konsoli kontenera za pomocą wykonania komendy 'docker container exec -it devbook_web_1 bash'
i zaaktualizuj baze danych 'php bin/console doctrine:schema:update --force'
- Wykonaj w konsoli kontenera ('docker container exec -it devbook_web_1 bash')  trzy komendy
'php bin/console DevMessenger:server-start', 'php bin/console RabbitMQ:notification-worker-start'
, 'php bin/console RabbitMQ:post-worker-start'
- Projekt jest gotowy do pracy.

### License

GNU GENERAL PUBLIC LICENSE Version 3

### Authors

* **Łukasz Staniszewski**