FROM redis:latest

ENV REDIS_PASSWORD my-pass

CMD ["sh", "-c", "exec redis-server --requirepass \"$REDIS_PASSWORD\""]