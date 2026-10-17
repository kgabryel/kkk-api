FROM ubuntu

RUN apt-get update && \
    apt-get install -y openssl

CMD openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /ssl/localhost.key -out /ssl/localhost.crt -subj "/CN=example.com"