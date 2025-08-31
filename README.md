# Backend de Reservas

Este é um projeto backend para gerenciamento de reservas de salas/ambientes no contexto corporativo. A aplicação segue os padrões de comunicação **REST API** e utiliza as seguintes tecnologias:

- **MySQL** como banco de dados relacional
- **Redis** como cache em memória (banco de dados não relacional)
- **PHPUnit** para testes unitários
- **JWT (JSON Web Token)** para autenticação
- **Swagger** para documentação da API

---

## 🚀 Requisitos para executar o projeto

Antes de iniciar, verifique os seguintes pré-requisitos:

- **Portas 8000 e 4200 livres**
- **Docker instalado**  
  [Clique aqui para baixar o Docker](https://www.docker.com/)

### 1. Criar rede externa no Docker
```bash
docker network create laravel
```

### 2. Criar arquivo .env na raiz do projeto, utilizar como base .env.example

### 3. Buildar projeto
```bash
docker compose up -d
```
---

## 📚 Testes unitários

### 1. Para executar os testes unitários acesse o container com o seguinte comando na raiz do projeto
```bash
docker exec -it skedway_backend_app sh
```

### 2. Dentro do container, execute o comando
```bash
php artisan test
```

---

## 🔗 Links

1 - [Collection Postman](https://www.postman.com/altimetry-specialist-72965033/workspace/api-rest-reservas-skedway/collection/31846039-d3334273-4a78-42c9-8484-a7747dc94ea2?action=share&creator=31846039)

2 - [Documentação Swagger - *Necessário rodar o projeto*](http://localhost:8000/api/documentation)

