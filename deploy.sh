#!/usr/bin/env bash
# ===========================================================================
# deploy.sh — construye, publica y despliega el sitio de Soluciones en Acero.
#
# Uso:
#   ./deploy.sh            # tag auto-incremental (ultimo + 1)
#   ./deploy.sh 7          # usar un tag concreto
#
# Flujo:
#   1. Verifica herramientas, sesion de Docker Hub y values.local.yaml
#   2. Verifica que cotizaciones (BD y servicio de fotos) exista en el cluster
#   3. docker build  → gmr27/solucionesenacero:<tag> (+ :latest)
#      (el Dockerfile compila SASS/JS y dependencias PHP; no hace falta npm aqui)
#   4. docker push
#   5. helm upgrade --install (namespace solucionesenacero)
#   6. kubectl rollout status + prueba de humo + resumen
#
# Requisitos previos (una sola vez, ver README "Despliegue"):
#   - cotizaciones desplegado con PORTAL_FOTOS_SECRETO
#   - usuario MySQL portal_soluciones creado (crear_usuario_portal.sh)
# ===========================================================================
set -euo pipefail

# --- Configuracion ---------------------------------------------------------
REPO="gmr27/solucionesenacero"
NAMESPACE="solucionesenacero"
RELEASE="solucionesenacero"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CHART_DIR="$ROOT_DIR/helm/solucionesenacero"
TAG_FILE="$ROOT_DIR/.deploy-tag"
HOST="solucionesenacero.iguzman.com.mx"

verde()   { printf '\033[32m%s\033[0m\n' "$*"; }
amarillo(){ printf '\033[33m%s\033[0m\n' "$*"; }
rojo()    { printf '\033[31m%s\033[0m\n' "$*" >&2; }

# --- Tag -------------------------------------------------------------------
if [[ $# -ge 1 ]]; then
    TAG="$1"
else
    ULTIMO=0
    [[ -f "$TAG_FILE" ]] && ULTIMO="$(cat "$TAG_FILE")"
    [[ "$ULTIMO" =~ ^[0-9]+$ ]] || ULTIMO=0
    TAG=$((ULTIMO + 1))
fi
verde "==> Tag de esta version: $TAG"

# --- Requisitos -------------------------------------------------------------
for cmd in docker helm kubectl; do
    command -v "$cmd" >/dev/null 2>&1 || { rojo "Falta '$cmd' en el PATH."; exit 1; }
done

if ! grep -q '"auths"' ~/.docker/config.json 2>/dev/null \
   || grep -q '"auths"[[:space:]]*:[[:space:]]*{}' ~/.docker/config.json 2>/dev/null; then
    rojo "No hay credenciales de Docker Hub. Ejecuta primero:  docker login"
    exit 1
fi

if [[ ! -f "$CHART_DIR/values.local.yaml" ]]; then
    rojo "Falta $CHART_DIR/values.local.yaml (copia values.local.yaml.example y llena los secretos)."
    exit 1
fi

# El chart valida los secretos (helm template falla si faltan o son cortos)
helm template "$RELEASE" "$CHART_DIR" -f "$CHART_DIR/values.yaml" -f "$CHART_DIR/values.local.yaml" >/dev/null

# --- Dependencias en el cluster --------------------------------------------
verde "==> Verificando cotizaciones en el cluster..."
for svc in mysql cotizaciones; do
    if ! kubectl get svc "$svc" -n cotizaciones >/dev/null 2>&1; then
        rojo "No existe el servicio '$svc' en el namespace cotizaciones."
        exit 1
    fi
done
if ! kubectl get secret cotizaciones -n cotizaciones -o jsonpath='{.data.PORTAL_FOTOS_SECRETO}' 2>/dev/null | grep -q .; then
    amarillo "AVISO: cotizaciones no tiene PORTAL_FOTOS_SECRETO: las fotos de entrega no se veran en el portal."
    amarillo "       Despliega cotizaciones con secrets.portalFotosSecreto (el mismo valor que aqui)."
fi

# --- Build ------------------------------------------------------------------
verde "==> Construyendo imagen..."
docker build -t "$REPO:$TAG" -t "$REPO:latest" "$ROOT_DIR"

# --- Push -------------------------------------------------------------------
verde "==> Subiendo a Docker Hub..."
docker push "$REPO:$TAG"
docker push "$REPO:latest"

# --- Deploy -----------------------------------------------------------------
verde "==> Desplegando con Helm en el namespace '$NAMESPACE'..."
helm upgrade --install "$RELEASE" "$CHART_DIR" \
    --namespace "$NAMESPACE" \
    --create-namespace \
    -f "$CHART_DIR/values.yaml" \
    -f "$CHART_DIR/values.local.yaml" \
    --set image.tag="$TAG"

echo "$TAG" > "$TAG_FILE"

# --- Verificacion -----------------------------------------------------------
verde "==> Esperando a que el rollout termine..."
kubectl rollout status deployment/solucionesenacero -n "$NAMESPACE" --timeout=180s

verde "==> Prueba de humo dentro del pod..."
POD="$(kubectl get pod -n "$NAMESPACE" -l app=solucionesenacero -o jsonpath='{.items[0].metadata.name}')"
kubectl exec -n "$NAMESPACE" "$POD" -- php -r '
    require "/var/www/html/includes/app.php";
    try { db()->query("SELECT 1 FROM pedidos LIMIT 1"); echo "  BD de cotizaciones: OK\n"; }
    catch (Throwable $e) { echo "  BD de cotizaciones: ERROR (" . $e->getMessage() . ")\n"; exit(1); }
' || amarillo "AVISO: el sitio publico funciona, pero el portal de clientes no conecta a la BD (revisa usuario y contrasena)."

echo
verde "==> Despliegue completado. Estado actual:"
kubectl get pods,svc,ingress -n "$NAMESPACE"
echo
verde "URL: https://$HOST"
amarillo "El certificado TLS puede tardar 1-2 min la primera vez (cert-manager)."
