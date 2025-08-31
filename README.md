Este projeto possui um frontend separado, por isso e necessário que exista uma rede docker específica para que os dois projetos possam se comunicar, para isso:

criar network laravel

docker network create laravel

criar .env a partir da .env.example

executar comando docker compose up -d
