FROM golang:1.24-alpine AS builder

WORKDIR /build
COPY go.mod go.sum ./
RUN go mod download

COPY . .
RUN CGO_ENABLED=0 GOOS=linux go build -ldflags="-s -w" -o iris .

# distroless/static includes CA certificates and a non-root user (uid 65532)
# without a shell or package manager, minimising the attack surface.
FROM gcr.io/distroless/static:nonroot
COPY --from=builder /build/iris /iris
USER nonroot:nonroot
ENTRYPOINT ["/iris"]
