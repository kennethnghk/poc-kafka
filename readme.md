# POC Kafka

## Getting started

1. Run Kafka and Zookeeper by docker-compose.yml

```sh
docker-compose up -d
```

Note that in `docker-compose.yml`:

- `KAFKA_CREATE_TOPICS: test:6:1` will create a topic `test` with 6 partitions and 1 replica
- `KAFKA_ZOOKEEPER_CONNECT: zoo1:2181` means it should connect to `zoo1` in `kafka1`

2. Login the Kafka container

```sh
docker exec -it [containerId] /bin/bash
```

3. In the kafka container, try to list the topics

```sh
$KAFKA_HOME/bin/kafka-topics.sh --list --zookeeper zoo1:2181
```

## Producer and consumer

Inside the web app, you can run producer (publish message) and consumer (listner) by Laravel artisan commands:

Producer:
```sh
php artisan producer
```

Consumer:
```sh
php artisan consumer
```